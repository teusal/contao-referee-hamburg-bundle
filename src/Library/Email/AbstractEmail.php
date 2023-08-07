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
use Contao\Database;
use Contao\DataContainer;
use Contao\Date;
use Contao\Email;
use Contao\Message;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\Mailer\AvailableTransports;
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
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des ausführenden Backend-Benutzers</h1>'],
        ['#BE-USER_NAME#', 'Name des ausführenden Backend-Benutzers'],
        ['#BE-USER_VORNAME#', 'Vorname des ausführenden Backend-Benutzers'],
        ['#BE-USER_NACHNAME#', 'Nachname des ausführenden Backend-Benutzers'],
        ['#BE-USER_EMAIL#', 'E-Mail-Adresse des ausführenden Backend-Benutzers'],
    ];

    protected static $beUserSignatur = [
        ['#BE-USER_SIGNATUR#', 'Signatur des ausführenden Backend-Benutzers'],
    ];

    protected static $webmaster = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des Webmasters</h1>'],
        ['#WEBMASTER_NAME#', 'Name des Webmasters'],
        ['#WEBMASTER_EMAIL#', 'E-Mail-Adresse des Webmasters'],
    ];

    protected static $sr = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des angeschriebenen Schiedsrichters</h1>'],
        ['#SR_ANREDE#', 'Anrede des Schiedsrichters (Liebe, Lieber oder Liebe/Lieber)'],
        ['#SR_VORNAME#', 'Vorname des Schiedsrichter'],
        ['#SR_NACHNAME#', 'Nachname des Schiedsrichter'],
        ['#SR_NAME#', 'Vor- und Nachname des Schiedsrichter getrennt durch ein Leerzeichen'],
        ['#SR_NAME_REV#', '"Nachname, Vorname" des Schiedsrichter'],
        ['#SR_EMAIL#', 'E-Mail-Adresse des Schiedsrichters'],
        ['#SR_ALTER#', 'Das Alter des Schiedsrichters'],
    ];

    protected static $verein = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des Vereins des angeschriebenen Schiedsrichters</h1>'],
        ['#VEREIN_NAME#', 'Langer Name des Vereins'],
        ['#VEREIN_NAME_KURZ#', 'Kurzer Name des Vereins'],
    ];

    protected static $currentDate = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des Vereins des angeschriebenen Schiedsrichters</h1>'],
        ['#DATUM_TTMMJJJJ#', 'Aktuelles Datum im Format Tag.Monat.Jahr, z.B. 31.12.1990'],
        ['#DATUM_MONAT#', 'Aktueller Monatsname, z.B. Dezember'],
        ['#DATUM_JAHR#', 'Aktuelles Kalenderjahr, z.B. 1990'],
    ];

    private $srID;
    private $isTest = false;
    private $isDevEnvironment = false;

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();

        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            $this->isDevEnvironment = true;
        }

        if (TL_MODE === 'BE') {
            $this->setBackendUser(BackendUser::getInstance());
        }

        $this->replacementValues['WEBMASTER'] = [
            'NAME' => 'Webmaster ('.$GLOBALS['BSA_NAMES'][Config::get('bsa_name')].')',
            'EMAIL' => Config::get('adminEmail'),
        ];

        $this->replacementValues['DATUM'] = [
            'TTMMJJJJ' => Date::parse(Config::get('dateFormat'), time()),
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
                        $varValue = Config::get('adminEmail');
                    }
                }
                break;
        }

        parent::__set($strKey, $varValue);
    }

    /**
     * validating and setting the mailer transport.
     *
     * @param string $mailerTransport
     */
    final public function setMailerTransport($mailerTransport): void
    {
        /** @var AvailableTransports $availableTransports */
        $availableTransports = System::getContainer()->get('contao.mailer.available_transports');

        if (null === $availableTransports->getTransport($mailerTransport)) {
            throw new \Exception('mailerTransport not set or not found');
        }
        $this->addHeader('X-Transport', $mailerTransport);
    }

    final public function getMailerTransportField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['mailerTransport'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => false, 'mandatory' => true, 'includeBlankOption' => true],
            'options_callback' => ['contao.mailer.available_transports', 'getAllTransportOptions'],
        ];
    }

    final public function getSubjectField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['subject'],
            'inputType' => 'text',
            'reference' => array_merge(static::$beUser, static::$webmaster, static::$currentDate, static::$sr, $this->getSubjectReferenceAddons()),
            'eval' => ['helpwizard' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'long clr'],
            'save_callback' => [
                [static::class, 'validateSubject'],
            ],
        ];
    }

    final public function getTextField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['text'],
            'inputType' => 'textarea',
            'reference' => array_merge(static::$beUser, static::$beUserSignatur, static::$webmaster, static::$currentDate, static::$sr, static::$verein, $this->getTextReferenceAddons()),
            'eval' => ['helpwizard' => true, 'rte' => 'tinyNews', 'mandatory' => true],
            'save_callback' => [
                [static::class, 'validateText'],
            ],
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

    /**
     * Validating the subject.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    final public function validateSubject($varValue, DataContainer $dc)
    {
        $arrDefined = $this->getReplacementsDefined($this->getSubjectField());
        $arrNeeded = $this->getReplacementsNeeded($varValue);
        $this->validateReplacements($arrDefined, $arrNeeded);

        return $varValue;
    }

    /**
     * Validating the text.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
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

        if (!isset($objSR)) {
            $this->srID = null;
            unset($this->replacementValues['SR']);

            return;
        }

        $this->srID = $objSR->id;

        switch ($objSR->__get('geschlecht')) {
                case 'm':
                case 'male':
                    $this->replacementValues['SR']['ANREDE'] = 'Lieber';
                    break;
                case 'w':
                case 'female':
                    $this->replacementValues['SR']['ANREDE'] = 'Liebe';
                    break;

                default:
                    $this->replacementValues['SR']['ANREDE'] = 'Liebe/Lieber';
                    break;
            }
        $this->replacementValues['SR']['VORNAME'] = $objSR->__get('vorname');
        $this->replacementValues['SR']['NACHNAME'] = $objSR->__get('nachname');
        $this->replacementValues['SR']['NAME'] = $objSR->__get('vorname').' '.$objSR->__get('nachname');
        $this->replacementValues['SR']['NAME_REV'] = $objSR->__get('name_rev');
        $this->replacementValues['SR']['EMAIL'] = $objSR->__get('email');
        $this->replacementValues['SR']['ALTER'] = BsaSchiedsrichterModel::getAlter($objSR);

        $this->setVerein($objSR->__get('verein'));
    }

    final public function setVerein($intVerein): void
    {
        $objVerein = BsaVereinModel::findByPk($intVerein);

        if (!isset($objVerein)) {
            unset($this->replacementValues['VEREIN']);

            return;
        }

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
     *
     * @return bool
     */
    public function sendTo()
    {
        if (!$this->objMessage->getHeaders()->has('X-Transport')) {
            throw new \Exception('mailerTransport not set or not found');
        }

        if (static::$countSentMails > 9) {
            static::$countSentMails = 0;
            sleep(1);
        }

        $arrArgs = \func_get_args();
        $email = $arrArgs[0];

        // replace email in dev environment
        if ($this->isDevEnvironment) {
            $email = 'mail@alexteuscher.de';
        }
        // remove cc and bcc in dev environment as well as for testmails
        if ($this->isDevEnvironment || $this->isTest) {
            parent::sendCc([]);
            parent::sendBcc([]);
        }

        $result = parent::sendTo($email);

        if ($this->srID && !$this->isTest) {
            SRHistory::insert($this->srID, null, ['E-Mail', 'INFO'], 'Der Schiedsrichters %s wurde per E-Mail angeschrieben. Betreff: '.$this->__get('subject'), __METHOD__);
        }

        ++static::$countSentMails;

        return $result;
    }

    /**
     * returns an array with referee data to use as reference in testmails.
     *
     * @return array data of a referee
     */
    public static function getRefereeForTestmail(): array
    {
        if (TL_MODE !== 'BE') {
            throw new \Exception('getRefereeForTestmail is only available in Backend!');
        }

        // den SR zum Backend-Login anhand der E-Mailadresse laden...
        $data = Database::getInstance()->prepare('SELECT id FROM tl_bsa_schiedsrichter WHERE email = ?')
            ->limit(1)
            ->execute(BackendUser::getInstance()->email)
            ->fetchAssoc()
        ;

        // den SR zum Backend-Login anhand des Namens laden...
        if (!isset($data) || !\is_array($data) || empty($data)) {
            $data = Database::getInstance()->prepare('SELECT id FROM tl_bsa_schiedsrichter WHERE CONCAT(vorname," ",nachname) = ?')
                ->limit(1)
                ->execute(BackendUser::getInstance()->name)
                ->fetchAssoc()
            ;
        }

        if (!isset($data) || !\is_array($data) || empty($data)) {
            $data = [
                'id' => -1,
                'geschlecht' => 'm',
                'vorname' => 'Max',
                'nachname' => 'Mustermann',
                'name_rev' => 'Mustermann, Max',
            ];
        }

        $data['email'] = BackendUser::getInstance()->email;

        return $data;
    }

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
