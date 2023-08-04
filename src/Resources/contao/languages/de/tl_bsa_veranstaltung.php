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
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['saison'] = ['Saison', 'Wählen Sie die Saison aus, in der diese Veranstalltung stattfindet.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['datum'] = ['Datum', 'Das Datum dieser Veranstaltung.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typ'] = ['Typ', 'Wählen Sie den Typ dieser Veranstaltung aus.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['name'] = ['Name/Beschreibung', 'Geben sie einen Namen, Titel oder eine Beschreibung der Veranstaltung ein.'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen'][''] = '-';
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen']['p'] = 'Pflichtsitzung/Lehrabend';
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen']['n'] = 'normale Sitzung';
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen']['cs'] = 'Sitzung/Lehrabend';
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen']['cb'] = 'Spielbeobachtung';

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['new'] = ['Neue Veranstaltung', 'Eine neue Veranstaltung hinzufügen.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['edit'] = ['Teilnehmer bearbeiten', 'Die Teilnehmer dieser Veranstaltung bearbeiten.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['editheader'] = ['Veranstaltung bearbeiten', 'Diese Veranstaltung und die Teilnehmer bearbeiten.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['delete'] = ['Löschen', 'Diese Veranstaltung löschen.'];
$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['export'] = ['XLS-Export', 'Veranstaltungen in eine Excel-Datei exportieren.'];

/*
 * Changes by Input::get('do')
 */
if ('sitzung' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['datum'] = ['Datum', 'Das Datum dieser Sitzung.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typ'] = ['Typ', 'Wählen Sie den Typ dieser Sitzung aus.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['new'] = ['Neue Sitzung', 'Eine neue Sitzung hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['edit'] = ['Bearbeiten', 'Diese Sitzung und die Teilnehmer bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['edit'] = ['Teilnehmer bearbeiten', 'Die Teilnehmer dieser Sitzung bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['editheader'] = ['Sitzung bearbeiten', 'Diese Sitzung bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['delete'] = ['Löschen', 'Diese Sitzung löschen.'];
} elseif ('obleute' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['datum'] = ['Datum', 'Das Datum dieser Obleutesitzung.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typ'] = ['Typ', 'Wählen Sie den Typ dieser Obleutesitzung aus.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['new'] = ['Neues Obleutesitzung', 'Eine neue Obleutesitzung hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['edit'] = ['Teilnehmer bearbeiten', 'Die Teilnehmer dieser Obleutesitzung bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['editheader'] = ['Obleutesitzung bearbeiten', 'Diese Obleutesitzung bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['delete'] = ['Löschen', 'Diese Obleutesitzung löschen.'];
} elseif ('training' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['datum'] = ['Datum', 'Das Datum dieses Trainings.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typ'] = ['Typ', 'Wählen Sie den Typ dieses Trainings aus.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['new'] = ['Neues Training', 'Eine neues Training hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['edit'] = ['Teilnehmer bearbeiten', 'Die Teilnehmer dieses Trainings bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['editheader'] = ['Training bearbeiten', 'Dieses Training bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['delete'] = ['Löschen', 'Dieses Training löschen.'];
} elseif ('regelarbeit' === Input::get('do')) {
    // Fields
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['datum'] = ['Datum', 'Das Datum dieser Regelarbeit.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typ'] = ['max. Punkte', 'Geben Sie die max. Punktzahl der Regelarbeit ein.'];
    // Buttons
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['new'] = ['Neue Regelarbeit', 'Eine neue Regelarbeit hinzufügen.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['edit'] = ['Teilnehmer bearbeiten', 'Die Teilnehmer dieser Regelarbeit bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['editheader'] = ['Regelarbeit bearbeiten', 'Diese Regelarbeit bearbeiten.'];
    $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['delete'] = ['Löschen', 'Diese Regelarbeit löschen.'];
}
