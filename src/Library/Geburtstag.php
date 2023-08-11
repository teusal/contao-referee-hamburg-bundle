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
use Contao\Date;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\AbstractEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\BSAEmail;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;

/**
 * Class Geburtstag.
 */
class Geburtstag extends System
{
    /**
     * Konstruktor.
     */
    public function __construct()
    {
        System::loadLanguageFile('default');
        $this->import(BackendUser::class, 'User');
        parent::__construct();
    }

    /**
     * Methode sendet Geburtstagsgrüße per Email.
     *
     * @param bool $test
     */
    public function sendMail($test = false): void
    {
        if (!Config::get('geb_mailing_aktiv')) {
            return;
        }

        $strMailerTransport = Config::get('geb_mailer_transport');

        $strSubject = Config::get('geb_subject');
        $strText = Config::get('geb_text');
        $strBcc = Config::get('geb_bcc');

        if (!$test) {
            $arrSR = RefereeModel::getPersonWithBirthdayToday('email<>""');
        } else {
            $data = AbstractEmail::getRefereeForTestmail();
            $arrSR = [$data];
        }

        if (empty($arrSR)) {
            if (TL_MODE === 'BE') {
                Message::addInfo('BSA-Geburtstagsmail wurde ausgeführt, es sind keine Mails zu versenden');
            } else {
                System::log('BSA-Geburtstagsmail wurde ausgeführt, es sind keine Mails zu versenden', 'BSA Geburtstag sendMail()', TL_CRON);
            }

            return;
        }

        foreach ($arrSR as $sr) {
            $objEmail = new BSAEmail();
            $objEmail->setMailerTransport($strMailerTransport);
            $objEmail->setSchiedsrichter($sr['id']);
            $objEmail->setTest($test);

            try {
                $objEmail->__set('subject', $strSubject);
                $objEmail->__set('html', $strText);

                if (!empty($strBcc)) {
                    $objEmail->sendBcc(explode(',', $strBcc));
                }

                $objEmail->sendTo([$sr['email']]);

                if (TL_MODE === 'BE') {
                    Message::addInfo('BSA-Geburtstagsmail an '.$sr['email'].' gesendet');
                } else {
                    System::log('BSA-Geburtstagsmail an '.$sr['firstname'].' '.$sr['lastname'].' &lt;'.$sr['email'].'&gt; gesendet', 'BSA Geburtstag sendMail()', TL_CRON);
                }
            } catch (\Exception $e) {
                if (TL_MODE === 'BE') {
                    System::log('BSA-Geburtstagsmail an '.$sr['firstname'].' '.$sr['lastname'].' &lt;'.$sr['email'].'&gt; konnte nicht gesendet werden: '.$e->getMessage(), 'BSA Geburtstag sendMail()', TL_CRON);
                }
            }
        }
    }

    /**
     * Kommende Geburtstage im Backend auflisten.
     *
     * @return string|null an info about upcomming birthdays
     */
    public function getSystemMessages()
    {
        if (!$this->User->hasAccess('schiedsrichter', 'modules') && $this->User->hasAccess('bsa_geburtstagsmail_settings', 'modules')) {
            return null;
        }

        if (!empty(Input::get('do'))) {
            return null;
        }

        $strMessage = '%s (%s) %s. Geburtstag';

        $arrBirthdays = [];
        $arrDates = [];

        $strLastDate = '';

        $arrSR = RefereeModel::getPersonWithBirthdayToday();

        foreach ($arrSR as $sr) {
            if ($strLastDate === Date::parse('d.m.', $sr['dateOfBirth'])) {
                $arrDates[] = '&nbsp;';
            } else {
                $strLastDate = Date::parse('d.m.', $sr['dateOfBirth']);
                $arrDates[] = $strLastDate.':';
            }

            $message = sprintf($strMessage, $sr['nameReverse'], ClubModel::findVerein($sr['clubId'])->__get('nameShort'), RefereeModel::getAge($sr));

            if (empty($sr['email'])) {
                $message .= ' <span style="color:#CC3333;">!keine E-Mailadresse!</span>';
            }
            $arrBirthdays[] = $message;
        }

        $arrSR = RefereeModel::getPersonWithBirthdayNextDays(1, 7);

        foreach ($arrSR as $sr) {
            if ($strLastDate === Date::parse('d.m.', $sr['dateOfBirth'])) {
                $arrDates[] = '&nbsp;';
            } else {
                $strLastDate = Date::parse('d.m.', $sr['dateOfBirth']);
                $arrDates[] = $strLastDate.':';
            }

            $message = sprintf($strMessage, $sr['nameReverse'], ClubModel::findVerein($sr['clubId'])->__get('nameShort'), RefereeModel::getAge($sr) + 1);

            if (!\strlen($sr['email'])) {
                $message .= ' <span style="color:#CC3333;">!keine E-Mailadresse!</span>';
            }
            $arrBirthdays[] = $message;
        }

        if (empty($arrBirthdays)) {
            return '';
        }

        return '
<div class="tl_confirm">
  <div style="float:left; margin-right:5px;">Kommende Geburtstage:</div>
  <div style="float:left; margin-right:5px;">'.implode('<br/>', $arrDates).'</div>
  <div>'.implode('<br/>', $arrBirthdays).'</div>
</div>
';
    }

    /**
     * Sendet eine Info per Mail über kommende Geburtstage (heute und in 4 Tagen).
     */
    public function sendInfoMail($test = false): void
    {
        if (!Config::get('geb_info_aktiv')) {
            return;
        }

        $html = '';

        $arrSR = RefereeModel::getPersonWithBirthdayToday();

        if (!empty($arrSR)) {
            $html .= '<h2>Geburtstage heute:</h2>';
        }

        foreach ($arrSR as $sr) {
            $html .= '<p>';
            $html .= '<strong>'.$sr['nameReverse'].'</strong><br/>';
            $html .= Date::parse(Config::get('dateFormat'), $sr['dateOfBirth']).' ('.RefereeModel::getAge($sr).'. Geburtstag)<br/>';
            $html .= 'Verein: '.ClubModel::findVerein($sr['clubId'])->__get('nameShort').'<br/>';
            $html .= 'E-Mailadresse: '.(\strlen($sr['email']) ? $sr['email'] : '-').'<br/>';

            if (\strlen($sr['phone1'])) {
                $html .= 'Tel privat: '.$sr['phone1'].'<br/>';
            }

            if (\strlen($sr['phone2'])) {
                $html .= 'Tel dienstl.: '.$sr['phone2'].'<br/>';
            }

            if (\strlen($sr['mobile'])) {
                $html .= 'Tel mobil: '.$sr['mobile'].'<br/>';
            }
            $html .= '</p>';
        }

        $arrSR = RefereeModel::getPersonWithBirthdayNextDays(4, 4);

        if (!empty($arrSR)) {
            $html .= '<h2>Geburtstage in 4 Tagen:</h2>';
        }

        foreach ($arrSR as $sr) {
            $html .= '<p>';
            $html .= '<strong>'.$sr['nameReverse'].'</strong><br/>';
            $html .= Date::parse(Config::get('dateFormat'), $sr['dateOfBirth']).' ('.(RefereeModel::getAge($sr) + 1).'. Geburtstag)<br/>';
            $html .= 'Verein: '.ClubModel::findVerein($sr['clubId'])->__get('nameShort').'<br/>';
            $html .= 'E-Mailadresse: '.(\strlen($sr['email']) ? $sr['email'] : '-').'<br/>';

            if (\strlen($sr['phone1'])) {
                $html .= 'Tel privat: '.$sr['phone1'].'<br/>';
            }

            if (\strlen($sr['phone2'])) {
                $html .= 'Tel dienstl.: '.$sr['phone2'].'<br/>';
            }

            if (\strlen($sr['mobile'])) {
                $html .= 'Tel mobil: '.$sr['mobile'].'<br/>';
            }
            $html .= 'Adresse: '.$sr['street'].'; '.$sr['postal'].' '.$sr['city'].'<br/>';
            $html .= '</p>';
        }

        if (!\strlen($html)) {
            return;
        }

        $objEmail = new BSAEmail();
        $objEmail->setMailerTransport(Config::get('geb_info_mailer_transport'));
        $objEmail->setTest($test);
        $objEmail->__set('subject', 'Kommende Geburtstage im '.$GLOBALS['BSA_NAMES'][Config::get('bsa_name')]);
        $objEmail->__set('html', $html);
        $objEmail->sendTo($test ? $this->User->email : explode(',', Config::get('geb_info_recipients')));
    }
}
