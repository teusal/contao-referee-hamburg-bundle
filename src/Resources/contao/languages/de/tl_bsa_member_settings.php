<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

if (!defined('TL_ROOT')) {
    die('You can not access this file directly!');
}

/*
 * Legends
 */
$GLOBALS['TL_LANG']['tl_bsa_member_settings']['legend_member_create_mails'] = 'Einstellungen für neue Logins';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_bsa_member_settings']['bsa_import_login_create'] = ['Logins für Schiedsrichter während des Imports anlegen?', 'Wählen Sie diese Option, damit Logins für Schiedsrichter während des Imports angelegt werden.'];
$GLOBALS['TL_LANG']['tl_bsa_member_settings']['bsa_import_login_send_mail'] = ['Mail mit Logindaten für angelegte Logins versenden?', 'Wählen Sie diese Option, damit Mails mit Logindaten für angelegte Logins versendet werden.'];

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_bsa_member_settings']['edit'] = 'Einstellungen für Logins';
$GLOBALS['TL_LANG']['tl_bsa_member_settings']['send_test'] = 'Testmail senden';
$GLOBALS['TL_LANG']['tl_bsa_member_settings']['sendTestConfirm'] = 'Soll eine Testmail an %s gesendet werden?';
