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
        'enableVersioning' => false,
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
            'mode' => DataContainer::MODE_SORTABLE,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => ['clubId'],
            'panelLayout' => 'sort,search,limit',
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['clubId', 'chairman', 'viceChairman1', 'viceChairman2', 'tstamp'],
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
            'label' => &$GLOBALS['TL_LANG']['MSC']['tstamp'],
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'eval' => ['rgxp' => 'date'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'clubId' => [
            'inputType' => 'select',
            'sorting' => true,
            'search' => true,
            'eval' => ['multiple' => false, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'Verein wählen', 'mandatory' => true, 'unique' => true, 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_club.nameShort',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'chairman' => [
            'inputType' => 'select',
            'sorting' => true,
            'search' => true,
            'eval' => ['multiple' => false, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein Obmann', 'tl_class' => 'w50'],
            'options_callback' => ['teusal.referee.available_referees', 'getRefereeOptions'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'save_callback' => [
                [tl_bsa_club_chairman::class, 'saveObmann'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'viceChairman1' => [
            'inputType' => 'select',
            'sorting' => true,
            'search' => true,
            'eval' => ['multiple' => false, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein stellv. Obmann', 'tl_class' => 'w50 clr'],
            'options_callback' => ['teusal.referee.available_referees.include_deleted', 'getRefereeOptions'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'save_callback' => [
                [tl_bsa_club_chairman::class, 'saveStellv1'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'viceChairman2' => [
            'inputType' => 'select',
            'sorting' => true,
            'search' => true,
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein stellv. Obmann', 'tl_class' => 'w50'],
            'options_callback' => ['teusal.referee.available_referees', 'getRefereeOptions'],
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
        return $this->updateChairmansGroup($varValue, $dc, 'chairman');
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
        return $this->updateChairmansGroup($varValue, $dc, 'viceChairman1');
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
        return $this->updateChairmansGroup($varValue, $dc, 'viceChairman2');
    }

    /**
     * Add the type of content element.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function doDelete(DataContainer $dc, $undoId): void
    {
        if (0 !== (int) $dc->activeRecord->chairman) {
            BSAMemberGroup::removeFromChairmansGroup((int) $dc->activeRecord->chairman);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->activeRecord->chairman);
        }

        if (0 !== (int) $dc->activeRecord->viceChairman1) {
            BSAMemberGroup::removeFromChairmansGroup((int) $dc->activeRecord->viceChairman1);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->activeRecord->viceChairman1);
        }

        if (0 !== (int) $dc->activeRecord->viceChairman2) {
            BSAMemberGroup::removeFromChairmansGroup((int) $dc->activeRecord->viceChairman2);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->activeRecord->viceChairman2);
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
    private function updateChairmansGroup($varValue, DataContainer $dc, $field)
    {
        if ($dc->activeRecord->$field !== $varValue) {
            if (0 !== (int) $varValue) {
                BSAMemberGroup::addToChairmansGroup((int) $varValue);
                $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $varValue);
            }

            if (0 !== (int) $dc->activeRecord->$field) {
                BSAMemberGroup::removeFromChairmansGroup((int) $dc->activeRecord->$field);
                $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->activeRecord->$field);
            }
        }

        return $varValue;
    }
}
