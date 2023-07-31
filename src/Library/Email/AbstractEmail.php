<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Email;

use Contao\BackendUser;
use Contao\Config;
use Contao\DataContainer;
use Contao\Date;
use Contao\Email;
use Contao\Message;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSchiedsrichterModel;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinModel;

/**
 * Class AbstractEmail.
 */
abstract class AbstractEmail extends Email
{
    protected $replacementValues = [];

    protected static $countSentMails = 0;

    protected static $beUser = [
        'Ersetzungen durch Daten des ausführenden Backend-Benutzers',
        ['#BE-USER_NAME#', 'Name des ausführenden Backend-Benutzers'],
        ['#BE-USER_VORNAME#', 'Vorname des ausführenden Backend-Benutzers'],
        ['#BE-USER_NACHNAME#', 'Nachname des ausführenden Backend-Benutzers'],
        ['#BE-USER_EMAIL#', 'E-Mail-Adresse des ausführenden Backend-Benutzers'],
    ];

    protected static $beUserSignatur = [
        ['#BE-USER_SIGNATUR#', 'Signatur des ausführenden Backend-Benutzers'],
    ];

    protected static $webmaster = [
        'Ersetzungen durch Daten des Webmasters',
        ['#WEBMASTER_NAME#', 'Name des Webmasters'],
        ['#WEBMASTER_EMAIL#', 'E-Mail-Adresse des Webmasters'],
    ];

    protected static $sr = [
        'Ersetzungen durch Daten des angeschriebenen Schiedsrichters',
        ['#SR_VORNAME#', 'Vorname des Schiedsrichter'],
        ['#SR_NACHNAME#', 'Nachname des Schiedsrichter'],
        ['#SR_NAME#', 'Vor- und Nachname des Schiedsrichter getrennt durch ein Leerzeichen'],
        ['#SR_NAME_REV#', '"Nachname, Vorname" des Schiedsrichter'],
        ['#SR_EMAIL#', 'E-Mail-Adresse des Schiedsrichters'],
        ['#SR_ALTER#', 'Das Alter des Schiedsrichters'],
    ];

    protected static $verein = [
        'Ersetzungen durch Daten des Vereins des angeschriebenen Schiedsrichters',
        ['#VEREIN_NAME#', 'Langer Name des Vereins'],
        ['#VEREIN_NAME_KURZ#', 'Kurzer Name des Vereins'],
    ];

    protected static $currentDate = [
        'Ersetzungen durch Daten des Vereins des angeschriebenen Schiedsrichters',
        ['#DATUM_TTMMJJJJ#', 'Aktuelles Datum im Format Tag.Monat.Jahr, z.B. 31.12.1990'],
        ['#DATUM_MONAT#', 'Aktueller Monatsname, z.B. Dezember'],
        ['#DATUM_JAHR#', 'Aktuelles Kalenderjahr, z.B. 1990'],
    ];

    private $srID;
    private $isTest = false;

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();

        if (TL_MODE === 'BE') {
            $this->setBackendUser(BackendUser::getInstance());

            // $user = \BackendUser::getInstance();
            // if ($user->useSMTP) {
            // 	\Config::set('useSMTP', true);
            // 	\Config::set('smtpHost', $user->smtpHost);
            // 	\Config::set('smtpUser', $user->smtpUser);
            // 	\Config::set('smtpPass', $user->smtpPass);
            // 	\Config::set('smtpEnc', $user->smtpEnc);
            // 	\Config::set('smtpPort', $user->smtpPort);
            // }
        }

        $this->replacementValues['WEBMASTER'] = [
            'NAME' => 'Webmaster ('.Config::get('websiteTitle').')',
            'EMAIL' => $GLOBALS['TL_CONFIG']['adminEmail'],
        ];

        $this->replacementValues['DATUM'] = [
            'TTMMJJJJ' => Date::parse($GLOBALS['TL_CONFIG']['dateFormat'], time()),
            'MONAT' => Date::parse('F', time()),
            'JAHR' => Date::parse('Y', time()),
        ];
    }

    public function __set($strKey, $varValue): void
    {
        switch ($strKey) {
            case 'fromName':
            case 'subject':
            case 'html':
                $varValue = $this->doReplace($varValue);
                break;
            case 'from':
                if (!\strlen($varValue)) {
                    if (TL_MODE === 'BE') {
                        $varValue = BackendUser::getInstance()->email;
                    } else {
                        $varValue = $GLOBALS['TL_CONFIG']['adminEmail'];
                    }
                }
                break;
        }

        parent::__set($strKey, $varValue);
    }

    final public function getSenderNameField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['senderName'],
            'inputType' => 'text',
            'reference' => array_merge(static::$beUser, static::$webmaster, $this->getSenderNameReferenceAddons()),
            'eval' => ['helpwizard' => true, 'decodeEntities' => true, 'maxlength' => 128, 'mandatory' => true, 'tl_class' => 'w50'],
            'save_callback' => [[static::class, 'validateSenderName']],
        ];
    }

    final public function getSenderField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['sender'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'email', 'mandatory' => false, 'tl_class' => 'w50'],
        ];
    }

    final public function getSubjectField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['subject'],
            'inputType' => 'text',
            'reference' => array_merge(static::$beUser, static::$webmaster, static::$currentDate, static::$sr, $this->getSubjectReferenceAddons()),
            'eval' => ['helpwizard' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'long clr'],
            'save_callback' => [[static::class, 'validateSubject']],
        ];
    }

    final public function getTextField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['text'],
            'inputType' => 'textarea',
            'reference' => array_merge(static::$beUser, static::$beUserSignatur, static::$webmaster, static::$currentDate, static::$sr, static::$verein, $this->getTextReferenceAddons()),
            'eval' => ['helpwizard' => true, 'rte' => 'tinyNews', 'mandatory' => true],
            'save_callback' => [[static::class, 'validateText']],
        ];
    }

    final public function getBccField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['bcc'],
            'inputType' => 'text',
            'eval' => ['rgxp' => 'emails', 'mandatory' => false, 'tl_class' => 'long'],
        ];
    }

    final public function validateSenderName($varValue, DataContainer $dc)
    {
        $arrDefined = $this->getReplacementsDefined($this->getSenderNameField());
        $arrNeeded = $this->getReplacementsNeeded($varValue);
        $this->validateReplacements($arrDefined, $arrNeeded);

        return $varValue;
    }

    final public function validateSubject($varValue, DataContainer $dc)
    {
        $arrDefined = $this->getReplacementsDefined($this->getSubjectField());
        $arrNeeded = $this->getReplacementsNeeded($varValue);
        $this->validateReplacements($arrDefined, $arrNeeded);

        return $varValue;
    }

    final public function validateText($varValue, DataContainer $dc)
    {
        $arrDefined = $this->getReplacementsDefined($this->getTextField());
        $arrNeeded = $this->getReplacementsNeeded($varValue);
        $this->validateReplacements($arrDefined, $arrNeeded);

        return $varValue;
    }

    final public function setBackendUser(BackendUser $user): void
    {
        $arrName = explode(' ', $user->name);
        $nachname = $arrName[\count($arrName) - 1];
        unset($arrName[\count($arrName) - 1]);
        $this->replacementValues['BE-USER'] = [
            'NAME' => $user->name,
            'VORNAME' => implode(' ', $arrName),
            'NACHNAME' => $nachname,
            'EMAIL' => $user->email,
            'SIGNATUR' => $user->signatur_html,
        ];
    }

    final public function setSchiedsrichter($intSR): void
    {
        $objSR = BsaSchiedsrichterModel::findSchiedsrichter($intSR);

        if (isset($objSR)) {
            $this->srID = $objSR->id;

            $this->replacementValues['SR']['VORNAME'] = $objSR->__get('vorname');
            $this->replacementValues['SR']['NACHNAME'] = $objSR->__get('nachname');
            $this->replacementValues['SR']['NAME'] = $objSR->__get('vorname').' '.$objSR->__get('nachname');
            $this->replacementValues['SR']['NAME_REV'] = $objSR->__get('name_rev');
            $this->replacementValues['SR']['EMAIL'] = $objSR->__get('email');
            $this->replacementValues['SR']['ALTER'] = BsaSchiedsrichterModel::getAlter($objSR);

            $this->setVerein($objSR->__get('verein'));
        }
    }

    final public function setVerein($intVerein): void
    {
        $objVerein = BsaVereinModel::findByPk($intVerein);
        $this->replacementValues['VEREIN']['NAME'] = (isset($objVerein) ? $objVerein->__get('name') : '-');
        $this->replacementValues['VEREIN']['NAME_KURZ'] = (isset($objVerein) ? $objVerein->__get('name_kurz') : '-');
    }

    public function addReplacements($key, $arrReplacements): void
    {
        $this->replacementValues = array_merge($this->replacementValues, [$key => $arrReplacements]);
    }

    public function setTest($isTest): void
    {
        $this->isTest = $isTest;
    }

    /**
     * Sendet die Mail und schreibt in die SRHistory.
     */
    public function sendTo()
    {
        if (static::$countSentMails > 9) {
            static::$countSentMails = 0;
            sleep(1);
        }

        $arrArgs = \func_get_args();
        $result = parent::sendTo($arrArgs[0]);

        if ($this->srID && !$this->isTest) {
            SRHistory::insert($this->srID, null, ['E-Mail', 'INFO'], 'Der Schiedsrichters %s wurde per E-Mail angeschrieben. Betreff: '.$this->__get('subject'), __METHOD__);
        }

        ++static::$countSentMails;

        return $result;
    }

    /**
     * Liefert die gesetzte Bcc-Adresse.
     */
    public function getBcc()
    {
        return $this->objMessage->getBcc();
    }

    abstract protected function getSenderNameReferenceAddons();

    abstract protected function getSubjectReferenceAddons();

    abstract protected function getTextReferenceAddons();

    private function getReplacementsDefined($field)
    {
        $result = [];

        foreach ($field['reference'] as $value) {
            if (\is_array($value)) {
                $result[] = $value[0];
            }
        }

        return $result;
    }

    private function getReplacementsNeeded($varValue)
    {
        $result = [];

        if (0 !== substr_count($varValue, '#') % 2) {
            throw new \Exception('Das Zeichen # umschließt Ersetzungen und darf daher nur dafür beutzt werden. Eine Ersetzung muss immer mit # beginnen und enden.');
        }

        $offset = 0;

        do {
            $pos1 = strpos($varValue, '#', $offset);
            $pos2 = strpos($varValue, '#', $pos1 + 1);

            if (false !== $pos1 && false !== $pos2) {
                $result[] = substr($varValue, $pos1, $pos2 - $pos1 + 1);
                $offset = $pos2 + 1;
            }
        } while (false !== $pos1 && false !== $pos2);

        return $result;
    }

    private function validateReplacements($arrDefined, $arrNeeded): void
    {
        $arrNotFound = [];

        foreach ($arrNeeded as $needed) {
            if (!\in_array($needed, $arrDefined, true)) {
                $arrNotFound[] = $needed;
            }
        }

        if (!empty($arrNotFound)) {
            throw new \Exception('Ersetzung ist nicht definiert: '.htmlspecialchars(implode(', ', $arrNotFound)));
        }
    }

    private function doReplace($varValue)
    {
        if (!\strlen($varValue)) {
            return $varValue;
        }

        $arrSearch = [];
        $arrReplace = [];

        foreach ($this->replacementValues as $groupKey => $groupValues) {
            foreach ($groupValues as $key => $value) {
                $arrSearch[] = sprintf('#%s_%s#', $groupKey, $key);
                $arrReplace[] = html_entity_decode((string) $value);
            }
        }

        $varValue = str_replace($arrSearch, $arrReplace, $varValue);

        $arrNeeded = $this->getReplacementsNeeded($varValue);

        if (!empty($arrNeeded)) {
            Message::addError('Ersetzung ist nicht definiert: '.htmlspecialchars(implode(', ', $arrNeeded)).'<br/>Die E-Mail wurde nicht versendet');
            System::log('Replacement not found ('.implode(';', $arrNeeded).')', 'AbstractEmail::doReplace', ERROR);

            throw new \Exception('Replacement not found ('.implode(';', $arrNeeded).')');
        }

        return str_replace('<p></p>', '', $varValue);
    }
}
