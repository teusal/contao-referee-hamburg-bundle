<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library;

use Contao\BackendUser;
use Contao\Config;
use Contao\Database;
use Contao\Date;
use Contao\MemberModel;
use Contao\Message;
use Contao\System;
use Symfony\Component\Mailer\Exception\TransportException;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\LoginEmail;

/**
 * Class MemberCreator.
 */
class MemberCreator extends System
{
    private $createdLogins = [];

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(Database::class);
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Erzeugen eines Logins, wenn es notwendig ist.
     */
    public function createLoginIfNeeded($srID)
    {
        if (!Config::get('bsa_import_login_create')) {
            return false;
        }

        if (!$srID) {
            return false;
        }
        $sr = $this->Database->prepare('SELECT tl_bsa_schiedsrichter.* FROM tl_bsa_schiedsrichter LEFT JOIN tl_member ON tl_bsa_schiedsrichter.id = tl_member.schiedsrichter WHERE tl_bsa_schiedsrichter.id=? AND tl_bsa_schiedsrichter.email!=? AND tl_bsa_schiedsrichter.deleted=? AND tl_member.id IS NULL')
            ->execute($srID, '', '')
            ->fetchAssoc()
        ;

        if (!\is_array($sr)) {
            return false;
        }

        $jetzt = new Date();
        $username = $sr['vorname'].'.'.$sr['nachname'];
        $suchen = ['ä', 'ö', 'ü', 'ß', ' ', '\\', '/'];
        $ersetzen = ['ae', 'oe', 'ue', 'ss', '', '-', '-'];
        $username = str_replace($suchen, $ersetzen, strtolower($username));

        if (!$this->checkUsername($username)) {
            $usernameAppended = $username.'.'.Date::parse('Y', $sr['geburtsdatum']);

            if (!$this->checkUsername($usernameAppended)) {
                $usernameAppended = $username.'.'.Date::parse(Config::get('dateFormat'), $sr['geburtsdatum']);

                if (!$this->checkUsername($usernameAppended)) {
                    $usernameAppended = $username.'.'.$sr['ausweisnummer'];

                    if (!$this->checkUsername($usernameAppended)) {
                        Message::addError('Es konnte kein Login für SR '.$sr['vorname'].' '.$sr['nachname'].' ID='.$sr['id'].' angelegt werden. Der Benutzername '.$username.' existiert bereits.');

                        return false;
                    }
                }
            }
            $username = $usernameAppended;
            unset($usernameAppended);
        }

        $password = substr(md5(uniqid(mt_rand(), true)), 0, 8);
        $pwd = password_hash($password, PASSWORD_DEFAULT);

        $res = $this->Database->prepare('INSERT INTO tl_member (tstamp, firstname, lastname, email, groups, login, username, password, schiedsrichter, dateAdded) VALUES (?,?,?,?,?,?,?,?,?,?)')
            ->execute($jetzt->__get('tstamp'), $sr['vorname'], $sr['nachname'], $sr['email'], serialize([]), true, $username, $pwd, $sr['id'], $jetzt->__get('tstamp'))
        ;

        if (0 === $res->__get('affectedRows')) {
            return false;
        }

        SRHistory::insert($srID, $res->insertId, ['Login', 'ADD'], 'Der Login des Schiedsrichters %s wurde mit dem Benutzernamen "%s" erstellt.', __METHOD__);

        $this->createdLogins[$srID] = ['sr' => $sr, 'username' => $username, 'password' => $password, 'notificationMailSent' => false];

        return true;
    }

    /**
     * Sendet Mails mit der Benachrichtigung an den User, für den ein Login angelegt wurde. Dabei wird die gesamte interne
     * Liste createdLogins abgearbeitet.
     * Optional kann eine ID eines Schiedsrichters übergeben werden. Wenn diese gesetzt ist, so wird der Mailversand nur
     * für den entsprechenden Schiedsrichter verarbeitet.
     */
    public function sendNotificationMails($srID = 0)
    {
        if (!Config::get('bsa_import_login_send_mail')) {
            return false;
        }

        if (0 !== $srID) {
            if (!isset($this->createdLogins[$srID]) || $this->createdLogins[$srID]['notificationMailSent']) {
                return true;
            }

            $this->sendNotificationMail($this->createdLogins[$srID]['sr'], $this->createdLogins[$srID]['username'], $this->createdLogins[$srID]['password']);
            $this->createdLogins[$srID]['notificationMailSent'] = true;
        }

        $mailSuccessfullySent = true;

        foreach (array_keys($this->createdLogins) as $key) {
            $success = $this->sendNotificationMails($key);
            $mailSuccessfullySent = $mailSuccessfullySent && $success;
        }

        return $mailSuccessfullySent;
    }

    /**
     * Sendet eine Testmail.
     */
    public function sendTestmail()
    {
        if (TL_MODE !== 'BE') {
            return false;
        }

        // throw new \Exception('test');

        // den SR zum Backend-Login laden...
        $data = Database::getInstance()->prepare('SELECT id FROM tl_bsa_schiedsrichter WHERE CONCAT(vorname," ",nachname) = ?')
            ->limit(1)
            ->execute($this->User->name)
            ->fetchAssoc()
        ;

        if (!isset($data) || !\is_array($data) || empty($data)) {
            $data = ['id' => 461];
        }
        $data['email'] = $this->User->email;

        try {
            return $this->sendNotificationMail($data, $this->User->username, 'XYZ', true);
        } catch (TransportException $e) {
            // TODO i.e. handle send as denied exception
            throw $e;
        }
    }

    /**
     * Liefert das Array mit angelegten Logins.
     */
    public function getCreatedLogins()
    {
        return $this->createdLogins;
    }

    /**
     * Prüft den übergebenen Benutzernamen. Wenn er in der Datenbank bereits existiert liefert die Methode false zurück.
     */
    private function checkUsername($usernameToCheck)
    {
        $objExisting = MemberModel::findOneBy('username', $usernameToCheck);

        return null === $objExisting;
    }

    /**
     * Sendet die Mail mit der Benachrichtigung an den übergebenen User.
     */
    private function sendNotificationMail($sr, $username, $password, $test = false)
    {
        $mailSuccessfullySent = false;

        // Vorgaben aus den Konfigurationen laden
        $strSenderEMail = Config::get('bsa_import_login_mail_sender');

        if (!\strlen($strSenderEMail)) {
            $strSenderEMail = $this->User->email;
        }

        $objEmail = new LoginEmail();
        $objEmail->setSchiedsrichter($sr['id']);
        $objEmail->setLogin($username, $password);
        $objEmail->setTest($test);

        $objEmail->__set('from', $strSenderEMail);
        $objEmail->__set('fromName', Config::get('bsa_import_login_mail_sender_name'));
        $objEmail->__set('subject', Config::get('bsa_import_login_mail_subject'));
        $objEmail->__set('html', System::getContainer()->get('contao.insert_tag.parser')->replaceInline(Config::get('bsa_import_login_mail_text') ?? ''));

        if (!$test && \strlen(Config::get('bsa_import_login_mail_bcc'))) {
            $objEmail->sendBcc(explode(',', Config::get('bsa_import_login_mail_bcc')));
        }

        if (\strlen($sr['email'])) {
            $mailSuccessfullySent = $objEmail->sendTo([$sr['email']]);
        }

        return $mailSuccessfullySent;
    }
}
