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
use Contao\Model\Collection;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\AbstractEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\BSAEmail;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

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
            $objReferee = RefereeModel::getRefereesWithBirthdayToday('email<>""');
            $arrSR = (null !== $objReferee ? $objReferee->fetchAll() : []);
        } else {
            $data = AbstractEmail::getRefereeForTestmail();
            $arrSR = [$data];
        }

        if (empty($arrSR)) {
            if (\defined('TL_MODE') && TL_MODE === 'BE') {
                Message::addInfo('BSA-Geburtstagsmail wurde ausgeführt, es sind keine Mails zu versenden');
            } else {
                System::log('BSA-Geburtstagsmail wurde ausgeführt, es sind keine Mails zu versenden', 'BSA Geburtstag sendMail()', TL_CRON);
            }

            return;
        }

        foreach ($arrSR as $sr) {
            $objEmail = new BSAEmail();
            $objEmail->setMailerTransport($strMailerTransport);
            $objEmail->setReferee($sr['id']);
            $objEmail->setTest($test);

            try {
                $objEmail->__set('subject', $strSubject);
                $objEmail->__set('html', $strText);

                if (!empty($strBcc)) {
                    $objEmail->sendBcc(explode(',', $strBcc));
                }

                $objEmail->sendTo([$sr['email']]);

                if (\defined('TL_MODE') && TL_MODE === 'BE') {
                    Message::addInfo('BSA-Geburtstagsmail an '.$sr['email'].' gesendet');
                } else {
                    System::log('BSA-Geburtstagsmail an '.$sr['firstname'].' '.$sr['lastname'].' &lt;'.$sr['email'].'&gt; gesendet', 'BSA Geburtstag sendMail()', TL_CRON);
                }
            } catch (\Exception $e) {
                if (!\defined('TL_MODE') || TL_MODE !== 'BE') {
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

        $objReferee = RefereeModel::getRefereesWithBirthdayNextDays(0, 7);

        if (null === $objReferee) {
            return '';
        }

        [$arrDates, $arrBirthdays] = $this->getBirthdayDetailsForSystemMessagesEmailAsHtml($objReferee);

        return '
<div class="tl_confirm">
  <div style="float:left; margin-right:5px;">Kommende Geburtstage:</div>
  <div style="float:left; margin-right:5px;">'.implode('<br/>', $arrDates).'</div>
  <div>'.implode('<br/>', $arrBirthdays).'</div>
</div>
';
    }

    /**
     * Sends an info by mail about upcoming birthdays (today and in 4 days).
     *
     * @param bool $test Specifies a testmail, optional, default false
     */
    public function sendInfoMail($test = false): void
    {
        if (!Config::get('geb_info_aktiv')) {
            return;
        }

        System::loadLanguageFile('default');

        $html = '';

        $objReferee = RefereeModel::getRefereesWithBirthdayToday();

        if (null !== $objReferee) {
            $html .= '<h2>Geburtstage heute:</h2>';
            $html .= $this->getBirthdayDetailsForEmailAsHtml($objReferee);
        }

        $objReferee = RefereeModel::getRefereesWithBirthdayNextDays(4, 4);

        if (null !== $objReferee) {
            $html .= '<h2>Geburtstage in 4 Tagen:</h2>';
            $html .= $this->getBirthdayDetailsForEmailAsHtml($objReferee);
        }

        if (!\strlen($html)) {
            return;
        }

        $objEmail = new BSAEmail();
        $objEmail->setMailerTransport(Config::get('geb_info_mailer_transport'));
        $objEmail->setTest($test);
        $objEmail->__set('subject', 'Kommende Geburtstage im '.Config::get('bsa_name').' '.$GLOBALS['BSA_NAMES'][Config::get('bsa_name')]);
        $objEmail->__set('html', $html);
        $objEmail->sendTo($test ? $this->User->email : explode(',', Config::get('geb_info_recipients')));
    }

    /**
     * returns arrays with dates and birtday informations for all referees grouped by days.
     *
     * @param Collection<RefereeModel>|array<RefereeModel>|RefereeModel $objReferee
     *
     * @return array<array<string>>
     */
    private function getBirthdayDetailsForSystemMessagesEmailAsHtml($objReferee)
    {
        $strMessage = '%s (%s) %s. Geburtstag';

        $arrDates = [];
        $arrBirthdays = [];

        $strLastDate = '';

        while ($objReferee->next()) {
            if ($strLastDate === Date::parse('d.m.', $objReferee->dateOfBirth)) {
                $arrDates[] = '&nbsp;';
            } else {
                $strLastDate = Date::parse('d.m.', $objReferee->dateOfBirth);
                $arrDates[] = $strLastDate.':';
            }

            $message = sprintf($strMessage, $objReferee->nameReverse, ClubModel::findVerein($objReferee->clubId)->nameShort, $objReferee->age + ($objReferee->hasBirthday ? 0 : 1));

            if (!\strlen($objReferee->email)) {
                $message .= ' <span style="color:#CC3333;">!keine E-Mailadresse!</span>';
            }
            $arrBirthdays[] = $message;
        }

        return [$arrDates, $arrBirthdays];
    }

    /**
     * returns html for each referee with informations about date of birth, age and contact data.
     *
     * @param Collection|array<RefereeModel>|RefereeModel $objReferee
     *
     * @return string
     */
    private function getBirthdayDetailsForEmailAsHtml($objReferee)
    {
        $html = '';

        while ($objReferee->next()) {
            $html .= '<p>';
            $html .= '<strong>'.$objReferee->nameReverse.'</strong><br/>';
            $html .= Date::parse(Config::get('dateFormat'), $objReferee->dateOfBirth).' ('.($objReferee->age + ($objReferee->hasBirthday ? 0 : 1)).'. Geburtstag)<br/>';
            $html .= 'Verein: '.ClubModel::findVerein($objReferee->clubId)->nameShort.'<br/>';
            $html .= 'E-Mailadresse: '.(\strlen($objReferee->email) ? $objReferee->email : '-').'<br/>';

            if (\strlen($objReferee->phone1)) {
                $html .= 'Tel privat: '.$objReferee->phone1.'<br/>';
            }

            if (\strlen($objReferee->phone2)) {
                $html .= 'Tel dienstl.: '.$objReferee->phone2.'<br/>';
            }

            if (\strlen($objReferee->mobile)) {
                $html .= 'Tel mobil: '.$objReferee->mobile.'<br/>';
            }
            $html .= '</p>';
        }

        return $html;
    }
}
