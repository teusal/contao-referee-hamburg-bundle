<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_bsa_match'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'closed' => true,
        'notEditable' => true,
        'notDeletable' => true,
        'notCopyable' => true,
        'notCreatable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'saison' => 'index',
                'datum' => 'index',
                'spielkennung' => 'index',
                'sr_id' => 'index',
                'sra1_id' => 'index',
                'sra2_id' => 'index',
                '4off_id' => 'index',
                'pate_id' => 'index',
                'sr_verein_id' => 'index',
                'sra1_verein_id' => 'index',
                'sra2_verein_id' => 'index',
                '4off_verein_id' => 'index',
                'pate_verein_id' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['datum DESC'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['datum', 'spielkennung', 'heimmannschaft', 'gastmannschaft', 'spielstaettenname'],
            'format' => '<span style="color:#b3b3b3;padding-right:10px">[%s]</span><span style="color:#b3b3b3;padding-right:10px">[%s]</span>%s - %s<span style="color:#b3b3b3;padding-left:10px">[%s]</span>',
        ],
        'operations' => [
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'exclude' => true,
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'saison' => [
            'filter' => true,
            'foreignKey' => 'tl_bsa_season.name',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'spielkennung' => [
            'search' => true,
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'datum' => [
            'filter' => true,
            'sorting' => true,
            'flag' => 6,
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'heim_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'heimmannschaft' => [
            'search' => true,
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'gast_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'gastmannschaft' => [
            'search' => true,
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'mannschaftsart' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'spielklasse' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'spielgebiet' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'staffelname' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sportplatz_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_sports_facility.name',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'spielstaettenname' => [
            'search' => true,
            'sorting' => true,
            'flag' => 11,
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sr_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sr_name' => [
            'search' => true,
            'sorting' => true,
            'flag' => 11,
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sr_vorname' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sr_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sr_verein_name' => [
            'search' => true,
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sr_verein_nameShort' => [
            'exclude' => true,
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'sra1_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sra1_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sra1_vorname' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sra1_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sra1_verein_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sra1_verein_nameShort' => [
            'exclude' => true,
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'sra2_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sra2_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sra2_vorname' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sra2_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sra2_verein_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'sra2_verein_nameShort' => [
            'exclude' => true,
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        '4off_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        '4off_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        '4off_vorname' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        '4off_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        '4off_verein_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        '4off_verein_nameShort' => [
            'exclude' => true,
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'pate_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pate_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'pate_vorname' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'pate_verein_id' => [
            'exclude' => true,
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'pate_verein_name' => [
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'pate_verein_nameShort' => [
            'exclude' => true,
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'halle' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'beobachtung' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'abgesetzt' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'sql' => "char(1) NOT NULL default ''",
        ],
        'anzeigen_ab' => [
            'exclude' => true,
            'sql' => "int(11) NOT NULL default '0'",
        ],
        'import_key' => [
            'exclude' => true,
            'eval' => ['doNotShow' => true],
            'sql' => 'varchar(6) NULL',
        ],
    ],
];
