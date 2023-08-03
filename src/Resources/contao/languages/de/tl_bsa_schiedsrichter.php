<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['geschlecht'] = ['Geschlecht', 'Bitte wählen Sie das Geschlecht aus.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['vorname'] = ['Vorname', 'Bitte den Vornamen eingeben.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['nachname'] = ['Nachname', 'Bitte den Nachnamen eingeben.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['name_rev'] = ['Name', ''];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['ausweisnummer'] = ['SR-Ausweisnummer', 'Bitte geben Sie die Nummer des Schiedsrichterausweises gemäß DFBnet ein (12stellig).'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['verein'] = ['Verein', 'Der Verein, auf dessen Meldebogen der Schiedsrichter notiert wurde.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['geburtsdatum'] = ['Geburtsdatum', 'Geburtsdatum'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['geburtsdatum_date'] = ['Geburtsdatum (YYYY-MM-DD)', 'Geburtsdatum in der Datenbankform. Dieses Feld wird beim Speichern aus dem Geburtsdatum generiert, manuelle Änderungen werden dabei überschrieben.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['strasse'] = ['Straße', 'Straße'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['plz'] = ['Postleitzahl', 'Postleitzahl'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['ort'] = ['Ort', 'Wohnort'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['telefon1'] = ['Telefonnummer privat', 'Eine private Telefonnummer', 'privat'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['telefon2'] = ['Telefonnummer dienstlich', 'Eine dienstliche Telefonnummer', 'diestl.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['telefon_mobil'] = ['Handynummer', 'Eine Handynummer', 'Handy'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['fax'] = ['Faxnummer', 'Ein Faxnummer', 'Fax'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['email'] = ['E-Mail Adresse', 'Gültige E-Mail-Adresse des Schiedsrichters'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['email_kontaktformular'] = ['E-Mail Adresse Kontaktformular', 'E-Mailadresse die im Kontaktformular genutzt wird. Wenn keine Mailadresse eingetragen wurde, so wird die normale E-Mailadress genutzt, die aus dem DFBnet vorgegeben wurde.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['sr_seit'] = ['Schiedsrichter seit', 'Prüfungsdatum des Schiedsrichters'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['sr_seit_date'] = ['Schiedsrichter seit (YYYY-MM-DD)', 'Prüfungsdatum des Schiedsrichters in der Datenbankform. Dieses Feld wird beim Speichern aus dem Prüfungsdatum des Schiedsrichters generiert, manuelle Änderungen werden dabei überschrieben.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['status'] = ['SR-Status', 'Der Status des Schiedsrichter gemäß DFBnet (z.B. aktiv).'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['image'] = ['Bild des Schiedsrichters', 'Wählen Sie gegebenenfalls ein Bild des Schiedsrichters aus. Die Bilder der Schiedsrichter müssen im Ordner <strong>"Schiedsrichter"</strong> abgelegt und vom Format <strong>".jpg"</strong>, <strong>".gif"</strong> oder <strong>".png"</strong> sein.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['image_print'] = ['Bild des Schiedsrichters als Druckversion', 'Wählen Sie gegebenenfalls ein Bild des Schiedsrichters aus, dass als Druckversion zum Download angeboten wird. Die Bilder der Schiedsrichter müssen im Ordner <strong>"Schiedsrichter"</strong> abgelegt und vom Format <strong>".jpg"</strong>, <strong>".gif"</strong> oder <strong>".png"</strong> sein.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['image_exempted'] = ['Bild des Schiedsrichters als Freisteller', 'Wählen Sie gegebenenfalls ein Bild des Schiedsrichters aus, dass als Freisteller in der Gruppenanzeige dargestellt werden kann. Die Bilder der Schiedsrichter müssen im Ordner <strong>"Schiedsrichter"</strong> abgelegt und vom Format <strong>".jpg"</strong>, <strong>".gif"</strong> oder <strong>".png"</strong> sein.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['searchable'] = ['Suchbar?', 'Wenn sie diese Option deaktivieren, wird der Schiedsrichter nicht bei Google oder anderen Internetsuchen zu finden sein.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['deleted'] = ['Gelöscht?', 'Wenn der Schiedsrichter eigentlich schon gar keiner mehr ist, aber noch im DFBnet existiert, dann aktivieren Sie diese Option und der Schiedsrichter wird nicht mehr angezeigt.'];

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['new'] = ['Neuer Schiedsrichter', 'Einen neuen Schiedsrichter hinzufügen.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['edit'] = ['Bearbeiten', 'Diesen Schiedsrichter bearbeiten.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['cut'] = ['Verschieben', 'Diesen Schiedsrichter in einen anderen Verein verschieben.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['copy'] = ['Kopieren', 'Diesen Schiedsrichter kopieren.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['delete'] = ['Löschen', 'Diesen Schiedsrichter löschen.'];
$GLOBALS['TL_LANG']['tl_bsa_schiedsrichter']['show'] = ['Anzeigen', 'Die Details dieses Schiedsrichter anzeigen.'];
