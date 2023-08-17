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
 * Legends
 */
$GLOBALS['TL_LANG']['tl_bsa_referee']['personal_legend'] = 'Personendaten';
$GLOBALS['TL_LANG']['tl_bsa_referee']['club_legend'] = 'Vereinszugehörigkeit';
$GLOBALS['TL_LANG']['tl_bsa_referee']['referee_legend'] = 'Schiedsrichterdaten';
$GLOBALS['TL_LANG']['tl_bsa_referee']['address_legend'] = 'Adressdaten';
$GLOBALS['TL_LANG']['tl_bsa_referee']['contact_legend'] = 'Kontaktdaten';
$GLOBALS['TL_LANG']['tl_bsa_referee']['image_legend'] = 'Bilder';
$GLOBALS['TL_LANG']['tl_bsa_referee']['expert_legend'] = 'Experteneinstellungen';
$GLOBALS['TL_LANG']['tl_bsa_referee']['publishing_legend'] = 'Veröffentlichung';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_bsa_referee']['gender'] = ['Geschlecht', 'Bitte wählen Sie das Geschlecht aus.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['firstname'] = ['Vorname', 'Bitte den Vornamen eingeben.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['lastname'] = ['Nachname', 'Bitte den Nachnamen eingeben.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['nameReverse'] = ['Name', ''];
$GLOBALS['TL_LANG']['tl_bsa_referee']['cardNumber'] = ['SR-Ausweisnummer', 'Bitte geben Sie die Nummer des Schiedsrichterausweises gemäß DFBnet ein (12stellig).'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['clubId'] = ['Verein', 'Der Verein, auf dessen Meldebogen der Schiedsrichter notiert wurde.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['dateOfBirth'] = ['Geburtsdatum', 'Geburtsdatum'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['street'] = ['Straße', 'Straße'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['postal'] = ['Postleitzahl', 'Postleitzahl'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['city'] = ['Ort', 'Wohnort'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['phone1'] = ['Telefonnummer privat', 'Eine private Telefonnummer', 'privat'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['phone2'] = ['Telefonnummer dienstlich', 'Eine dienstliche Telefonnummer', 'diestl.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['mobile'] = ['Handynummer', 'Eine Handynummer', 'Handy'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['fax'] = ['Faxnummer', 'Eine Faxnummer', 'Fax'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['email'] = ['E-Mail Adresse', 'Gültige E-Mail-Adresse des Schiedsrichters'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['emailContactForm'] = ['E-Mail Adresse Kontaktformular', 'E-Mailadresse die im Kontaktformular genutzt wird. Wenn keine Mailadresse eingetragen wurde, so wird die normale E-Mailadress genutzt, die aus dem DFBnet vorgegeben wurde.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['dateOfRefereeExamination'] = ['Schiedsrichter seit', 'Prüfungsdatum des Schiedsrichters'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['state'] = ['SR-Status', 'Der Status des Schiedsrichter gemäß DFBnet (z.B. aktiv).'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['image'] = ['Bild des Schiedsrichters', 'Wählen Sie gegebenenfalls ein Bild des Schiedsrichters aus. Die Bilder der Schiedsrichter müssen im Ordner <strong>"Schiedsrichter"</strong> abgelegt und vom Format <strong>".jpg"</strong>, <strong>".gif"</strong> oder <strong>".png"</strong> sein.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['imagePrint'] = ['Bild des Schiedsrichters als Druckversion', 'Wählen Sie gegebenenfalls ein Bild des Schiedsrichters aus, dass als Druckversion zum Download angeboten wird. Die Bilder der Schiedsrichter müssen im Ordner <strong>"Schiedsrichter"</strong> abgelegt und vom Format <strong>".jpg"</strong>, <strong>".gif"</strong> oder <strong>".png"</strong> sein.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['imageExempted'] = ['Bild des Schiedsrichters als Freisteller', 'Wählen Sie gegebenenfalls ein Bild des Schiedsrichters aus, dass als Freisteller in der Gruppenanzeige dargestellt werden kann. Die Bilder der Schiedsrichter müssen im Ordner <strong>"Schiedsrichter"</strong> abgelegt und vom Format <strong>".jpg"</strong>, <strong>".gif"</strong> oder <strong>".png"</strong> sein.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['searchable'] = ['Suchbar?', 'Wenn sie diese Option deaktivieren, wird der Schiedsrichter nicht bei Google oder anderen Internetsuchen zu finden sein.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['deleted'] = ['Gelöscht?', 'Wenn der Schiedsrichter eigentlich schon gar keiner mehr ist, aber noch im DFBnet existiert, dann aktivieren Sie diese Option und der Schiedsrichter wird nicht mehr angezeigt.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['dateOfRefereeExaminationAsDate'] = ['Schiedsrichter seit (YYYY-MM-DD)', 'Prüfungsdatum des Schiedsrichters in der Datenbankform. Dieses Feld wird beim Speichern aus dem Prüfungsdatum des Schiedsrichters generiert, manuelle Änderungen werden dabei überschrieben.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['dateOfBirthAsDate'] = ['Geburtsdatum (YYYY-MM-DD)', 'Geburtsdatum in der Datenbankform. Dieses Feld wird beim Speichern aus dem Geburtsdatum generiert, manuelle Änderungen werden dabei überschrieben.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['isNew'] = ['Neuer Datensatz?', 'Wird für die Verarbeitung während des Imports benötigt.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['importKey'] = ['Import-Key', 'Wird für die Verarbeitung während des Imports benötigt.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['addressbookVcards'] = ['addressbookVcards', 'Daten über angelegte vcards.'];

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_bsa_referee']['new'] = ['Neuer Schiedsrichter', 'Einen neuen Schiedsrichter hinzufügen.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['edit'] = ['Bearbeiten', 'Diesen Schiedsrichter bearbeiten.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['cut'] = ['Verschieben', 'Diesen Schiedsrichter in einen anderen Verein verschieben.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['copy'] = ['Kopieren', 'Diesen Schiedsrichter kopieren.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['delete'] = ['Löschen', 'Diesen Schiedsrichter löschen.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['show'] = ['Anzeigen', 'Die Details dieses Schiedsrichter anzeigen.'];

/*
 * Import
 */
$GLOBALS['TL_LANG']['tl_bsa_referee']['source'] = ['DFBnet-Schiedsrichterliste', 'Wählen Sie die Datei aus, die importiert werden soll.'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['dfbnet_import'] = ['DFBnet-Import', 'Datenimport für Schiedsrichter'];
$GLOBALS['TL_LANG']['tl_bsa_referee']['import'] = ['DFBnet-Import', 'DFBnet-Report importieren und Schiedsrichter anlegen bzw. aktualisieren.'];
