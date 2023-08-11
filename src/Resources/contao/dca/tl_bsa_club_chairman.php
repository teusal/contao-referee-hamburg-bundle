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
use Teusal\ContaoRefereeHamburgBundle\Library\Member\BSAMemberGroup;
use Teusal\ContaoRefereeHamburgBundle\Library\Newsletter\BSANewsletter;

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

$GLOBALS['TL_DCA']['tl_bsa_club_chairman'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'ondelete_callback' => [
            [tl_bsa_club_chairman::class, 'doDelete'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'clubId' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['clubId'],
            'panelLayout' => 'limit',
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['clubId', 'chairman', 'viceChairman1', 'viceChairman2'],
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
        'default' => 'clubId;chairman,viceChairman1,viceChairman2',
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
        'clubId' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Verein wählen', 'mandatory' => true, 'unique' => true, 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'chairman' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein Obmann', 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'save_callback' => [
                [tl_bsa_club_chairman::class, 'saveObmann'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'viceChairman1' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein stellv. Obmann', 'tl_class' => 'w50 clr'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'save_callback' => [
                [tl_bsa_club_chairman::class, 'saveStellv1'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'viceChairman2' => [
            'inputType' => 'select',
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein stellv. Obmann', 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'save_callback' => [
                [tl_bsa_club_chairman::class, 'saveStellv2'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
    ],
];

/**
 * Class tl_bsa_club_chairman.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 *
 * @property BSANewsletter $BSANewsletter
 */
class tl_bsa_club_chairman extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
        $this->import(BSANewsletter::class, 'BSANewsletter');
    }

    /**
     * Add the type of content element.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function saveObmann($varValue, DataContainer $dc)
    {
        return $this->updateObleuteGroup($varValue, $dc, 'chairman');
    }

    /**
     * Add the type of content element.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function saveStellv1($varValue, DataContainer $dc)
    {
        return $this->updateObleuteGroup($varValue, $dc, 'viceChairman1');
    }

    /**
     * Add the type of content element.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function saveStellv2($varValue, DataContainer $dc)
    {
        return $this->updateObleuteGroup($varValue, $dc, 'viceChairman2');
    }

    /**
     * Add the type of content element.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function doDelete(DataContainer $dc, $undoId): void
    {
        if (0 !== $dc->__get('activeRecord')->chairman) {
            BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->chairman);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->chairman);
        }

        if (0 !== $dc->__get('activeRecord')->viceChairman1) {
            BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->viceChairman1);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->viceChairman1);
        }

        if (0 !== $dc->__get('activeRecord')->viceChairman2) {
            BSAMemberGroup::removeFromObleute($dc->__get('activeRecord')->viceChairman2);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->viceChairman2);
        }
    }

    /**
     * Add the type of content element.
     *
     * @param mixed  $varValue
     * @param string $field
     *
     * @return mixed
     */
    private function updateObleuteGroup($varValue, DataContainer $dc, $field)
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
