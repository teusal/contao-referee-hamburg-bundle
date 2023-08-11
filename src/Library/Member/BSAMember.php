<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Member;

use Contao\BackendUser;
use Contao\Config;
use Contao\Controller;
use Contao\Database;
use Contao\DataContainer;
use Contao\Environment;
use Contao\Input;
use Contao\MemberModel;
use Contao\Message;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;

/**
 * Class BSAMember.
 *
 * @property MemberCreator $MemberCreator
 */
class BSAMember extends System
{
    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(Database::class, 'Database');
        $this->import(BackendUser::class, 'User');
        $this->import(MemberCreator::class, 'MemberCreator');
    }

    /**
     * Liefert das Array mit angelegten Logins.
     *
     * @param DataContainer|int $var
     */
    public function executeSubmitSchiedsrichter($var): void
    {
        $intId = 0;

        if ($var instanceof DataContainer) {
            $intId = $var->id;
        } else {
            $intId = (int) $var;
        }

        // Den Schiedsrichter laden
        $objSR = RefereeModel::findReferee($intId);

        if (!isset($objSR)) {
            throw new \Exception('Schiedsrichter zu ID '.$intId.' nicht gefunden!');
        }

        // Die personenbezogenen Daten an den Login tl_member übernehmen
        $this->setPersonalData($objSR);

        // nur Obleute oder aktive Schiedsrichter sollen automatisch verwaltete Logins haben
        $needsLogin = $this->needsLogin($objSR->id);

        // existierenden Login tl_member laden
        $objMember = MemberModel::findOneBy('refereeId', $objSR->id);

        if ($objSR->deleted || !$needsLogin) {
            // Mitglied deaktivieren
            if (isset($objMember) && !$objMember->disable) {
                // Login deaktivieren
                $objMember->disable = true;
                $objMember->save();
                // aus allen Gruppen entfernen
                BSAMemberGroup::deleteFromGroups($objSR->id);
            }
        } else {
            if (!isset($objMember) && $needsLogin) {
                // Login anlegen
                $this->MemberCreator->createLoginIfNeeded($objSR->id);

                if (Config::get('bsa_import_login_send_mail')) {
                    $this->MemberCreator->sendNotificationMails($objSR->id);
                }

                // Den neuen Login laden
                $objMember = MemberModel::findOneBy('refereeId', $objSR->id);
            }

            if (isset($objMember) && $objMember->__get('disable')) {
                // Login aktivieren
                $objMember->disable = false;
                $objMember->save();
            }

            // Die Automatik-Grupen verwalten
            BSAMemberGroup::handleAutomaticGroups($objSR->id);
        }
    }

    /**
     * Liefert das Array mit angelegten Logins.
     */
    public function getCreatedLogins()
    {
        return $this->MemberCreator->getCreatedLogins();
    }

    /**
     * Prüft alle Schiedsrichter und vereinslose Personen ob sie einen neuen Login benötigen und legt den Zugang an.
     */
    public function createNeededLogins(): void
    {
        if ('createNeeded' !== Input::get('key')) {
            return;
        }

        $redirectUrl = str_replace('&key=createNeeded', '', Environment::get('request'));

        if (!Config::get('bsa_import_login_create')) {
            Message::addError($GLOBALS['TL_LANG']['ERROR']['login_create_inactive']);
            Controller::redirect($redirectUrl);
        }

        $arrSR = $this->Database->prepare('SELECT tl_bsa_referee.id, tl_bsa_referee.nameReverse FROM tl_bsa_referee LEFT JOIN tl_member ON tl_bsa_referee.id = tl_member.refereeId WHERE tl_bsa_referee.email!=? AND tl_bsa_referee.deleted=? AND tl_member.id IS NULL ORDER BY nameReverse')
            ->execute('', '')
            ->fetchAllAssoc()
        ;

        if (!empty($arrSR)) {
            foreach ($arrSR as $key => $sr) {
                if (!$this->needsLogin($sr['id'])) {
                    unset($arrSR[$key]);
                }
            }
        }

        if (empty($arrSR)) {
            Message::addInfo($GLOBALS['TL_LANG']['INFO']['login_create_not_required']);
            Controller::redirect($redirectUrl);
        }

        $arrLoginNames = [];

        foreach ($arrSR as $sr) {
            $this->executeSubmitSchiedsrichter($sr['id']);

            if (\is_array($this->MemberCreator->getCreatedLogins()) && \array_key_exists($sr['id'], $this->MemberCreator->getCreatedLogins())) {
                $arrLoginNames[] = $sr['nameReverse'];

                if (Config::get('bsa_import_login_send_mail')) {
                    $this->MemberCreator->sendNotificationMails($sr['id']);
                }
            } else {
                Message::addError(sprintf($GLOBALS['TL_LANG']['ERROR']['login_create_error'], $sr['nameReverse']));
            }
        }

        Message::addConfirmation(sprintf($GLOBALS['TL_LANG']['INFO']['login_created'], \count($arrLoginNames), implode('; ', $arrLoginNames)));
        Controller::redirect($redirectUrl);
    }

    /**
     * checks whether a login is needed or not.
     *
     * @return bool
     */
    private function needsLogin($intID)
    {
        return RefereeModel::isClubReferee($intID) || ClubChairmanModel::isChairman($intID);
    }

    /**
     * Setzt Vor-, Nachname und E-Mail sowie weitere personenbezogene Daten am Login tl_member.
     */
    private function setPersonalData($objSR): void
    {
        $objMember = MemberModel::findOneBy('refereeId', $objSR->id);

        if (isset($objMember)) {
            $gender = 'misc';

            if ('m' === $objSR->gender) {
                $gender = 'male';
            } elseif ('w' === $objSR->gender) {
                $gender = 'female';
            }

            $objMember->__set('firstname', $objSR->firstname);
            $objMember->__set('lastname', $objSR->lastname);
            $objMember->__set('dateOfBirth', $objSR->dateOfBirth);
            $objMember->__set('gender', $gender);
            $objMember->__set('street', $objSR->street);
            $objMember->__set('postal', $objSR->postal);
            $objMember->__set('city', $objSR->city);
            $objMember->__set('phone', $objSR->phone1);
            $objMember->__set('mobile', $objSR->mobile);
            $objMember->__set('fax', $objSR->fax);
            $objMember->__set('email', $objSR->getFriendlyEmail());

            $objMember->save();
        }
    }
}
