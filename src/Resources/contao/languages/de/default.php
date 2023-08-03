<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['CTE']['backlink'] = ['Zurück-Link', 'Erzeugt einen Link zur vorherigen Seite.'];

/*
 * referee contents
 */
$GLOBALS['BSA'] = ['alster', 'bergedorf', 'harburg', 'nord', 'ost', 'pinneberg', 'unterelbe', 'walddoerfer'];
$GLOBALS['BSA_NAMES'] = [
    'vsa_nfv_dfb' => 'VSA/NFV/DFB',
    'alster' => 'BSA Alster',
    'bergedorf' => 'BSA Bergedorf',
    'harburg' => 'BSA Harburg',
    'nord' => 'BSA Nord',
    'ost' => 'BSA Ost',
    'pinneberg' => 'BSA Pinneberg',
    'unterelbe' => 'BSA Unterelbe',
    'walddoerfer' => 'BSA Walddörfer',
];

/*
 * list of known genders
 */
$GLOBALS['TL_LANG']['genders'] = [
    'm' => 'männlich',
    'w' => 'weiblich',
    'd' => 'Divers',
];

/*
 * Fields
 */
$GLOBALS['TL_LANG']['mail_config']['mailerTransport'] = ['Mailer-Transport', 'Hier geben Sie den Mailer-Transport der E-Mail an.'];
$GLOBALS['TL_LANG']['mail_config']['senderName'] = ['Absendername', 'Hier geben Sie den Namen des Absenders ein, Ersetzungen sind möglich (siehe Hilfe).'];
$GLOBALS['TL_LANG']['mail_config']['sender'] = ['Absenderadresse', 'Hier können Sie eine individuelle Absenderadresse eingeben. Wenn das Feld leer gelassen wird, wird die E-Mailadresse des Backend-Benutzers benutzt.'];
$GLOBALS['TL_LANG']['mail_config']['subject'] = ['Betreff', 'Bitte geben Sie den Betreff der E-Mail ein, Ersetzungen sind möglich (siehe Hilfe).'];
$GLOBALS['TL_LANG']['mail_config']['text'] = ['Text', 'Hier geben Sie den Text-Inhalt der E-Mail ein, Ersetzungen sind möglich (siehe Hilfe)'];
$GLOBALS['TL_LANG']['mail_config']['bcc'] = ['BCC-Adressen', 'Tragen sie hier durch Kommas getrennt gültige E-Mail-Adressen ein, damit eine Blindkopie an diese Mailadressen gesendet wird. Wenn das Feld leer gelassen wird, wird die E-Mail ohne BCC versendet.'];
