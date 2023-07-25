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
use Contao\DC_File;
use Contao\Input;

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_bsa_global_settings'] = [
    // Config
    'config' => [
        'dataContainer' => DC_File::class,

        'closed' => true,
    ],

    // Palettes
    'palettes' => [
        '__selector__' => ['createAddressbooks'],
        'default' => '{general_legend},bsa_name,bsa_domain;{kontaktformular_legend},bsa_kontaktformular;{addressbook_legend},createAddressbooks',
    ],

    // Subpalettes
    'subpalettes' => [
        'createAddressbooks' => 'addressbookURI,addressbookUsername,addressbookPassword,defaultAddressbookTokenId',
    ],

    // Fields
    'fields' => [
        'bsa_name' => [
            'inputType' => 'radio',
            'options' => $GLOBALS['BSA'],
            'reference' => &$GLOBALS['BSA_NAMES'],
            'eval' => ['mandatory' => true],
        ],
        'bsa_domain' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true],
        ],
        'bsa_kontaktformular' => [
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => ['mandatory' => true, 'fieldType' => 'radio'],
            'relation' => ['type' => 'hasOne', 'load' => 'eager'],
        ],
        'createAddressbooks' => [
            'inputType' => 'checkbox',
            'eval' => ['submitOnChange' => true],
        ],
        'addressbookURI' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => true, 'nospace' => true, 'rgxp' => 'url', 'tl_class' => 'long'],
        ],
        'addressbookUsername' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
        ],
        'addressbookPassword' => [
            'inputType' => 'textStore',
            'eval' => ['decodeEntities' => true, 'tl_class' => 'w50'],
            'save_callback' => [[tl_bsa_global_settings::class, 'storeAddressbookPass']],
        ],
        'defaultAddressbookTokenId' => [
            'inputType' => 'text',
            'eval' => ['mandatory' => false, 'maxlength' => 50, 'nospace' => true, 'tl_class' => 'long clr'],
        ],
    ],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_global_settings extends Backend
{
    /**
     * Store the unfiltered addressbook password.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function storeAddressbookPass($varValue, DataContainer $dc)
    {
        if (isset($_POST[$dc->field])) {
            return Input::postUnsafeRaw($dc->field);
        }

        return $varValue;
    }
}
