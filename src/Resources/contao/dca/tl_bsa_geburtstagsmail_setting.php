<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\BackendUser;
use Contao\Input;
use Contao\Message;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\BSAEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\Geburtstag;

$objEmail = new BSAEmail();

/*
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_bsa_geburtstagsmail_setting'] = [
    // Config
    'config' => [
        'dataContainer' => 'File',
        'closed' => true,
        'onload_callback' => [['tl_bsa_geburtstagsmail_setting', 'onLoad']],
    ],

    'edit' => [
        'buttons_callback' => [['tl_bsa_geburtstagsmail_setting', 'addSendButton']],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['geb_mailing_aktiv', 'geb_info_aktiv'],
        'default' => '{bsa_geburtstagsmail_legend},geb_mailing_aktiv;{bsa_geburtstag_info_legend},geb_info_aktiv',
    ],

    // Subpalettes
    'subpalettes' => [
        'geb_mailing_aktiv' => 'geb_mailer_transport,geb_subject,geb_text,geb_bcc',
        'geb_info_aktiv' => 'geb_info_mailer_transport,geb_info_recipients',
    ],

    // Fields
    'fields' => [
        'geb_mailing_aktiv' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
        ],
        'geb_mailer_transport' => $objEmail->getMailerTransportField(),
        'geb_subject' => $objEmail->getSubjectField(),
        'geb_text' => $objEmail->getTextField(),
        'geb_bcc' => $objEmail->getBccField(),
        'geb_info_aktiv' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
        ],
        'geb_info_mailer_transport' => $objEmail->getMailerTransportField(),
        'geb_info_recipients' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'rgxp' => 'emails', 'tl_class' => 'w50 clr'],
        ],
    ],
];

/**
 * Class tl_bsa_geburtstagsmail_setting.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @property Geburtstag $Geburtstag
 */
class tl_bsa_geburtstagsmail_setting extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
        $this->import(Geburtstag::class, 'Geburtstag');
    }

    /**
     * Sendet einen Test.
     */
    public function onLoad($dc): void
    {
        if (!empty(Input::post('sendTest'))) {
            $this->Geburtstag->sendMail(true);
            Message::addConfirmation('Testmail wurde versendet');
            $this->reload();
        }

        if (!empty(Input::post('sendInfoTest'))) {
            $this->Geburtstag->sendInfoMail(true);
            Message::addConfirmation('Info-Testmail wurde versendet');
            $this->reload();
        }
    }

    /**
     * Adding the send testmail button.
     *
     * @param array         $arrButtons Array of strings
     * @param DataContainer $dc         Data Container object
     *
     * @return array
     */
    public function addSendButton($arrButtons, $dc)
    {
        $arrButtons['sendTest'] = '<button type="submit" name="sendTest" value="sendTest" class="tl_submit" onclick="if (!confirm(\''.sprintf($GLOBALS['TL_LANG']['tl_bsa_geburtstagsmail_setting']['sendTestConfirm'], $this->User->email).'\')) return false;">'.$GLOBALS['TL_LANG']['tl_bsa_geburtstagsmail_setting']['send_test'].'</button>';
        $arrButtons['sendInfoTest'] = '<button type="submit" name="sendInfoTest" value="sendInfoTest" class="tl_submit" onclick="if (!confirm(\''.sprintf($GLOBALS['TL_LANG']['tl_bsa_geburtstagsmail_setting']['sendInfoTestConfirm'], $this->User->email).'\')) return false;">'.$GLOBALS['TL_LANG']['tl_bsa_geburtstagsmail_setting']['send_info_test'].'</button>';

        return $arrButtons;
    }
}
