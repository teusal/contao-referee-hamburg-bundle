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
use Contao\BackendUser;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Input;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSeasonModel;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaTeilnehmerModel;

$typs = [];

$typs['']['palettes'] = 'datum,saison';
$typs['sitzung']['palettes'] = 'datum,saison,typ';
$typs['obleute']['palettes'] = 'datum,saison';
$typs['training']['palettes'] = 'datum,saison';
$typs['regelarbeit']['palettes'] = 'datum,saison,name,typ';
$typs['coaching']['palettes'] = 'datum,saison,typ';
$typs['lehrgang']['palettes'] = 'datum,saison,name';
$typs['helsen']['palettes'] = 'datum,saison';
$typs['sonstige']['palettes'] = 'datum,saison,name';

$typs['']['eval'] = [];
$typs['sitzung']['eval'] = [];
$typs['obleute']['eval'] = [];
$typs['training']['eval'] = [];
$typs['regelarbeit']['eval'] = ['rgxp' => 'digit', 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'maximale Punkte wählen'];
$typs['coaching']['eval'] = [];
$typs['lehrgang']['eval'] = [];
$typs['sonstige']['eval'] = [];

$typs['']['values'] = [];
$typs['sitzung']['values'] = ['n', 'p'];
$typs['obleute']['values'] = ['n'];
$typs['training']['values'] = ['n'];
$typs['lehrgang']['values'] = ['n'];
$typs['helsen']['values'] = ['n'];
$typs['sonstige']['values'] = ['n'];

for ($i = 1; $i <= 100; ++$i) {
    $typs['regelarbeit']['values'][] = $i;
}
$typs['coaching']['values'] = ['cs', 'cb'];

/*
 * Table tl_bsa_veranstaltung
 */
$GLOBALS['TL_DCA']['tl_bsa_veranstaltung'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_bsa_teilnehmer'],
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_MONTH_DESC,
            'fields' => ['datum'],
            'panelLayout' => 'filter;limit',
            'filter' => [['veranstaltungsgruppe=?', Input::get('do')]],
        ],
        'label' => [
            'fields' => ['datum'],
            'format' => '<div style="float:left;color:#b3b3b3;padding-right:3px">[%s]</div>',
            'label_callback' => [tl_bsa_veranstaltung::class, 'listVeranstaltung'],
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'href' => 'table=tl_bsa_teilnehmer',
                'icon' => 'edit.gif',
            ],
            'editheader' => [
                'href' => 'act=edit',
                'icon' => 'header.gif',
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
        'default' => $typs[Input::get('do')]['palettes'],
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
        'veranstaltungsgruppe' => [
            'default' => Input::get('do'),
            'sql' => "varchar(15) NOT NULL default ''",
        ],
        'datum' => [
            'inputType' => 'text',
            'default' => time(),
            'flag' => DataContainer::SORT_MONTH_DESC,
            'eval' => ['maxlength' => 10, 'rgxp' => 'date', 'mandatory' => true, 'datepicker' => true],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'saison' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['alwaysSave' => true, 'mandatory' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Saison wählen'],
            'foreignKey' => 'tl_bsa_season.name',
            'default' => BsaSeasonModel::getCurrentSeasonId(),
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'typ' => [
            'filter' => true,
            'inputType' => 'select',
            'options' => $typs[Input::get('do')]['values'],
            'reference' => &$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen'],
            'eval' => $typs[Input::get('do')]['eval'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 50],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_veranstaltung.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_veranstaltung extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Generiert das Label eines Datensatzes.
     *
     * @param array         $row     Record data
     * @param string        $label   Current label
     * @param DataContainer $dc      Data Container object
     * @param array         $columns with existing labels
     *
     * @return array|string
     */
    public function listVeranstaltung($row, $label, DataContainer $dc, $columns)
    {
        $anzTeilnehmer = BsaTeilnehmerModel::countBy('pid', $row['id']);
        $label .= '<div style="float:left;text-align:right;width:100px;">'.$anzTeilnehmer.' Teilnehmer</div>';

        if ('sitzung' === Input::get('do') || 'coaching' === Input::get('do')) {
            $label .= '<div style="float:left;margin-left:20px;">'.$GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen'][$row['typ']].'</div>';
        } elseif ('regelarbeit' === Input::get('do') || 'lehrgang' === Input::get('do') || 'sonstige' === Input::get('do')) {
            $label .= '<div style="float:left;margin-left:20px;">'.$row['name'].'</div>';
        }

        return $label;
    }
}
