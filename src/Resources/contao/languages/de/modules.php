<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

 use Contao\Config;

 Config::set('bsa_name', 'nord');

 /*
  * Menu entry
  */
$GLOBALS['TL_LANG']['MOD']['bsa_verein_schiedsrichter'] = ['Vereine & Personen', 'Konfigurieren Sie hier die aktuelle Saison.'];

/*
 * Back end modules
 */
$GLOBALS['TL_LANG']['MOD']['season'] = ['Konfiguration Saisons', 'Konfigurieren Sie hier die aktuelle oder erstellen Sie eine neue Saison.'];
$GLOBALS['TL_LANG']['MOD']['vereinslos'] = ['vereinslose Personen', 'Verwaltung von Personen, die keinem Verein des '.(strlen($GLOBALS['BSA_NAMES'][Config::get('bsa_name')]) ? $GLOBALS['BSA_NAMES'][Config::get('bsa_name')] : '').' zugeordnet werden können.'];
$GLOBALS['TL_LANG']['MOD']['verein'] = ['Vereine', 'Bearbeitung der Vereine des '.(strlen($GLOBALS['BSA_NAMES'][Config::get('bsa_name')]) ? $GLOBALS['BSA_NAMES'][Config::get('bsa_name')] : '').'.'];
$GLOBALS['TL_LANG']['MOD']['obmann'] = ['Obleute & Stellvertreter', 'Einstellungen des Obmanns und der Stellvertreter der Vereine des '.(strlen($GLOBALS['BSA_NAMES'][Config::get('bsa_name')]) ? $GLOBALS['BSA_NAMES'][Config::get('bsa_name')] : '').'.'];
$GLOBALS['TL_LANG']['MOD']['schiedsrichter'] = ['Schiedsrichter', 'Bearbeitung der Schiedsrichter des '.(strlen($GLOBALS['BSA_NAMES'][Config::get('bsa_name')]) ? $GLOBALS['BSA_NAMES'][Config::get('bsa_name')] : '').'.'];
$GLOBALS['TL_LANG']['MOD']['freigaben'] = ['Web-Freigaben', 'Zeigt die Web-Freigaben der Schiedsrichter des '.(strlen($GLOBALS['BSA_NAMES'][Config::get('bsa_name')]) ? $GLOBALS['BSA_NAMES'][Config::get('bsa_name')] : '').' an.'];
$GLOBALS['TL_LANG']['MOD']['schiedsrichter_historie'] = ['Schiedsrichter-Historie', 'Zeigt die Historie bzw. die Änderungen von Schiedsrichtern an.'];

/*
 * Front end modules
 */
$GLOBALS['TL_LANG']['FMD']['bsa_schiedsrichter'] = ['Schiedsrichter', 'Zeigt die Schiedsrichter an.'];
$GLOBALS['TL_LANG']['FMD']['bsa_vereine'] = ['Verein', 'Zeigt die Vereine an.'];
$GLOBALS['TL_LANG']['FMD']['bsa_freigaben'] = ['Freigaben-Bearbeitung', 'Modul zur Bearbeitung der Web-Freigaben im Frontend.'];
