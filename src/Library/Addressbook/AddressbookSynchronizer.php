<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Addressbook;

use Contao\Config;
use Contao\Database;
use Contao\DataContainer;
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Teusal\ContaoPhoneNumberNormalizerBundle\Library\Addressbook\CarddavBackend;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * Class AddressbookSynchronizer.
 */
class AddressbookSynchronizer extends System
{
    /**
     * a constant for the logfile name.
     */
    public const logFile = 'addressbook.log';

    /**
     * processing a submit of a referee.
     *
     * @param DataContainer|string|int $var            The reference to the referee
     * @param int                      $excludeGroupId An optional group id to exclude
     * @param bool                     $force          true if you want to push the vcard although there are no changes
     */
    public static function executeSubmitReferee($var, $excludeGroupId = 0, $force = false): void
    {
        if (!Config::get('createAddressbooks')) {
            return;
        }

        System::getContainer()->get('monolog.logger.teusal.addressbook')->info('This is an test: info');
        System::getContainer()->get('monolog.logger.teusal.addressbook')->warning('This is an test: warning');
        System::getContainer()->get('monolog.logger.teusal.addressbook')->error('This is an test: error');

        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            Message::addInfo('Adressbuch-Synchronisation wird im Testsystem nicht ausgeführt.');

            return;
        }

        $intId = 0;

        if ($var instanceof DataContainer) {
            $intId = $var->id;
        } elseif (\is_int($var)) {
            $intId = (int) $var;
        } else {
            $intId = (int) $var;
        }

        if (0 === $intId) {
            return;
        }

        // creating a new instance
        $carddav = new CarddavBackend();
        $carddav->set_auth(Config::get('addressbookUsername'), Config::get('addressbookPassword'));

        // read the default addressbook from the configuration
        $addressbookURI = Config::get('addressbookURI').'/addressbooks/'.Config::get('addressbookUsername');

        // getting the referee from the database
        $objReferee = RefereeModel::findReferee($intId);

        if (!isset($objReferee)) {
            throw new \Exception('Schiedsrichter zu ID '.$intId.' nicht gefunden!');
        }

        System::getContainer()->get('monolog.logger.teusal.addressbook')->info('Synchronize. Contact: '.$objReferee->nameReverse);

        if ($objReferee->deleted || !(RefereeModel::isClubReferee($objReferee->id) || ClubChairmanModel::isChairman($objReferee->id))) {
            $arrGroups = [];
        } else {
            $query = 'SELECT DISTINCT tl_member_group.addressbook_token_id ';
            $query .= 'FROM tl_member_group ';
            $query .= 'JOIN tl_bsa_member_group_referee_assignment ON tl_member_group.id=tl_bsa_member_group_referee_assignment.pid ';
            $query .= 'WHERE tl_member_group.sync_addressbook=? ';
            $query .= 'AND tl_member_group.addressbook_token_id!=? ';
            $query .= 'AND tl_member_group.id!=? ';
            $query .= 'AND tl_bsa_member_group_referee_assignment.refereeId=?';

            $arrGroups = Database::getInstance()->prepare($query)
                ->execute(true, '', $excludeGroupId, $objReferee->id)
                ->fetchAllAssoc()
            ;

            if (!empty(Config::get('defaultAddressbookTokenId')) && !\in_array(Config::get('defaultAddressbookTokenId'), $arrGroups, true)) {
                $arrGroups[] = ['addressbook_token_id' => Config::get('defaultAddressbookTokenId')];
            }
        }

        $arrExistingVCards = StringUtil::deserialize($objReferee->addressbookVcards);

        if (!\is_array($arrExistingVCards)) {
            $arrExistingVCards = [];
        }
        $arrNewVCards = [];

        // determine which vcards still exist. setting uid and vcard_id there. keep the rest to delete it
        foreach ($arrGroups as $key => $arrGroup) {
            if (\array_key_exists($arrGroup['addressbook_token_id'], $arrExistingVCards)) {
                $arrGroups[$key]['vcard_id'] = $arrExistingVCards[$arrGroup['addressbook_token_id']]['vcard_id'];
                $arrGroups[$key]['uid'] = $arrExistingVCards[$arrGroup['addressbook_token_id']]['uid'];
                $arrGroups[$key]['checksum'] = $arrExistingVCards[$arrGroup['addressbook_token_id']]['checksum'];
                unset($arrExistingVCards[$arrGroup['addressbook_token_id']]);
            }
        }

        // delete unused cards
        foreach ($arrExistingVCards as $addressbookTokenId => $vcardToDelete) {
            $carddav->set_url($addressbookURI.'/'.$addressbookTokenId);
            $carddav->delete($vcardToDelete['vcard_id']);
            log_message('   Delete vCard ID='.$vcardToDelete['vcard_id'].' from addressbook '.$addressbookTokenId.'. Contact: '.$objReferee->nameReverse, static::logFile);
        }

        if (!empty($arrGroups)) {
            // create content of vcard
            $vcardTemplate = self::getVCardContent($objReferee, $excludeGroupId);
            $checksum = md5($vcardTemplate);

            // now update or create cards
            foreach ($arrGroups as $arrGroup) {
                $vcardId = $arrGroup['vcard_id'];
                $uid = self::getUID($objReferee, $arrGroup);

                if ($checksum === $arrGroup['checksum'] && !$force) {
                    $arrNewVCards[$arrGroup['addressbook_token_id']] = ['vcard_id' => $vcardId, 'uid' => $uid, 'checksum' => $checksum];
                    continue;
                }

                // set UID and REV
                $vcard = sprintf($vcardTemplate, $uid, self::getLastRevisionDate());

                // set used addressbook
                $carddav->set_url($addressbookURI.'/'.$arrGroup['addressbook_token_id'].'/');

                // update or create
                if (empty($vcardId)) {
                    $vcardId = $carddav->add($vcard);
                    log_message('   Create vCard ID='.$vcardId.' in addressbook '.$arrGroup['addressbook_token_id'].'. Contact: '.$objReferee->nameReverse, static::logFile);
                } else {
                    $carddav->update($vcard, $vcardId);
                    log_message('   Update vCard ID='.$vcardId.' in addressbook '.$arrGroup['addressbook_token_id'].'. Contact: '.$objReferee->nameReverse, static::logFile);
                }

                // set values to save them in the database
                $arrNewVCards[$arrGroup['addressbook_token_id']] = ['vcard_id' => $vcardId, 'uid' => $uid, 'checksum' => $checksum];
            }
        }

        Database::getInstance()->prepare('UPDATE tl_bsa_referee SET addressbookVcards=? WHERE id=?')
            ->execute(serialize($arrNewVCards), $objReferee->id)
        ;
    }

    /**
     * returns the vcard content.
     *
     * @param RefereeModel $objReferee     The referee
     * @param int          $excludeGroupId An optional group id to exclude
     *
     * @return string vcard content
     */
    private static function getVCardContent($objReferee, $excludeGroupId)
    {
        $nl = '
';

        $categories = self::getCategories($objReferee, $excludeGroupId);

        // create vCard content string
        $vcard = 'BEGIN:VCARD'.$nl;
        $vcard .= 'VERSION:3.0'.$nl;
        $vcard .= 'UID:%s'.$nl;

        if (!empty($categories)) {
            $vcard .= 'CATEGORIES:'.$categories.$nl;
        }
        $vcard .= 'FN:'.$objReferee->firstname.' '.$objReferee->lastname.$nl;
        $vcard .= 'N:'.$objReferee->lastname.';'.$objReferee->firstname.';;;'.$nl;

        if (!empty($objReferee->dateOfBirthAsDate)) {
            $vcard .= 'BDAY;value=date:'.$objReferee->dateOfBirthAsDate.$nl;
        }

        if (!empty($objReferee->email)) {
            $vcard .= 'EMAIL;type=INTERNET;type=HOME;type=pref:'.$objReferee->email.$nl;
        }

        $pref = ';type=pref';

        if (!empty($objReferee->mobile)) {
            $vcard .= 'TEL;type=CELL;type=VOICE'.$pref.':'.str_replace(' / ', ' ', preg_replace('/^0 */', '+49 ', preg_replace('/^00/', '+', $objReferee->mobile))).$nl;
            $pref = '';
        }

        if (!empty($objReferee->phone1)) {
            $vcard .= 'TEL;type=HOME;type=VOICE'.$pref.':'.str_replace(' / ', ' ', preg_replace('/^0 */', '+49 ', preg_replace('/^00/', '+', $objReferee->phone1))).$nl;
            $pref = '';
        }

        if (!empty($objReferee->phone2)) {
            $vcard .= 'TEL;type=WORK;type=VOICE'.$pref.':'.str_replace(' / ', ' ', preg_replace('/^0 */', '+49 ', preg_replace('/^00/', '+', $objReferee->phone2))).$nl;
        }

        if (!empty($objReferee->street) || !empty($objReferee->postal) || !empty($objReferee->city)) {
            $vcard .= 'ADR;type=HOME;type=pref:;;'.$objReferee->street.';'.$objReferee->city.';;'.$objReferee->postal.';Deutschland'.$nl;
        }

        $vcard .= 'REV:%s'.$nl;
        $vcard .= 'END:VCARD';

        return $vcard;
    }

    /**
     * returns the groups of the referee as a category string, optional excludes the specified group.
     *
     * @param RefereeModel $objReferee     The referee
     * @param int          $excludeGroupId An optional group id to exclude
     *
     * @return string names of groups as string
     */
    private static function getCategories($objReferee, $excludeGroupId)
    {
        $query = 'SELECT DISTINCT REPLACE(name, ",", "\,") AS name FROM tl_member_group JOIN tl_bsa_member_group_referee_assignment ON tl_member_group.id=tl_bsa_member_group_referee_assignment.pid WHERE tl_member_group.id!=? AND tl_bsa_member_group_referee_assignment.refereeId=? ORDER BY name';

        $arrNames = Database::getInstance()->prepare($query)
            ->execute($excludeGroupId, $objReferee->id)
            ->fetchEach('name')
        ;

        return html_entity_decode(implode(',', $arrNames));
    }

    /**
     * returns a UID.
     *
     * @param RefereeModel         $objReferee The referee
     * @param array<string, mixed> $arrGroup   The goups
     *
     * @return string uid
     */
    private static function getUID($objReferee, $arrGroup)
    {
        // generate a UID
        if (!empty($arrGroup['uid'])) {
            return $arrGroup['uid'];
        }

        return uniqid($objReferee->id.'-');
    }

    /**
     * returns the last revision date as string.
     *
     * @return string
     */
    private static function getLastRevisionDate()
    {
        // last revision date of last update in UTC
        $tz = date_default_timezone_get();
        date_default_timezone_set('UTC');
        $rev = date('Y-m-d\TH:i:s\Z');
        date_default_timezone_set($tz);

        return $rev;
    }
}
