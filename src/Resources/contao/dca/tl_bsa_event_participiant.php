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

$globalOperations = [
    'spiele' => [
        'href' => 'key=spiele',
        'class' => 'header_edit_all',
        'attributes' => 'onclick="Backend.getScrollOffset();"',
    ],
    'besucher' => [
        'href' => 'key=besucher&start=true',
        'class' => 'header_edit_all',
        'attributes' => 'onclick="Backend.getScrollOffset();"',
    ],
    'import' => [
        'href' => 'key=import',
        'class' => 'header_css_import',
        'attributes' => 'onclick="Backend.getScrollOffset();"',
    ],
    'all' => [
        'href' => 'act=select',
        'class' => 'header_edit_all',
        'attributes' => 'onclick="Backend.getScrollOffset();"',
    ],
];

$typs = [];

$typs['']['inputType'] = 'select';
$typs['sitzung']['inputType'] = 'select';
$typs['obleute']['inputType'] = 'select';
$typs['training']['inputType'] = 'select';
$typs['regelarbeit']['inputType'] = 'text';
$typs['coaching']['inputType'] = 'select';
$typs['lehrgang']['inputType'] = 'select';
$typs['helsen']['inputType'] = 'select';
$typs['sonstige']['inputType'] = 'select';

$typs['']['values'] = [];
$typs['sitzung']['values'] = ['a', 'e', 's', 'v'];
$typs['obleute']['values'] = ['a', 'e', 's', 'v'];
$typs['training']['values'] = ['a', 'e', 's', 'v'];
$typs['regelarbeit']['values'] = [];
$typs['coaching']['values'] = ['a', 'e', 's', 'v'];
$typs['lehrgang']['values'] = ['a', 'e', 's', 'v'];
$typs['helsen']['values'] = ['best', '9,5r', '9,0r', '8,5r', '8,0r', '7,5r', '7,0r', '6,5r', '6,0r', '5,5r', '5,0r', '4,5r', '4,0r', '3,5r', '3,0r', '2,5r', '2,0r', '1,5r', '1,0r', '0,5r'];
$typs['sonstige']['values'] = ['a', 'e', 's', 'v'];

$typs['']['eval'] = ['tl_class' => 'w50 clr'];
$typs['sitzung']['eval'] = ['tl_class' => 'w50 clr'];
$typs['obleute']['eval'] = ['tl_class' => 'w50 clr'];
$typs['training']['eval'] = ['tl_class' => 'w50 clr'];
$typs['regelarbeit']['eval'] = ['rgxp' => 'digit', 'mandatory' => true, 'tl_class' => 'w50 clr'];
$typs['coaching']['eval'] = ['tl_class' => 'w50 clr'];
$typs['lehrgang']['eval'] = ['tl_class' => 'w50 clr'];
$typs['helsen']['eval'] = ['tl_class' => 'w50 clr'];
$typs['sonstige']['eval'] = ['tl_class' => 'w50 clr'];

$typs['']['global_operations'] = [];
$typs['sitzung']['global_operations'] = $globalOperations;
$typs['obleute']['global_operations'] = $globalOperations;
$typs['training']['global_operations'] = $globalOperations;
$typs['regelarbeit']['global_operations'] = $globalOperations;
unset($typs['regelarbeit']['global_operations']['spiele'], $typs['regelarbeit']['global_operations']['besucher']);

$typs['coaching']['global_operations'] = $globalOperations;
$typs['lehrgang']['global_operations'] = $globalOperations;
unset($typs['helsen']['global_operations']['spiele'], $typs['helsen']['global_operations']['besucher'], $typs['helsen']['global_operations']['import']);

$typs['sonstige']['global_operations'] = $globalOperations;
unset($typs['sonstige']['global_operations']['import']);

/*
 * Table tl_bsa_event_participiant
 */
$GLOBALS['TL_DCA']['tl_bsa_event_participiant'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_bsa_event',
        'enableVersioning' => false,
        'onsubmit_callback' => [
            [
                tl_bsa_event_participiant::class, 'setRefereeNameReverse',
            ],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['refereeNameReverse'],
            'headerFields' => ['date', 'seasonId', 'type'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['refereeNameReverse'],
            'label_callback' => [tl_bsa_event_participiant::class, 'getLabel'],
        ],
        'global_operations' => $typs[Input::get('do')]['global_operations'],
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
        'default' => 'refereeId,type',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'refereeId' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'Schiedsrichter wählen', 'tl_class' => 'w50'],
            'options_callback' => ['teusal.referee.available_referees', 'getRefereeOptions'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'refereeNameReverse' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['doNotShow' => true],
            'sql' => "varchar(103) NOT NULL default ''",
        ],
        'type' => [
            'filter' => true,
            'inputType' => $typs[Input::get('do')]['inputType'],
            'options' => $typs[Input::get('do')]['values'],
            'reference' => &$GLOBALS['TL_LANG']['tl_bsa_event_participiant']['typen'],
            'eval' => $typs[Input::get('do')]['eval'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_event_participiant.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_event_participiant extends Backend
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
     * setting the name of the referee.
     *
     * @param DataContainer $dc Data Container object
     */
    public function setRefereeNameReverse(DataContainer $dc): void
    {
        $this->Database->prepare('UPDATE tl_bsa_event_participiant AS participiant, tl_bsa_referee AS sr '.
                                 'SET participiant.refereeNameReverse=sr.nameReverse '.
                                 'WHERE participiant.refereeId=sr.id AND participiant.id=?')
            ->execute($dc->id)
        ;
    }

    /**
     * Generates the label of a data set.
     *
     * @param array         $row   Record data
     * @param string        $label Current label
     * @param DataContainer $dc    Data Container object
     *
     * @return string
     */
    public function getLabel($row, $label, DataContainer $dc)
    {
        $sql = 'SELECT v.nameShort FROM tl_bsa_club AS v, tl_bsa_referee AS s WHERE (v.id=s.clubId AND s.id=?)';
        $sql .= ' UNION ';
        $sql .= 'SELECT v.nameShort FROM tl_bsa_club AS v, tl_bsa_club_chairman AS o WHERE v.id=o.clubId AND (o.chairman=? OR o.viceChairman1=? OR o.viceChairman2=?)';
        $sql .= ' ORDER BY nameShort';

        $arrVerein = $this->Database->prepare($sql)
            ->execute($row['refereeId'], $row['refereeId'], $row['refereeId'], $row['refereeId'])
            ->fetchEach('nameShort')
        ;

        $strVerein = implode(', ', $arrVerein);

        return $label.' ('.$strVerein.')'.('regelarbeit' === Input::get('do') ? ' - '.str_replace('.', ',', (string) ((float) ($row['type']))).' Punkte' : '');
    }
}
