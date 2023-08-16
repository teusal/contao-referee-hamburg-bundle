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
use Contao\Database;
use Contao\Date;
use Contao\MemberModel;
use Contao\Message;
use Contao\System;
use Symfony\Component\Mailer\Exception\TransportException;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\AbstractEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\LoginEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * Class MemberCreator.
 */
class MemberCreator extends System
{
    /**
     * Collection of generated logins in a process.
     *
     * @var array<integer, array<string, array<mixed>|bool|string>>
     */
    private $createdLogins = [];

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(Database::class, 'Database');
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Generate a login for the specified referee when it is necessary.
     *
     * @param int $srId The id of the referee
     *
     * @return bool true if the requested login ist created
     */
    public function createLoginIfNeeded($srId)
    {
        if (!Config::get('bsa_import_login_create')) {
            return false;
        }

        if (!$srId) {
            return false;
        }

        $objMember = MemberModel::findOneBy('refereeId', $srId);

        if (isset($objMember)) {
            return false;
        }

        $objReferee = RefereeModel::findOneBy(['id = ?', 'email != ?', 'deleted = ?'], [$srId, '', '']);

        if (!isset($objReferee)) {
            return false;
        }

        $now = new Date();
        $username = str_replace(
            ['ä', 'ö', 'ü', 'ß', ' ', '\\', '/'],
            ['ae', 'oe', 'ue', 'ss', '', '-', '-'],
            strtolower($objReferee->firstname.'.'.$objReferee->lastname)
        );

        if (!$this->checkUsername($username)) {
            $usernameAppended = $username.'.'.Date::parse('Y', $objReferee->dateOfBirth);

            if (!$this->checkUsername($usernameAppended)) {
                $usernameAppended = $username.'.'.Date::parse(Config::get('dateFormat'), $objReferee->dateOfBirth);

                if (!$this->checkUsername($usernameAppended)) {
                    $usernameAppended = $username.'.'.$objReferee->cardNumber;

                    if (!$this->checkUsername($usernameAppended)) {
                        Message::addError('Es konnte kein Login für SR '.$objReferee->firstname.' '.$objReferee->lastname.' ID='.$objReferee->id.' angelegt werden. Der Benutzername '.$username.' existiert bereits.');

                        return false;
                    }
                }
            }
            $username = $usernameAppended;
            unset($usernameAppended);
        }

        $password = substr(md5(uniqid((string) (mt_rand()), true)), 0, 8);
        $pwd = password_hash($password, PASSWORD_DEFAULT);

        $gender = '';

        if ('m' === $objReferee->gender) {
            $gender = 'male';
        } elseif ('w' === $objReferee->gender) {
            $gender = 'female';
        }

        $res = $this->Database->prepare('INSERT INTO tl_member (tstamp, firstname, lastname, dateOfBirth, gender, street, postal, city, phone, mobile, fax, email, groups, login, username, password, refereeId, dateAdded) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute($now->__get('tstamp'), $objReferee->firstname, $objReferee->lastname, $objReferee->dateOfBirth, $gender, $objReferee->street, $objReferee->postal, $objReferee->city, $objReferee->phone1, $objReferee->mobile, $objReferee->fax, $objReferee->friendlyEmail, serialize([]), true, $username, $pwd, $objReferee->id, $now->__get('tstamp'))
        ;

        if (0 === $res->__get('affectedRows')) {
            return false;
        }

        SRHistory::insert($srId, $res->__get('insertId'), ['Login', 'ADD'], 'Der Login des Schiedsrichters %s wurde mit dem Benutzernamen "%s" erstellt.', __METHOD__);

        $this->createdLogins[$srId] = ['sr' => $objReferee->row(), 'username' => $username, 'password' => $password, 'notificationMailSent' => false];

        return true;
    }

    /**
     * Sends mails with the notification to the user for whom a login was created. The entire internal
     * list of createdLogins is processed.
     * Optionally, an ID of a referee can be transferred. If this is set, the mail dispatch is processed
     * only for the corresponding referee.
     *
     * @param int $srId optional id of a referee
     *
     * @return bool true if all emails were sent successfully
     */
    public function sendNotificationMails($srId = 0)
    {
        if (!Config::get('bsa_import_login_send_mail')) {
            return false;
        }

        if (0 !== $srId) {
            if (!isset($this->createdLogins[$srId]) || $this->createdLogins[$srId]['notificationMailSent']) {
                return true;
            }

            $this->sendNotificationMail($this->createdLogins[$srId]['sr'], $this->createdLogins[$srId]['username'], $this->createdLogins[$srId]['password']);
            $this->createdLogins[$srId]['notificationMailSent'] = true;
        }

        $mailSuccessfullySent = true;

        foreach (array_keys($this->createdLogins) as $key) {
            $success = $this->sendNotificationMails($key);
            $mailSuccessfullySent = $mailSuccessfullySent && $success;
        }

        return $mailSuccessfullySent;
    }

    /**
     * Sends a test mail.
     *
     * @return bool true if the email was sent successfully
     */
    public static function sendTestmail()
    {
        if (!\defined('TL_MODE') || TL_MODE !== 'BE') {
            return false;
        }

        $data = AbstractEmail::getRefereeForTestmail();

        try {
            return self::sendNotificationMail($data, BackendUser::getInstance()->username, 'XYZ', true);
        } catch (TransportException $e) {
            // TODO i.e. handle send as denied exception
            throw $e;
        }
    }

    /**
     * Returns the array with created logins.
     *
     * @return array<integer, array<string, array<mixed>|bool|string>>
     */
    public function getCreatedLogins(): array
    {
        return $this->createdLogins;
    }

    /**
     * Checks the passed user name. If it already exists in the database the method returns false.
     *
     * @param string $username The username you want to check
     *
     * @return bool true if the username is unique
     */
    private function checkUsername($username): bool
    {
        $objExisting = MemberModel::findOneBy('username', $username);

        return null === $objExisting;
    }

    /**
     * Sends the mail with the notification to the passed user.
     *
     * @param array<string, mixed> $sr       The referee
     * @param string               $username The new username
     * @param string               $password The new password
     * @param bool                 $test     true if it is a test, default false
     *
     * @return bool true if the email was sent successfully
     */
    private static function sendNotificationMail($sr, $username, $password, $test = false): bool
    {
        $mailSuccessfullySent = false;

        $objEmail = new LoginEmail();
        $objEmail->setMailerTransport(Config::get('bsa_import_login_mail_mailer_transport'));
        $objEmail->setReferee($sr['id']);
        $objEmail->setLogin($username, $password);
        $objEmail->setTest($test);

        $objEmail->__set('subject', Config::get('bsa_import_login_mail_subject'));
        $objEmail->__set('html', System::getContainer()->get('contao.insert_tag.parser')->replaceInline(Config::get('bsa_import_login_mail_text') ?? ''));

        if (!$test && !empty(Config::get('bsa_import_login_mail_bcc'))) {
            $objEmail->sendBcc(explode(',', Config::get('bsa_import_login_mail_bcc')));
        }

        if (!empty($sr['email'])) {
            $mailSuccessfullySent = $objEmail->sendTo([$sr['email']]);
        }

        return $mailSuccessfullySent;
    }
}
