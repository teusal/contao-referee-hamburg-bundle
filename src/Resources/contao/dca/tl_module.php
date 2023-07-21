<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

// Add a palette to tl_module
$GLOBALS['TL_DCA']['tl_module']['palettes']['bsa_freigaben'] = '{title_legend},name,headline,type;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['bsa_vereine'] = '{title_legend},name,headline,type;zeige_daten,zeige_anzahl_sr;jumpTo_sportplatz;align,space,cssID';
$GLOBALS['TL_DCA']['tl_module']['palettes']['bsa_schiedsrichter'] = '{title_legend},name,headline,type;list_where;perPage;guests,protected;zeige_daten;align,space,cssID';

/*
 * Add fields to tl_module
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['zeige_daten'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_member_group.name',
    'eval' => ['multiple' => true],
    'sql' => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['zeige_anzahl_sr'] = [
    'exclude' => true,
    'inputType' => 'select',
    'options' => &$GLOBALS['TL_LANG']['tl_module']['zeige_anzahl_sr_options'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['zeige_anzahl_sr_options'],
    'eval' => ['multiple' => false],
    'sql' => 'varchar(5) NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['jumpTo_sportplatz'] = [
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => ['fieldType' => 'radio'],
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => ['type' => 'hasOne', 'load' => 'eager'],
];
