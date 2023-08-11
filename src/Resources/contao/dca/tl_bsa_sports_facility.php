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
use Contao\Config;
use Contao\DataContainer;
use Contao\DC_Table;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Teusal\ContaoPhoneNumberNormalizerBundle\Library\PhoneNumberNormalizer;

$paletteDefault = 'name;groundskeeper;street,postal,city;phone1,phone1Description,phone2,phone2Description;hvvLink;published';

if ('sporthalle' === Input::get('do')) {
    $paletteDefault = 'name;street,postal,city;hvvLink;published';
}

$GLOBALS['TL_DCA']['tl_bsa_sports_facility'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'ctable' => ['tl_bsa_sports_facility_number'],
        'enableVersioning' => true,
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
            'fields' => ['name'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
            'filter' => [['type=?', Input::get('do')]],
        ],
        'label' => [
            'fields' => ['name', 'address'],
            'showColumns' => true,
        ],
        'global_operations' => [
            'export' => [
                'href' => 'key=export',
                'attributes' => 'onclick="Backend.getScrollOffset();" style="padding:2px 0 3px 20px;background:url(\'/system/modules/x_bsa_sportplatz/assets/excel.png\') no-repeat left center;" onfocus="if(this.blur()){this.blur();}"',
            ],
        ],
        'operations' => [
            'edit' => [
                'href' => 'table=tl_bsa_sports_facility_number',
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
                'button_callback' => [tl_bsa_sports_facility::class, 'deleteIcon'],
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['hvvLink'],
        'default' => $paletteDefault,
    ],

    // Subpalettes
    'subpalettes' => [
        'hvvLink' => 'hvvId',
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
        'name' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 100, 'tl_class' => 'long'],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'type' => [
            'sql' => "varchar(25) NOT NULL default ''",
            'default' => Input::get('do'),
        ],
        'street' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 100, 'tl_class' => 'long'],
            'sql' => "varchar(100) NOT NULL default ''",
        ],
        'postal' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'city' => [
            'inputType' => 'text',
            'default' => 'Hamburg',
            'eval' => ['mandatory' => true, 'maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'address' => [
            // Aktualisierung via Trigger
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'phone1' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 25, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => 'varchar(25) NULL',
        ],
        'phone1Description' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => 'varchar(50) NULL',
        ],
        'phone2' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 25, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => 'varchar(25) NULL',
        ],
        'phone2Description' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => 'varchar(50) NULL',
        ],
        'hvvLink' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'hvvId' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50],
            'sql' => 'varchar(50) NULL',
        ],
        'groundskeeper' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein Platzwart'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'sql' => 'int(10) unsigned NULL',
        ],
        'published' => [
            'inputType' => 'checkbox',
            'default' => true,
            'filter' => true,
            'sql' => "char(1) NOT NULL default '1'",
        ],
    ],
];

/**
 * Class tl_bsa_sports_facility.
 */
class tl_bsa_sports_facility extends Backend
{
    private array $arrUsedIds;

    /**
     * contstruct. load all used id's of sports fields.
     */
    public function __construct()
    {
        parent::__construct();

        $this->arrUsedIds = [];

        $arrUsed = $this->Database->execute('SELECT * FROM tl_bsa_sports_facility_club_assignment')->fetchEach('sportsFacilityIds');

        foreach ($arrUsed as $used) {
            if (!empty($used)) {
                $arrIds = unserialize($used);

                if (!empty($arrIds) && is_array($arrIds)) {
                    $this->arrUsedIds = array_merge($this->arrUsedIds, $arrIds);
                }
            }
        }
    }

    public function exportXLS(): void
    {
        // TODO
        require_once 'ContaoExport/ExportHandler.php';
        $this->import('ExportHandler');

        $this->ExportHandler->setConfig($GLOBALS['TL_CONFIG']);
        $this->ExportHandler->set('protect', false);
        $fileToken = $this->ExportHandler->writeToken();

        $this->redirect('http://export.'.Config::get('bsa_domain').'/sportplatz.php?token='.$this->ExportHandler->getTokenId($fileToken));
    }

    /**
     * Return the "gruppenmitglieder" button.
     *
     * @param array         $row               Record data
     * @param string|null   $href              Button href
     * @param string        $label             Label
     * @param string        $title             Title
     * @param string|null   $icon              Icon
     * @param string        $attributes        HTML attributes
     * @param string        $table             Table
     * @param array         $rootRecordIds     IDs of all root records
     * @param array         $childRecordIds    IDs of all child records
     * @param bool          $circularReference Whether this is a circular reference of the tree view
     * @param string        $previous          “Previous” label
     * @param string        $next              “Next” label
     * @param DataContainer $dc                Data Container object
     */
    public function deleteIcon($row, $href, $label, $title, $icon, $attributes, $table, $rootRecordIds, $childRecordIds, $circularReference, $previous, $next, DataContainer $dc): string
    {
        if (in_array($row['id'], $this->arrUsedIds, true)) {
            return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

        $href .= '&amp;id='.$row['id'];

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }
}
