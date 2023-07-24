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
use Teusal\ContaoPhoneNumberNormalizerBundle\Library\PhoneNumberNormalizer;
use Teusal\ContaoRefereeHamburgBundle\Library\Test;

/*
 * Table tl_bsa_verein
 */
$GLOBALS['TL_DCA']['tl_bsa_verein'] = [
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
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name_kurz'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['name', 'nummer', 'anzahl_schiedsrichter_aktiv', 'anzahl_schiedsrichter_passiv'],
            'format' => '<div style="float:left;">%s (DFBnet: %s)</div><div style="text-align:right;">Anzahl SR: %s aktiv, %s passiv</div>',
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
        'default' => 'nummer,name,name_kurz;strasse,name_zusatz,plz,ort,telefon1,telefon2,fax,email;image;homepage1,homepage2;anzeigen',
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
            'eval' => ['mandatory' => true, 'maxlength' => 100, 'tl_class' => 'w50', 'unique' => true],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'name_kurz' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 20, 'tl_class' => 'w50'],
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'nummer' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 6, 'minlength' => 6, 'unique' => true, 'rgxp' => 'digit'],
            'sql' => "varchar(6) NOT NULL default ''",
        ],
        'name_zusatz' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'strasse' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'plz' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => 'varchar(5) NULL',
        ],
        'ort' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => 'varchar(50) NULL',
        ],
        'telefon1' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'save_callback' => [[PhoneNumberNormalizer::class, 'format']],
            'sql' => 'varchar(100) NULL',
        ],
        'telefon2' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'save_callback' => [[PhoneNumberNormalizer::class, 'format']],
            'sql' => 'varchar(100) NULL',
        ],
        'fax' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'save_callback' => [[PhoneNumberNormalizer::class, 'format']],
            'sql' => 'varchar(100) NULL',
        ],
        'email' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'clr long'],
            'sql' => 'varchar(100) NULL',
        ],
        'image' => [
            'inputType' => 'fileTree',
            'eval' => ['files' => true, 'fieldType' => 'radio', 'filesOnly' => true, 'extensions' => 'gif,jpg,png', 'path' => 'files/Bilder/Vereinslogos'],
            'sql' => 'binary(16) NULL',
        ],
        'homepage1' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'homepage2' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => 'varchar(100) NULL',
        ],
        'anzahl_schiedsrichter_aktiv' => [
            'sql' => "int(4) NOT NULL default '0'",
        ],
        'anzahl_schiedsrichter_passiv' => [
            'sql' => "int(4) NOT NULL default '0'",
        ],
        'anzeigen' => [
            'inputType' => 'checkbox',
            'default' => true,
            'filter' => true,
            'sql' => "char(1) NOT NULL default '1'",
        ], ],
];

/**
 * Class tl_bsa_verein.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_verein extends Backend
{
    public function test($varValue, $dc): void
    {
        throw new Exception(Test::TEXT);
    }

    // TODO implementation of exports
    public function exportXLS(): void
    {
        require_once 'ContaoExport/ExportHandler.php';
        $this->import('ExportHandler');

        $this->ExportHandler->setConfig($GLOBALS['TL_CONFIG']);
        $this->ExportHandler->set('protect', false);
        $fileToken = $this->ExportHandler->writeToken();

        $this->redirect('http://export.'.$GLOBALS['TL_CONFIG']['bsa_domain'].'/vereine.php?token='.$this->ExportHandler->getTokenId($fileToken));
    }
}
