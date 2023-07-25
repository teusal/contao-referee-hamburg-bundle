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
use Teusal\ContaoRefereeHamburgBundle\Library\BSAMemberGroup;

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_bsa_verein_obmann'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ondelete_callback' => [[tl_bsa_verein_obmann::class, 'doDelete']],
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
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['verein'],
            'panelLayout' => 'limit',
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['verein', 'obmann', 'stellv_obmann_1', 'stellv_obmann_2'],
            'showColumns' => true,
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
        'default' => 'verein;obmann,stellv_obmann_1,stellv_obmann_2',
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
        'verein' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Verein wählen', 'mandatory' => true, 'unique' => true, 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_verein.name_kurz',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'obmann' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein Obmann', 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
            'save_callback' => [
                ['tl_bsa_verein_obmann', 'saveObmann'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'stellv_obmann_1' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein stellv. Obmann', 'tl_class' => 'w50 clr'],
            'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
            'save_callback' => [
                ['tl_bsa_verein_obmann', 'saveStellv1'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'stellv_obmann_2' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein stellv. Obmann', 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
            'save_callback' => [
                ['tl_bsa_verein_obmann', 'saveStellv2'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
    ],
];

/**
 * Class tl_bsa_verein_obmann.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_verein_obmann extends Backend
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
     * Add the type of content element.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function saveObmann($varValue, DataContainer $dc)
    {
        return $this->updateObleuteGroup($varValue, $dc, 'obmann');
    }

    /**
     * Add the type of content element.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function saveStellv1($varValue, DataContainer $dc)
    {
        return $this->updateObleuteGroup($varValue, $dc, 'stellv_obmann_1');
    }

    /**
     * Add the type of content element.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function saveStellv2($varValue, DataContainer $dc)
    {
        return $this->updateObleuteGroup($varValue, $dc, 'stellv_obmann_2');
    }

    /**
     * Add the type of content element.
     *
     * @param int $undoId
     *
     * @return mixed
     */
    public function doDelete(DataContainer $dc, $undoId): void
    {
        if (0 !== $dc->__get('activeRecord')->obmann) {
            BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->obmann);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->obmann);
        }

        if (0 !== $dc->__get('activeRecord')->stellv_obmann_1) {
            BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->stellv_obmann_1);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->stellv_obmann_1);
        }

        if (0 !== $dc->__get('activeRecord')->stellv_obmann_2) {
            BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->stellv_obmann_2);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->stellv_obmann_2);
        }
    }

    /**
     * Add the type of content element.
     *
     * @param mixed         $varValue
     * @param DataContainer $dc
     * @param string        $field
     *
     * @return mixed
     */
    private function updateObleuteGroup($varValue, $dc, $field)
    {
        if ($varValue !== $dc->__get('activeRecord')->$field) {
            if (0 !== $varValue) {
                BSAMemberGroup::addToObleute($varValue);
                $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($varValue);
            }

            if (0 !== $dc->__get('activeRecord')->$field) {
                BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->$field);
                $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->$field);
            }
        }

        return $varValue;
    }
}
