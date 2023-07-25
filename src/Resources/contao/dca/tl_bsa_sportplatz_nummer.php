<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Backend;
use Contao\DataContainer;
use Contao\DC_Table;

$GLOBALS['TL_DCA']['tl_bsa_sportplatz_nummer'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_bsa_sportplatz',
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
                'dfbnet_nummer' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['dfbnet_nummer'],
            'headerFields' => ['name', 'anschrift'],
            'panelLayout' => 'filter;search,limit',
            'child_record_callback' => [tl_bsa_sportplatz_nummer::class, 'listChildRecord'],
            'disableGrouping' => true,
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
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
        'default' => 'dfbnet_nummer',
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
            'foreignKey' => 'tl_bsa_sportplatz.name',
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
            'sql' => 'int(10) unsigned NULL',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dfbnet_nummer' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'unique' => true, 'rgxp' => 'digit', 'maxlength' => 10, 'minlength' => 10],
            'sql' => 'varchar(10) NULL',
        ],
    ],
];

/**
 * Class tl_bsa_sportplatz_nummer.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_sportplatz_nummer extends Backend
{
    /**
     * Defines how child elements are rendered in "parent view".
     *
     * @param array $arrRow Record Data
     */
    public function listChildRecord($arrRow): string
    {
        return '<div>'.$arrRow['dfbnet_nummer'].'</div>';
    }
}
