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
use Teusal\ContaoRefereeHamburgBundle\Model\EventParticipiantModel;
use Teusal\ContaoRefereeHamburgBundle\Model\SeasonModel;

$typs = [];

$typs['']['palettes'] = 'seasonId,date';
$typs['sitzung']['palettes'] = 'seasonId,date,type';
$typs['obleute']['palettes'] = 'seasonId,date';
$typs['training']['palettes'] = 'seasonId,date';
$typs['regelarbeit']['palettes'] = 'seasonId,date,name,type';
$typs['coaching']['palettes'] = 'seasonId,date,type';
$typs['lehrgang']['palettes'] = 'seasonId,date,name';
$typs['helsen']['palettes'] = 'seasonId,date';
$typs['sonstige']['palettes'] = 'seasonId,date,name';

$typs['']['eval'] = ['tl_class' => 'w50 clr'];
$typs['sitzung']['eval'] = ['tl_class' => 'w50 clr'];
$typs['obleute']['eval'] = ['tl_class' => 'w50 clr'];
$typs['training']['eval'] = ['tl_class' => 'w50 clr'];
$typs['regelarbeit']['eval'] = ['rgxp' => 'digit', 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'maximale Punkte wählen', 'tl_class' => 'w50 clr'];
$typs['coaching']['eval'] = ['tl_class' => 'w50 clr'];
$typs['lehrgang']['eval'] = ['tl_class' => 'w50 clr'];
$typs['sonstige']['eval'] = ['tl_class' => 'w50 clr'];

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
 * Table tl_bsa_event
 */
$GLOBALS['TL_DCA']['tl_bsa_event'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_bsa_event_participiant'],
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
            'fields' => ['date'],
            'panelLayout' => 'filter;limit',
            'filter' => [['eventGroup=?', Input::get('do')]],
        ],
        'label' => [
            'fields' => ['date'],
            'format' => '<div style="float:left;color:#b3b3b3;padding-right:3px">[%s]</div>',
            'label_callback' => [tl_bsa_event::class, 'listVeranstaltung'],
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'href' => 'table=tl_bsa_event_participiant',
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
        'eventGroup' => [
            'default' => Input::get('do'),
            'sql' => "varchar(15) NOT NULL default ''",
        ],
        'date' => [
            'inputType' => 'text',
            'default' => time(),
            'flag' => DataContainer::SORT_MONTH_DESC,
            'eval' => ['maxlength' => 10, 'rgxp' => 'date', 'mandatory' => true, 'datepicker' => true, 'tl_class' => 'w50 clr'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'seasonId' => [
            'filter' => true,
            'inputType' => 'select',
            'eval' => ['alwaysSave' => true, 'mandatory' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Saison wählen', 'tl_class' => 'w50 clr'],
            'foreignKey' => 'tl_bsa_season.name',
            'default' => SeasonModel::getCurrentSeasonId(),
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'type' => [
            'filter' => true,
            'inputType' => 'select',
            'options' => $typs[Input::get('do')]['values'],
            'reference' => &$GLOBALS['TL_LANG']['tl_bsa_event']['typen'],
            'eval' => $typs[Input::get('do')]['eval'],
            'sql' => "varchar(2) NOT NULL default ''",
        ],
        'name' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 50, 'tl_class' => 'w50 clr'],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_event.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_event extends Backend
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
        $anzTeilnehmer = EventParticipiantModel::countBy('pid', $row['id']);
        $label .= '<div style="float:left;text-align:right;width:100px;">'.$anzTeilnehmer.' Teilnehmer</div>';

        if ('sitzung' === Input::get('do') || 'coaching' === Input::get('do')) {
            $label .= '<div style="float:left;margin-left:20px;">'.$GLOBALS['TL_LANG']['tl_bsa_event']['typen'][$row['type']].'</div>';
        } elseif ('regelarbeit' === Input::get('do') || 'lehrgang' === Input::get('do') || 'sonstige' === Input::get('do')) {
            $label .= '<div style="float:left;margin-left:20px;">'.$row['name'].'</div>';
        }

        return $label;
    }
}
