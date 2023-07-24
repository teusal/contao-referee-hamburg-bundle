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
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['text'] = ['Text', 'Geben Sie optional einen Text an, der abhängig vom Template angezeigt wird.'];
$GLOBALS['TL_LANG']['tl_module']['mGroups_template'] = ['Gruppenvorlage', 'Bitte wählen Sie ein Layout. Sie können eigene Gruppenlayouts im Ordner <em>templates</em> speichern. Gruppenvorlagen müssen mit <em>bsaGroups_</em> beginnen und die Dateiendung <em>.tpl</em> haben.'];
$GLOBALS['TL_LANG']['tl_module']['mGroups'] = ['BSA-Gruppe', 'Wählen Sie die Gruppen aus, die angezeigt werden sollen.'];
$GLOBALS['TL_LANG']['tl_module']['zeige_daten'] = ['Freigaben für Mitglieder', 'Wählen Sie die Mitgliedergruppen aus, für die <strong>ALLE</strong> Daten von Schiedsrichtern angezeigt werden, auch die, die von der jeweiligen Person nicht freigegeben wurden.'];
$GLOBALS['TL_LANG']['tl_module']['zeige_anzahl_sr'] = ['Anzahl der Schiedsrichter anzeigen', 'Wählen Sie die Einstellung mit der die Anzahl der Schiedsrichter eines Vereins angezeigt werden sollen.'];
$GLOBALS['TL_LANG']['tl_module']['jumpTo_sportplatz'] = ['Weiterleitung für Sportplätze', 'Wählen Sie die Seite aus, auf die weitergeleitet werden soll.'];

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_module']['zeige_anzahl_sr_options'] = ['ever' => 'Immer anzeigen', 'user' => 'Nur eingeloggten Usern anzeigen', 'never' => 'Nie anzeigen'];
