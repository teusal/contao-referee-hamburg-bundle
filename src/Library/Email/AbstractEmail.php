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
use Contao\User;
use Teusal\ContaoRefereeHamburgBundle\Library\Mailer\AvailableTransports;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * Class AbstractEmail.
 */
abstract class AbstractEmail extends Email
{
    /**
     * The values to be used while replace the used definitions within an email.
     *
     * @var array<string, array<string, mixed>>
     */
    protected $replacementValues = [];

    /**
     * counter of sent mails.
     *
     * @var int
     */
    protected static $countSentMails = 0;

    /**
     * Backend user replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $beUser = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des ausführenden Backend-Benutzers</h1>'],
        ['#BE-USER_NAME#', 'Name des ausführenden Backend-Benutzers'],
        ['#BE-USER_VORNAME#', 'Vorname des ausführenden Backend-Benutzers'],
        ['#BE-USER_NACHNAME#', 'Nachname des ausführenden Backend-Benutzers'],
        ['#BE-USER_EMAIL#', 'E-Mail-Adresse des ausführenden Backend-Benutzers'],
    ];

    /**
     * Backend user signature replacement definition.
     *
     * @var array<array<string>>
     */
    protected static $beUserSignatur = [
        ['#BE-USER_SIGNATUR#', 'Signatur des ausführenden Backend-Benutzers'],
    ];

    /**
     * Webmaster replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $webmaster = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des Webmasters</h1>'],
        ['#WEBMASTER_NAME#', 'Name des Webmasters'],
        ['#WEBMASTER_EMAIL#', 'E-Mail-Adresse des Webmasters'],
    ];

    /**
     * Referee replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $referee = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des angeschriebenen Schiedsrichters</h1>'],
        ['#SR_ANREDE#', 'Anrede des Schiedsrichters (Liebe, Lieber oder Liebe/Lieber)'],
        ['#SR_VORNAME#', 'Vorname des Schiedsrichter'],
        ['#SR_NACHNAME#', 'Nachname des Schiedsrichter'],
        ['#SR_NAME#', 'Vor- und Nachname des Schiedsrichter getrennt durch ein Leerzeichen'],
        ['#SR_NAME_REV#', '"Nachname, Vorname" des Schiedsrichter'],
        ['#SR_EMAIL#', 'E-Mail-Adresse des Schiedsrichters'],
        ['#SR_ALTER#', 'Das Alter des Schiedsrichters'],
    ];

    /**
     * Club replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $club = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des Vereins des angeschriebenen Schiedsrichters</h1>'],
        ['#VEREIN_NAME#', 'Langer Name des Vereins'],
        ['#VEREIN_NAME_KURZ#', 'Kurzer Name des Vereins'],
    ];

    /**
     * Current date replacement definitions.
     *
     * @var array<array<string>>
     */
    protected static $currentDate = [
        ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Ersetzungen durch Daten des Vereins des angeschriebenen Schiedsrichters</h1>'],
        ['#DATUM_TTMMJJJJ#', 'Aktuelles Datum im Format Tag.Monat.Jahr, z.B. 31.12.1990'],
        ['#DATUM_MONAT#', 'Aktueller Monatsname, z.B. Dezember'],
        ['#DATUM_JAHR#', 'Aktuelles Kalenderjahr, z.B. 1990'],
    ];

    /**
     * The id of the referee or null.
     *
     * @var int|null
     */
    private $refereeId;

    /**
     * test or not.
     *
     * @var bool
     */
    private $isTest = false;

    /**
     * development environment or not.
     *
     * @var bool
     */
    private $isDevEnvironment = false;

    /**
     * constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ('dev' === System::getContainer()->getParameter('kernel.environment')) {
            $this->isDevEnvironment = true;
        }

        if (\defined('TL_MODE') && TL_MODE === 'BE' && \defined('BE_USER_LOGGED_IN') && BE_USER_LOGGED_IN) {
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

    /**
     * Set an object property.
     *
     * @param string $strKey   The property name
     * @param mixed  $varValue The property value
     *
     * @throws \Exception If $strKey is unknown
     */
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
                    if (\defined('TL_MODE') && TL_MODE === 'BE') {
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

    /**
     * returns the dca field definition for the selection of the mailer transport.
     *
     * @return array<string, mixed>
     */
    final public function getMailerTransportField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['mailerTransport'],
            'exclude' => true,
            'inputType' => 'select',
            'eval' => ['tl_class' => 'w50', 'includeBlankOption' => true, 'mandatory' => true],
            'options_callback' => ['contao.mailer.available_transports', 'getTransportOptions'],
        ];
    }

    /**
     * returns the dca field definition for the input of the subject.
     *
     * @return array<string, mixed>
     */
    final public function getSubjectField(): array
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['subject'],
            'inputType' => 'text',
            'reference' => array_merge(static::$beUser, static::$webmaster, static::$currentDate, static::$referee, $this->getSubjectReferenceAddons()),
            'eval' => ['helpwizard' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'long clr'],
            'save_callback' => [
                [static::class, 'validateSubject'],
            ],
        ];
    }

    /**
     * returns the dca field definition for the input of the body text.
     *
     * @return array<string, mixed>
     */
    final public function getTextField()
    {
        return [
            'label' => &$GLOBALS['TL_LANG']['mail_config']['text'],
            'inputType' => 'textarea',
            'reference' => array_merge(static::$beUser, static::$beUserSignatur, static::$webmaster, static::$currentDate, static::$referee, static::$club, $this->getTextReferenceAddons()),
            'eval' => ['helpwizard' => true, 'rte' => 'tinyNews', 'mandatory' => true],
            'save_callback' => [
                [static::class, 'validateText'],
            ],
        ];
    }

    /**
     * returns the dca field definition for the input of the blind carbon copy recipients.
     *
     * @return array<string, mixed>
     */
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

    /**
     * setting the BackendUser.
     *
     * @param User $user The user
     */
    final public function setBackendUser(User $user): void
    {
        $arrName = explode(' ', $user->name);
        $lastname = $arrName[\count($arrName) - 1];
        unset($arrName[\count($arrName) - 1]);
        $this->replacementValues['BE-USER'] = [
            'NAME' => $user->name,
            'VORNAME' => implode(' ', $arrName),
            'NACHNAME' => $lastname,
            'EMAIL' => $user->email,
            'SIGNATUR' => $user->__get('signatur_html'),
        ];
    }

    /**
     * setting the referee.
     *
     * @param string|int $refereeId The referee id
     */
    final public function setReferee($refereeId): void
    {
        $objReferee = RefereeModel::findByPk($refereeId);

        if (!isset($objReferee)) {
            $this->refereeId = null;
            unset($this->replacementValues['SR']);
            $this->setClub(0);

            return;
        }

        $this->refereeId = (int) $objReferee->id;

        switch ($objReferee->gender) {
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
        $this->replacementValues['SR']['VORNAME'] = $objReferee->firstname;
        $this->replacementValues['SR']['NACHNAME'] = $objReferee->lastname;
        $this->replacementValues['SR']['NAME'] = $objReferee->firstname.' '.$objReferee->lastname;
        $this->replacementValues['SR']['NAME_REV'] = $objReferee->nameReverse;
        $this->replacementValues['SR']['EMAIL'] = $objReferee->email;
        $this->replacementValues['SR']['ALTER'] = $objReferee->age;

        $this->setClub($objReferee->clubId);
    }

    /**
     * setting the club.
     *
     * @param string|int $clubId The club id
     */
    final public function setClub($clubId): void
    {
        $objClub = ClubModel::findByPk($clubId);

        if (isset($objClub)) {
            $this->replacementValues['VEREIN']['NAME'] = $objClub->name;
            $this->replacementValues['VEREIN']['NAME_KURZ'] = $objClub->nameShort;
        } else {
            $this->replacementValues['VEREIN']['NAME'] = '-';
            $this->replacementValues['VEREIN']['NAME_KURZ'] = '-';
        }
    }

    /**
     * adding more replacements to the list auf replacement values.
     *
     * @param string               $key             The group key of replacements
     * @param array<string, mixed> $arrReplacements The key value pairs of replacements
     */
    public function addReplacements($key, $arrReplacements): void
    {
        $this->replacementValues = array_merge($this->replacementValues, [$key => $arrReplacements]);
    }

    /**
     * mark as a testmail.
     *
     * @param bool $isTest true if it is a test
     */
    public function setTest($isTest): void
    {
        $this->isTest = $isTest;
    }

    /**
     * Send the mail and write in the referee's history.
     *
     * @return bool true if it sent successfully
     */
    public function sendTo(): bool
    {
        /** @phpstan-ignore-next-line */
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

        if ($this->refereeId && !$this->isTest) {
            SRHistory::insert($this->refereeId, null, ['E-Mail', 'INFO'], 'Der Schiedsrichter %s wurde per E-Mail angeschrieben. Betreff: '.$this->__get('subject'), __METHOD__);
        }

        ++static::$countSentMails;

        return $result;
    }

    /**
     * returns an array with referee data to use as reference in testmails.
     *
     * @return array<string, mixed> data of a referee
     */
    public static function getRefereeForTestmail(): array
    {
        if (!\defined('TL_MODE') || TL_MODE !== 'BE') {
            throw new \Exception('getRefereeForTestmail is only available in Backend!');
        }

        // den SR zum Backend-Login anhand der E-Mailadresse laden...
        $data = Database::getInstance()->prepare('SELECT * FROM tl_bsa_referee WHERE email = ?')
            ->limit(1)
            ->execute(BackendUser::getInstance()->email)
            ->fetchAssoc()
        ;

        // den SR zum Backend-Login anhand des Namens laden...
        if (!\is_array($data) || empty($data)) {
            $data = Database::getInstance()->prepare('SELECT * FROM tl_bsa_referee WHERE CONCAT(firstname," ",lastname) = ?')
                ->limit(1)
                ->execute(BackendUser::getInstance()->name)
                ->fetchAssoc()
            ;
        }

        if (!\is_array($data) || empty($data)) {
            $data = [
                'id' => -1,
                'gender' => 'm',
                'firstname' => 'Max',
                'lastname' => 'Mustermann',
                'nameReverse' => 'Mustermann, Max',
            ];
        }

        $data['email'] = BackendUser::getInstance()->email;

        return $data;
    }

    /**
     * provides additional replacements used in subject.
     *
     * @return array<array<string>>
     */
    abstract protected function getSubjectReferenceAddons();

    /**
     * provides additional replacements used in body text.
     *
     * @return array<array<string>>
     */
    abstract protected function getTextReferenceAddons();

    /**
     * returns all keys of the field reference, which is comparable to the keys of all substitutions.
     *
     * @param array<string, mixed> $dcaFieldDefinition The dca field definition
     *
     * @return array<string>
     */
    private function getReplacementsDefined($dcaFieldDefinition)
    {
        $result = [];

        foreach ($dcaFieldDefinition['reference'] as $reference) {
            if (\is_array($reference)) {
                $strReplacementKey = (string) $reference[0];

                if (preg_match($strReplacementKey, '/#.*#/')) {
                    $result[] = $strReplacementKey;
                }
            }
        }

        return $result;
    }

    /**
     * extracts the replacement that are used in the specified string.
     *
     * @param string $varValue The value including to be checked
     *
     * @return array<string> all necessary replacement keys
     */
    private function getReplacementsNeeded($varValue)
    {
        $result = [];

        if (0 !== substr_count($varValue, '#') % 2) {
            throw new \Exception('Das Zeichen # umschließt Ersetzungen und darf daher nur dafür benutzt werden. Eine Ersetzung muss immer mit # beginnen und enden.');
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

    /**
     * validating of all used replacements, if they are defined.
     *
     * @param array<string> $arrDefined List of all replacement definitions
     * @param array<string> $arrNeeded  List of all required replacements
     */
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

    /**
     * replaces all used replacements in the string.
     *
     * @param string $varValue The string to be replaced
     *
     * @return string The string after replacements
     */
    private function doReplace($varValue): string
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
            System::getContainer()->get('monolog.logger.contao.error')->error('Replacement not found ('.implode(';', $arrNeeded).')');

            throw new \Exception('Replacement not found ('.implode(';', $arrNeeded).')');
        }

        return str_replace('<p></p>', '', $varValue);
    }
}
