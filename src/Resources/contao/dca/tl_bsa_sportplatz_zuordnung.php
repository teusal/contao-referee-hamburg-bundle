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
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSportplatzModel;

$GLOBALS['TL_DCA']['tl_bsa_sportplatz_zuordnung'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'verein' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['verein'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['verein', 'sportplaetze'],
            'showColumns' => true,
            'label_callback' => [tl_bsa_sportplatz_zuordnung::class, 'getLabel'],
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
        '__selector__' => [],
        'default' => 'verein;sportplaetze',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'verein' => [
            'inputType' => 'select',
            'filter' => true,
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Verein wählen', 'mandatory' => true, 'unique' => true, 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_verein.name_kurz',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sportplaetze' => [
            'inputType' => 'checkboxWizard',
            'filter' => true,
            'eval' => ['multiple' => true],
            'foreignKey' => 'tl_bsa_sportplatz.name',
            'sql' => 'blob NULL',
        ],
    ],
];

/**
 * Class tl_bsa_sportplatz_zuordnung.
 */
class tl_bsa_sportplatz_zuordnung extends Backend
{
    /**
     * sets the list of assigned sports facilities in column two.
     *
     * @param array         $row     Record data
     * @param string        $label   Current label
     * @param DataContainer $dc      Data Container object
     * @param array         $columns with existing labels
     *
     * @return array|string
     */
    public function getLabel($row, $label, DataContainer $dc, $columns)
    {
        if (empty($columns[1])) {
            return $columns;
        }

        $objVerein = BsaSportplatzModel::findMultipleByIds(explode(', ', $columns[1]), ['order' => 'name']);

        if (isset($objVerein)) {
            $columns[1] = implode(', ', $objVerein->fetchEach('name'));
        } else {
            $columns[1] = '-';
        }

        return $columns;
    }
}
