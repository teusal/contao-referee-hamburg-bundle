<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Input;

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['sr_id'] = ['Teilnehmer', 'Wählen Sie den Schiedsrichter oder Obmann aus, der an dieser Veranstaltung teilgenommen hat.'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['sr'] = ['Teilnehmer', 'Der Teilnehmer, der an dieser Veranstaltung teilgenommen hat.'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typ'] = ['Typ', 'Wählen Sie die Art der Teilnahme aus: anwesend, entschuldigt, Spiel oder Verhandlung'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['a'] = 'anwesend';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['e'] = 'entschuldigt';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['s'] = 'Spiel';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['v'] = 'Verhandlung';

$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['best'] = 'Bestanden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['9,5r'] = 'Abbruch nach 9,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['9,0r'] = 'Abbruch nach 9,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['8,5r'] = 'Abbruch nach 8,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['8,0r'] = 'Abbruch nach 8,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['7,5r'] = 'Abbruch nach 7,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['7,0r'] = 'Abbruch nach 7,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['6,5r'] = 'Abbruch nach 6,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['6,0r'] = 'Abbruch nach 6,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['5,5r'] = 'Abbruch nach 5,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['5,0r'] = 'Abbruch nach 5,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['4,5r'] = 'Abbruch nach 4,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['4,0r'] = 'Abbruch nach 4,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['3,5r'] = 'Abbruch nach 3,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['3,0r'] = 'Abbruch nach 3,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['2,5r'] = 'Abbruch nach 2,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['2,0r'] = 'Abbruch nach 2,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['1,5r'] = 'Abbruch nach 1,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['1,0r'] = 'Abbruch nach 1,0 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['0,5r'] = 'Abbruch nach 0,5 Runden';
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen']['9,0r'] = 'Abbruch nach 9,0 Runden';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['new'] = ['Neuer Teilnehmer', 'Einen neuen Teilnehmer zu der Veranstaltung hinzufügen.'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['edit'] = ['Bearbeiten', 'Diesen Teilnehmer bearbeiten.'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['delete'] = ['Löschen', 'Diesen Teilnehmer löschen.'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['spiele'] = ['Spiele eintragen', 'Veranstaltungsbesuche angesetzter Schiedsrichter eintragen'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['besucher'] = ['Besucher eintragen', 'Veranstaltungsbesuche anwesender/entschuldigter Schiedsrichter eintragen'];
$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['import'] = ['CSV Import', 'Import einer csv-Datei'];

if ('sitzung' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['sr'] = ['Teilnehmer', 'Wählen Sie den Schiedsrichter aus, der an dieser Sitzung teilgenommen hat.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['new'] = ['Neuer Teilnehmer', 'Einen neuen Teilnehmer zu dieser Sitzung hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['spiele'] = ['Spiele eintragen', 'Sitzungsbesuche angesetzter Schiedsrichter eintragen'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['besucher'] = ['Besucher eintragen', 'Sitzungsbesuche anwesender/entschuldigter Schiedsrichter eintragen'];
} elseif ('obleute' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['sr'] = ['Teilnehmer', 'Wählen Sie den Obmann oder Stellvertreter aus, der an dieser Obleutesitzung teilgenommen hat.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['new'] = ['Neuer Teilnehmer', 'Einen neuen Teilnehmer zu dieser Obleutesitzung hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['spiele'] = ['Spiele eintragen', 'Sitzungsbesuche angesetzter Schiedsrichter eintragen'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['besucher'] = ['Besucher eintragen', 'Sitzungsbesuche anwesender/entschuldigter Schiedsrichter eintragen'];
} elseif ('training' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['sr'] = ['Teilnehmer', 'Wählen Sie den Schiedsrichter aus, der an diesem Training teilgenommen hat.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['new'] = ['Neuer Teilnehmer', 'Einen neuen Teilnehmer zu diesem Training hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['spiele'] = ['Spiele eintragen', 'Trainingsteilnahme angesetzter Schiedsrichter eintragen'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['besucher'] = ['Besucher eintragen', 'Trainingsteilnahme anwesender/entschuldigter Schiedsrichter eintragen'];
} elseif ('regelarbeit' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['sr'] = ['Teilnehmer', 'Wählen Sie den Schiedsrichter aus, der an dieser Regelarbeit teilgenommen hat.'];
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typ'] = ['erreichte Punkte', 'Wählen Sie die erreichten Punkte aus.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['new'] = ['Neuer Teilnehmer', 'Einen neuen Teilnehmer zu dieser Regelarbeit hinzufügen.'];
}