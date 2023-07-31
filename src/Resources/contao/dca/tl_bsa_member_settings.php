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
use Contao\DC_File;
use Contao\Input;
use Contao\Message;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\LoginEmail;
use Teusal\ContaoRefereeHamburgBundle\Library\MemberCreator;

$objEmail = new LoginEmail();

$GLOBALS['TL_DCA']['tl_bsa_member_settings'] = [
    // Config
    'config' => [
        'dataContainer' => DC_File::class,
        'closed' => true,
        'onload_callback' => [[tl_bsa_member_settings::class, 'onLoad']],
    ],

    'edit' => [
        'buttons_callback' => [[tl_bsa_member_settings::class, 'addSendButton']],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['bsa_import_login_send_mail'],
        'default' => '{legend_member_create_mails},bsa_import_login_create,bsa_import_login_send_mail',
    ],

    // Subpalettes
    'subpalettes' => [
        'bsa_import_login_send_mail' => 'bsa_import_login_mail_sender_name,bsa_import_login_mail_sender,bsa_import_login_mail_subject,bsa_import_login_mail_text,bsa_import_login_mail_bcc',
    ],

    // Fields
    'fields' => [
        'bsa_import_login_create' => [
            'inputType' => 'checkbox',
        ],
        'bsa_import_login_send_mail' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
        ],
        'bsa_import_login_mail_sender_name' => $objEmail->getSenderNameField(),
        'bsa_import_login_mail_sender' => $objEmail->getSenderField(),
        'bsa_import_login_mail_subject' => $objEmail->getSubjectField(),
        'bsa_import_login_mail_text' => $objEmail->getTextField(),
        'bsa_import_login_mail_bcc' => $objEmail->getBccField(),
    ],
];

/**
 * Class tl_bsa_member_settings.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @property MemberCreator $MemberCreator
 */
class tl_bsa_member_settings extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
        // $this->import(MemberCreator::class);
    }

    /**
     * Sendet einen Test.
     *
     * @param DataContainer|null $dc
     */
    public function onLoad($dc): void
    {
        if (!empty(Input::post('sendTest'))) {
            // $this->import(MemberCreator::class);
            if ((new MemberCreator())->sendTestmail()) {
                Message::addConfirmation('Testmail wurde versendet');
            }
            $this->reload();
        }
    }

    public function addSendButton($arrButtons, $dc)
    {
        $arrButtons['sendTest'] = '<button type="submit" name="sendTest" value="sendTest" class="tl_submit" onclick="if (!confirm(\''.sprintf($GLOBALS['TL_LANG']['tl_bsa_member_settings']['sendTestConfirm'], $this->User->email).'\')) return false;">'.$GLOBALS['TL_LANG']['tl_bsa_member_settings']['send_test'].'</button>';

        return $arrButtons;
    }
}
