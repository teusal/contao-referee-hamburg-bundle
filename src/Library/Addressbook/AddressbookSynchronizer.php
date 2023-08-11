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
use Contao\Message;
use Contao\StringUtil;
use Contao\System;
use Teusal\ContaoPhoneNumberNormalizerBundle\Library\Addressbook\CarddavBackend;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;

/**
 * Class AddressbookSynchronizer.
 */
class AddressbookSynchronizer extends System
{
    /**
     * a constant for the logfile name.
     */
    public const logFile = 'addressbook.log';

    public static function executeSubmitSchiedsrichter($var, $excludeGroupId = 0, $force = false): void
    {
        if (!Config::get('createAddressbooks')) {
            return;
        }

        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            Message::addInfo('Adressbuch-Synchronisation wird im Testsystem nicht ausgeführt.');

            return;
        }

        $intId = 0;

        if ($var instanceof DataContainer) {
            $intId = $var->id;
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
        $objSR = RefereeModel::findReferee($intId);

        if (!isset($objSR)) {
            throw new \Exception('Schiedsrichter zu ID '.$intId.' nicht gefunden!');
        }

        log_message('Synchronize. Contact: '.$objSR->__get('nameReverse'), static::logFile);

        if ($objSR->__get('deleted') || !(RefereeModel::isClubReferee($objSR->id) || ClubChairmanModel::isChairman($objSR->id))) {
            $arrGroups = [];
        } else {
            $query = 'SELECT DISTINCT tl_member_group.addressbook_token_id FROM tl_member_group JOIN tl_bsa_member_group_member_assignment ON tl_member_group.id=tl_bsa_member_group_member_assignment.pid WHERE tl_member_group.sync_addressbook=? AND tl_member_group.addressbook_token_id!=? AND tl_member_group.id!=? AND tl_bsa_member_group_member_assignment.refereeId=?';

            $arrGroups = Database::getInstance()->prepare($query)
                ->execute(true, '', $excludeGroupId, $objSR->id)
                ->fetchAllAssoc()
            ;

            if (!empty(Config::get('defaultAddressbookTokenId')) && !\in_array(Config::get('defaultAddressbookTokenId'), $arrGroups, true)) {
                $arrGroups[] = ['addressbook_token_id' => Config::get('defaultAddressbookTokenId')];
            }
        }

        $arrExistingVCards = StringUtil::deserialize($objSR->__get('addressbookVcards'));

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
            log_message('   Delete vCard ID='.$vcardToDelete['vcard_id'].' from addressbook '.$addressbookTokenId.'. Contact: '.$objSR->__get('nameReverse'), static::logFile);
        }

        if (!empty($arrGroups)) {
            // create content of vcard
            $vcardTemplate = static::getVCardContent($objSR, $excludeGroupId);
            $checksum = md5($vcardTemplate);

            // now update or create cards
            foreach ($arrGroups as $arrGroup) {
                $vcardId = $arrGroup['vcard_id'];
                $uid = static::getUID($objSR, $arrGroup);

                if ($checksum === $arrGroup['checksum'] && !$force) {
                    $arrNewVCards[$arrGroup['addressbook_token_id']] = ['vcard_id' => $vcardId, 'uid' => $uid, 'checksum' => $checksum];
                    continue;
                }

                // set UID and REV
                $vcard = sprintf($vcardTemplate, $uid, static::getLastRevisionDate());

                // set used addressbook
                $carddav->set_url($addressbookURI.'/'.$arrGroup['addressbook_token_id'].'/');

                // update or create
                if (empty($vcardId)) {
                    $vcardId = $carddav->add($vcard);
                    log_message('   Create vCard ID='.$vcardId.' in addressbook '.$arrGroup['addressbook_token_id'].'. Contact: '.$objSR->__get('nameReverse'), static::logFile);
                } else {
                    $carddav->update($vcard, $vcardId);
                    log_message('   Update vCard ID='.$vcardId.' in addressbook '.$arrGroup['addressbook_token_id'].'. Contact: '.$objSR->__get('nameReverse'), static::logFile);
                }

                // set values to save them in the database
                $arrNewVCards[$arrGroup['addressbook_token_id']] = ['vcard_id' => $vcardId, 'uid' => $uid, 'checksum' => $checksum];
            }
        }

        Database::getInstance()->prepare('UPDATE tl_bsa_referee SET addressbookVcards=? WHERE id=?')
            ->execute(serialize($arrNewVCards), $objSR->id)
        ;
    }

    private static function getVCardContent($objSR, $excludeGroupId)
    {
        $nl = '
';

        $categories = static::getCategories($objSR, $excludeGroupId);

        // create vCard content string
        $vcard = 'BEGIN:VCARD'.$nl;
        $vcard .= 'VERSION:3.0'.$nl;
        $vcard .= 'UID:%s'.$nl;

        if (!empty($categories)) {
            $vcard .= 'CATEGORIES:'.$categories.$nl;
        }
        $vcard .= 'FN:'.$objSR->__get('firstname').' '.$objSR->__get('lastname').$nl;
        $vcard .= 'N:'.$objSR->__get('lastname').';'.$objSR->__get('firstname').';;;'.$nl;

        if (!empty($objSR->__get('dateOfBirthAsDate'))) {
            $vcard .= 'BDAY;value=date:'.$objSR->__get('dateOfBirthAsDate').$nl;
        }

        if (!empty($objSR->__get('email'))) {
            $vcard .= 'EMAIL;type=INTERNET;type=HOME;type=pref:'.$objSR->__get('email').$nl;
        }

        $pref = ';type=pref';

        if (!empty($objSR->__get('mobile'))) {
            $vcard .= 'TEL;type=CELL;type=VOICE'.$pref.':'.str_replace(' / ', ' ', preg_replace('/^0 */', '+49 ', preg_replace('/^00/', '+', $objSR->__get('mobile')))).$nl;
            $pref = '';
        }

        if (!empty($objSR->__get('phone1'))) {
            $vcard .= 'TEL;type=HOME;type=VOICE'.$pref.':'.str_replace(' / ', ' ', preg_replace('/^0 */', '+49 ', preg_replace('/^00/', '+', $objSR->__get('phone1')))).$nl;
            $pref = '';
        }

        if (!empty($objSR->__get('phone2'))) {
            $vcard .= 'TEL;type=WORK;type=VOICE'.$pref.':'.str_replace(' / ', ' ', preg_replace('/^0 */', '+49 ', preg_replace('/^00/', '+', $objSR->__get('phone2')))).$nl;
        }

        if (!empty($objSR->__get('street')) || !empty($objSR->__get('postal')) || !empty($objSR->__get('city'))) {
            $vcard .= 'ADR;type=HOME;type=pref:;;'.$objSR->__get('street').';'.$objSR->__get('city').';;'.$objSR->__get('postal').';Deutschland'.$nl;
        }

        $vcard .= 'REV:%s'.$nl;
        $vcard .= 'END:VCARD';

        return $vcard;
    }

    private static function getCategories($objSR, $excludeGroupId)
    {
        $query = 'SELECT DISTINCT REPLACE(name, ",", "\,") AS name FROM tl_member_group JOIN tl_bsa_member_group_member_assignment ON tl_member_group.id=tl_bsa_member_group_member_assignment.pid WHERE tl_member_group.id!=? AND tl_bsa_member_group_member_assignment.refereeId=? ORDER BY name';

        $arrNames = Database::getInstance()->prepare($query)
            ->execute($excludeGroupId, $objSR->id)
            ->fetchEach('name')
        ;

        return html_entity_decode(implode(',', $arrNames));
    }

    private static function getUID($objSR, $arrGroup)
    {
        // generate a UID
        if (!empty($arrGroup['uid'])) {
            return $arrGroup['uid'];
        }

        return uniqid($objSR->id.'-');
    }

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
