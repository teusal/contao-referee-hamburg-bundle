<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DC_File;

$arrReferences = [
    ['colspan', '<h1 style="margin-top: 15px; font-weight: bold;">Mögliche Ersetzungen in E-Mails der Tauschbörse</h1>'],
    ['#USER_VORNAME#', 'Vorname des eingelogten Frontend-Benutzers'],
    ['#USER_NACHNAME#', 'Nachname des eingelogten Frontend-Benutzers'],
    ['#USER_EMAIL#', 'E-Mail-Adresse des eingelogten Frontend-Benutzers'],
    ['#USER_VEREIN#', 'Verein des eingelogten Frontend-Benutzers'],
    ['#DATUM+ZEIT#', 'Datum und Zeit des Spiels'],
    ['#SPIELNUMMER#', 'Die Spielnummer'],
    ['#VEREIN_NEU#', 'Der neue, übernehmende Verein (beim Anlegen eines Spiels leer!)'],
];

/*
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_bsa_swap_meet_settings'] = [
    // Config
    'config' => [
        'dataContainer' => DC_File::class,
        'closed' => true,
    ],

    // Palettes
    'palettes' => [
        '__selector__' => [],
        'default' => '{swap_meet_legend},swap_meet_newsletter,swap_meet_mailer_transport,swap_meet_days_before;{swap_meet_add_legend},swap_meet_add_subject,swap_meet_add_text;{swap_meet_del_legend},swap_meet_del_subject,swap_meet_del_text',
    ],

    // Fields
    'fields' => [
        'swap_meet_newsletter' => [
            'inputType' => 'select',
            'options_callback' => ['Newsletter', 'getNewsletters'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'bitte Newsletter wählen', 'tl_class' => 'w50'],
        ],
        'swap_meet_mailer_transport' => [
            'inputType' => 'select',
            'eval' => ['includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50'],
            'options_callback' => ['contao.mailer.available_transports', 'getSystemTransportOptions'],
        ],
        'swap_meet_days_before' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'digit', 'mandatory' => true, 'tl_class' => 'w50'],
        ],
        'swap_meet_add_subject' => [
            'inputType' => 'text',
            'reference' => $arrReferences,
            'eval' => ['helpwizard' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'long'],
        ],
        'swap_meet_add_text' => [
            'inputType' => 'textarea',
            'reference' => array_merge($arrReferences, [['#SPIELDATEN_TABELLE#', 'Die Daten des Spiels als vollständige Tabelle']]),
            'eval' => ['helpwizard' => true, 'rte' => 'tinyNews', 'mandatory' => true],
        ],
        'swap_meet_del_subject' => [
            'inputType' => 'text',
            'reference' => $arrReferences,
            'eval' => ['helpwizard' => true, 'decodeEntities' => true, 'mandatory' => true, 'tl_class' => 'long'],
        ],
        'swap_meet_del_text' => [
            'inputType' => 'textarea',
            'reference' => array_merge($arrReferences, [['#SPIELDATEN_TABELLE#', 'Die Daten des Spiels als vollständige Tabelle']]),
            'eval' => ['helpwizard' => true, 'rte' => 'tinyNews', 'mandatory' => true],
        ],
    ],
];
