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
use Teusal\ContaoPhoneNumberNormalizerBundle\Library\PhoneNumberNormalizer;

/*
 * Table tl_bsa_club
 */
$GLOBALS['TL_DCA']['tl_bsa_club'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'notDeletable' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'name' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['nameShort'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['nameShort', 'name', 'number', 'refereesActiveQuantity', 'refereesPassiveQuantity'],
            'showColumns' => true,
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => [''],
        'default' => '{club_data_legend},number,name,nameShort;{address_legend},street,nameAddition,postal,city;{contact_legend},phone1,phone2,fax,email;{image_legend},image;{web_legend},homepage1,homepage2;{publishing_legend},published',
    ],

    // Subpalettes
    'subpalettes' => [
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name' => [
            'inputType' => 'text',
            'sorting' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 100, 'tl_class' => 'w50', 'unique' => true],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'nameShort' => [
            'inputType' => 'text',
            'sorting' => true,
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 20, 'tl_class' => 'w50'],
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'number' => [
            'inputType' => 'text',
            'sorting' => true,
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 6, 'minlength' => 6, 'unique' => true, 'rgxp' => 'digit'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'nameAddition' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'street' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'postal' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => 'varchar(5) NULL',
        ],
        'city' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => 'varchar(50) NULL',
        ],
        'phone1' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => 'varchar(100) NULL',
        ],
        'phone2' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => 'varchar(100) NULL',
        ],
        'fax' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => 'varchar(100) NULL',
        ],
        'email' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50', 'rgxp' => 'email'],
            'sql' => 'varchar(100) NULL',
        ],
        'image' => [
            'inputType' => 'fileTree',
            'eval' => ['files' => true, 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'gif,jpg,png', 'path' => 'files/Bilder/Vereinslogos'],
            'sql' => 'binary(16) NULL',
        ],
        'homepage1' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'httpurl', 'maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'homepage2' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'httpurl', 'maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'refereesActiveQuantity' => [
            'sorting' => true,
            'sql' => "int(4) NOT NULL default '0'",
        ],
        'refereesPassiveQuantity' => [
            'sorting' => true,
            'sql' => "int(4) NOT NULL default '0'",
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
 * Class tl_bsa_club.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_club extends Backend
{
    // TODO implementation of exports
    public function exportXLS(): void
    {
        require_once 'ContaoExport/ExportHandler.php';
        $this->import('ExportHandler');

        $this->ExportHandler->setConfig($GLOBALS['TL_CONFIG']);
        $this->ExportHandler->set('protect', false);
        $fileToken = $this->ExportHandler->writeToken();

        $this->redirect('http://export.'.Config::get('bsa_domain').'/vereine.php?token='.$this->ExportHandler->getTokenId($fileToken));
    }
}
