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

$GLOBALS['TL_DCA']['tl_bsa_sports_facility_number'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_bsa_sports_facility',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'dfbnetNumber' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['dfbnetNumber'],
            'headerFields' => ['name', 'address'],
            'panelLayout' => 'limit',
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['dfbnetNumber'],
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();"',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => 'dfbnetNumber',
    ],

    // Subpalettes
    'subpalettes' => [
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'foreignKey' => 'tl_bsa_sports_facility.name',
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dfbnetNumber' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'unique' => true, 'rgxp' => 'digit', 'maxlength' => 10, 'minlength' => 10, 'tl_class' => 'w50'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
    ],
];
