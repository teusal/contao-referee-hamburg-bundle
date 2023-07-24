<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_LANG']['tl_member_group']['legend_beobachtung'] = 'Beobachtungen';
$GLOBALS['TL_LANG']['tl_member_group']['legend_email'] = 'Kontaktformular';
$GLOBALS['TL_LANG']['tl_member_group']['legend_automatik'] = 'Verwaltung der Gruppenmitglieder';
$GLOBALS['TL_LANG']['tl_member_group']['legend_image'] = 'Anzeige- und Druckbilder';
$GLOBALS['TL_LANG']['tl_member_group']['addressbook_legend'] = 'Addressbuch';

/*
 * Fields
 */
$GLOBALS['TL_LANG']['tl_member_group']['beobachtung_beobachterauswahl'] = ['Gruppe als Beobachterauswahl aktivieren', 'Schaltet die Mitglieder der Gruppe als Beobachter in der Auswahllister der auszuführenden Beobachtungen frei.'];
$GLOBALS['TL_LANG']['tl_member_group']['beobachtung_aktivieren'] = ['Beobachtungen aktivieren', 'Schaltet die Funktionen der Beobachtungen für diese Gruppe ein.'];
$GLOBALS['TL_LANG']['tl_member_group']['beobachtung_ordner_name'] = ['Ordner für Beobachtungen im Dateisystem', 'Der Ordnername für Beobachtungen.'];
$GLOBALS['TL_LANG']['tl_member_group']['beobachtung_gruppen_name'] = ['Gruppierung für Beobachtungen', 'Die Gruppierung für die Auswahl bei Beobachtungen.'];
$GLOBALS['TL_LANG']['tl_member_group']['beobachtung_gruppen_name_kurz'] = ['Gruppenname kurz für XLS-Auswertung', 'Der Name ersetzt den ausgeschriebenen Namen in der Excel-Auswertung. Wenn Sie hier nichts eintragen, wird der Gruppenname benutzt.'];
$GLOBALS['TL_LANG']['tl_member_group']['email_aktivieren'] = ['Kontaktformular aktivieren', 'Das Kontaktformular wird für diese Gruppe verlinkt, wenn diese Option aktiviert ist. Sie müssen anschließend eine E-Mailadresse angeben.'];
$GLOBALS['TL_LANG']['tl_member_group']['email'] = ['E-Mail Adresse', 'Die angegebene E-Mail Adresse sollte ein Verteiler sein. <strong>Der Verteiler ist unabhängig von den Mitgliedern dieser Gruppe</strong>!'];
$GLOBALS['TL_LANG']['tl_member_group']['name'] = ['Gruppenname', ''];
$GLOBALS['TL_LANG']['tl_member_group']['automatik'] = ['Automatik für Gruppenmitgliederverwaltung', 'Wählen Sie aus, wie die Mitglieder dieser Gruppe verwaltet werden sollen.'];
$GLOBALS['TL_LANG']['tl_member_group']['image_anzeigen'] = ['Bild auf den Seiten anzeigen', 'Auf der Website wird für diese Gruppe ein Bild angezeigt.'];
$GLOBALS['TL_LANG']['tl_member_group']['image'] = ['Bild für Webanzeige', 'Dieses Bild kann für die Anzeige auf der Website genutzt werden.'];
$GLOBALS['TL_LANG']['tl_member_group']['image_print_verlinken'] = ['Bild zum Download (Druckversion) verlinken', 'Das Anzeigebild wird, wenn vorhanden, mit einem Link zum Download versehen, wenn hier eine Druckversion eingestellt wird.'];
$GLOBALS['TL_LANG']['tl_member_group']['image_print'] = ['Bild zum Download (Druckversion)', 'Das Anzeigebild wird, wenn vorhanden, mit einem Link zum Download versehen, wenn hier eine Druckversion eingestellt wird.'];
$GLOBALS['TL_LANG']['tl_member_group']['add_logins'] = ['Logins anlegen', 'Sollen für die Schiedsrichter dieser Gruppe Logins angelegt werden?'];
$GLOBALS['TL_LANG']['tl_member_group']['groups'] = ['Mitgliedergruppen', 'Wählen Sie die Mitgliedergruppen aus, bei denen der Login zugehörig sein soll.'];
$GLOBALS['TL_LANG']['tl_member_group']['sync_addressbook'] = ['Schiedsrichter in Addressbuch sychronisieren', 'Aktivieren Sie diese Option, damit die Schiedsrichter und Personen aus dieser Gruppe in ein Addressbuch synchronisiert werden.'];
$GLOBALS['TL_LANG']['tl_member_group']['addressbook_token_id'] = ['Addressbuch Token ID', 'Geben Sie die Token ID des Addressbuchs an.'];
$GLOBALS['TL_LANG']['tl_member_group']['legend_veranstaltung'] = 'Veranstaltung Export';
$GLOBALS['TL_LANG']['tl_member_group']['veranstaltung_include_as_filter'] = ['Gruppe als Filter in XLS-Export verwenden?', 'Geben Sie an, ob diese Gruppe als Filter im Reporting von Veranstaltungen verwendet werden soll.'];

/*
 * Reference
 */
$GLOBALS['TL_LANG']['tl_member_group']['options']['vollautomatik'] = 'VOLLAUTOMATIK';
$GLOBALS['TL_LANG']['tl_member_group']['options']['halbautomatik'] = 'HALBAUTOMATIK';
$GLOBALS['TL_LANG']['tl_member_group']['options']['alle'] = ['Alle Schiedsrichter*innen, Obleute & Stellvertreter*innen', 'Es werden automatisch alle vereinslosen Personen, die als Obleute arbeiten, sowie alle Schiedsrichter in diese Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['alle_sr'] = ['Alle Schiedsrichter*innen', 'Es werden automatisch alle Schiedsrichter in diese Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['obleute'] = ['Obleute & Stellvertreter*innen', 'Es werden automatisch alle Obleute sowie deren Stellvertreter in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['U18'] = ['Aktive Schiedsrichter*innen unter 18 Jahren', 'Es werden automatisch alle minderjährigen Schiedsrichter in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['Ü40'] = ['Aktive Schiedsrichter*innen über 40 Jahren', 'Es werden automatisch alle Ü40 Schiedsrichter in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['m'] = ['Aktive, männliche Schiedsrichter', 'Es werden automatisch alle männlichen Schiedsrichter in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['w'] = ['Aktive, weibliche Schiedsrichterinnen', 'Es werden automatisch alle weiblichen Schiedsrichterinnen in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['aktive'] = ['Aktive Schiedsrichter*innen', 'Es werden automatisch alle aktiven Schiedsrichter in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['passive'] = ['Passive Schiedsrichter*innen', 'Es werden automatisch alle passiven Schiedsrichter in dieser Gruppe erfasst.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['10_jahre'] = ['10 Jahre Schiedsrichter*in', 'Es werden alle Schiedsrichter*innen erfasst, die im aktuellen Kalenderjahr 10 jähriges Jubiläum haben.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['25_jahre'] = ['25 Jahre Schiedsrichter*in', 'Es werden alle Schiedsrichter*innen erfasst, die im aktuellen Kalenderjahr 25 jähriges Jubiläum haben.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['40_jahre'] = ['40 Jahre Schiedsrichter*in', 'Es werden alle Schiedsrichter*innen erfasst, die im aktuellen Kalenderjahr 40 jähriges Jubiläum haben.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['50_jahre'] = ['50 Jahre Schiedsrichter*in', 'Es werden alle Schiedsrichter*innen erfasst, die im aktuellen Kalenderjahr 50 jähriges Jubiläum haben.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['60_jahre'] = ['60 Jahre Schiedsrichter*in', 'Es werden alle Schiedsrichter*innen erfasst, die im aktuellen Kalenderjahr 60 jähriges Jubiläum haben.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['70_jahre'] = ['70 Jahre Schiedsrichter*in', 'Es werden alle Schiedsrichter*innen erfasst, die im aktuellen Kalenderjahr 70 jähriges Jubiläum haben.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['ohne_sitzung'] = ['Fehlende Lehrveranstaltung', 'Es werden alle Schiedsrichter*innen erfasst, die in der aktuellen Saison nicht Teilnehmer einer Lehrveranstaltung waren.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['ohne_regelarbeit'] = ['Fehlende Regelarbeit', 'Es werden alle Schiedsrichter*innen erfasst, die in der aktuellen Saison nicht Teilnehmer einer Regelarbeit waren.'];
$GLOBALS['TL_LANG']['tl_member_group']['options']['ohne_sitzung_regelarbeit'] = ['Fehlende Lehrveranstaltung & Regelarbeit', 'Es werden alle Schiedsrichter*innen erfasst, die in der aktuellen Saison weder Teilnehmer einer Lehrveranstaltung noch einer Regelarbeit waren.'];

/*
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_member_group']['edit_gruppenmitglieder'] = ['Gruppenmitglieder bearbeiten', 'Die Gruppenmitglieder dieser Gruppe bearbeiten'];
$GLOBALS['TL_LANG']['tl_member_group']['edit_newsletterzuordnung'] = ['Newsletterzuordnung bearbeiten', 'Die Newsletterzuordnung dieser Gruppe bearbeiten'];
