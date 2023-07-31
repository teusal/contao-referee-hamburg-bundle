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
$GLOBALS['TL_LANG']['tl_user']['smtp_legend'] = 'SMTP-Einstellungen';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_user']['signatur_html'] = ['E-Mail Signatur', 'Definieren sie die Signatur des Benutzer, diese wird beim Versenden von E-Mails automatisch angehängt.'];
$GLOBALS['TL_LANG']['tl_user']['useSMTP'] = ['Eigener SMTP-Server', 'Einen eigenen SMTP-Server für den Newsletter-Versand verwenden.'];
$GLOBALS['TL_LANG']['tl_user']['smtpHost'] = ['SMTP-Hostname', 'Bitte geben Sie den Hostnamen des SMTP-Servers ein.'];
$GLOBALS['TL_LANG']['tl_user']['smtpUser'] = ['SMTP-Benutzername', 'Hier können Sie den SMTP-Benutzernamen eingeben.'];
$GLOBALS['TL_LANG']['tl_user']['smtpPass'] = ['SMTP-Passwort', 'Hier können Sie das SMTP-Passwort eingeben. Für O365 Accounts muss das App-Kennwort verwendet werden.'];
$GLOBALS['TL_LANG']['tl_user']['smtpEnc'] = ['SMTP-Verschlüsselung', 'Hier können Sie eine Verschlüsselungsmethode auswählen (SSL oder TLS).'];
$GLOBALS['TL_LANG']['tl_user']['smtpPort'] = ['SMTP-Portnummer', 'Bitte geben Sie die Portnummer des SMTP-Servers ein.'];
