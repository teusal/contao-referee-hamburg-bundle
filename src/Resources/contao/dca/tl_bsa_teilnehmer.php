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

$typs['']['eval'] = [];
$typs['sitzung']['eval'] = [];
$typs['obleute']['eval'] = [];
$typs['training']['eval'] = [];
$typs['regelarbeit']['eval'] = ['rgxp' => 'digit', 'mandatory' => true];
$typs['coaching']['eval'] = [];
$typs['lehrgang']['eval'] = [];
$typs['helsen']['eval'] = [];
$typs['sonstige']['eval'] = [];

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
 * Table tl_bsa_teilnehmer
 */
$GLOBALS['TL_DCA']['tl_bsa_teilnehmer'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ptable' => 'tl_bsa_veranstaltung',
        'enableVersioning' => false,
        'onsubmit_callback' => [
            [
                tl_bsa_teilnehmer::class, 'setSRName',
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
            'fields' => ['sr'],
            'headerFields' => ['datum', 'saison_id', 'typ'],
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['sr'],
            'label_callback' => [tl_bsa_teilnehmer::class, 'getLabel'],
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
        'default' => 'sr_id,typ',
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
        'sr_id' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'Schiedsrichter wählen'],
            'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sr' => [
            'search' => true,
            'inputType' => 'text',
            'eval' => ['doNotShow' => true],
            'sql' => "varchar(103) NOT NULL default ''",
        ],
        'typ' => [
            'filter' => true,
            'inputType' => $typs[Input::get('do')]['inputType'],
            'options' => $typs[Input::get('do')]['values'],
            'reference' => &$GLOBALS['TL_LANG']['tl_bsa_teilnehmer']['typen'],
            'eval' => $typs[Input::get('do')]['eval'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_teilnehmer.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_teilnehmer extends Backend
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
    public function setSRName(DataContainer $dc): void
    {
        $this->Database->execute('UPDATE tl_bsa_teilnehmer AS st, tl_bsa_schiedsrichter AS sr SET st.sr=sr.name_rev WHERE st.sr_id=sr.id AND st.id='.$dc->id);
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
        $sql = 'SELECT v.name_kurz FROM tl_bsa_verein AS v, tl_bsa_schiedsrichter AS s WHERE (v.id=s.verein AND s.id=?)';
        $sql .= ' UNION  ';
        $sql .= 'SELECT v.name_kurz FROM tl_bsa_verein AS v, tl_bsa_verein_obmann AS o WHERE v.id=o.verein AND (o.obmann=? OR o.stellv_obmann_1=? OR o.stellv_obmann_2=?)';
        $sql .= ' ORDER BY name_kurz';

        $arrVerein = $this->Database->prepare($sql)
            ->execute($row['sr_id'], $row['sr_id'], $row['sr_id'], $row['sr_id'])
            ->fetchEach('name_kurz')
        ;

        $strVerein = implode(', ', $arrVerein);

        return $label.' ('.$strVerein.')'.('regelarbeit' === Input::get('do') ? ' - '.str_replace('.', ',', (float) ($row['typ'])).' Punkte' : '');
    }
}
