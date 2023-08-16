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
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;
use Contao\StringUtil;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;

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
            'options_callback' => [tl_bsa_club_chairman::class, 'getRefereeOptions'],
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
     * clubs.
     *
     * @var array<int, array<string, mixed>>
     */
    protected $arrClubs = [];

    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
        $this->import(BSANewsletter::class, 'BSANewsletter');

        // load the clubs in an array. the key is set by the id, the value is by nameShort.
        $objClub = ClubModel::findAll(['order' => 'nameShort']);

        if (isset($objClub)) {
            while ($objClub->next()) {
                $this->arrClubs[$objClub->id] = [
                    'number' => $objClub->number,
                    'nameShort' => StringUtil::specialchars($objClub->nameShort),
                    'visible' => $objClub->published,
                ];
            }
        }
        $this->arrClubs[0] = ['number' => '', 'nameShort' => 'vereinslos', 'visible' => false];
    }

    /**
     * returns all possible recipient options.
     *
     * @param DataContainer|null $dc Data Container object
     * @return array<int, array<string, string>> the list of selectable options
     */
    public function getRefereeOptions(DataContainer $dc): array
    {
        $arrRecipientOptions = [];

        if(isset($dc)) {
            $strField = $dc->field;
            // as first add an option, if the selected referee is deleted
            $objReferee = RefereeModel::findByPk($dc->activeRecord->$strField);
            if(isset($objReferee) && $objReferee->deleted) {
                $arrRecipientOptions['gelöscht'][$objReferee->id] = StringUtil::specialchars($objReferee->nameReverse);
            }
        }

        // add empty containers for all clubs
        foreach ($this->arrClubs as $club) {
            $arrRecipientOptions[$club['nameShort']] = [];
        }

        // loading all referees and sort each to his club
        $objReferee = RefereeModel::findByDeleted('', ['order' => 'nameReverse']);

        if (isset($objReferee)) {
            while ($objReferee->next()) {
                $arrRecipientOptions[$this->arrClubs[$objReferee->clubId]['nameShort']][$objReferee->id] = StringUtil::specialchars($objReferee->nameReverse);
            }
        }

        // remove empty clubs
        foreach ($arrRecipientOptions as $clubName => $arrReferees) {
            if (empty($arrReferees)) {
                unset($arrRecipientOptions[$clubName]);
            }
        }

        return $arrRecipientOptions;
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
        if (0 !== (int) $dc->__get('activeRecord')->chairman) {
            BSAMemberGroup::removeFromChairmansGroup((int) $dc->__get('activeRecord')->chairman);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->__get('activeRecord')->chairman);
        }

        if (0 !== (int) $dc->__get('activeRecord')->viceChairman1) {
            BSAMemberGroup::removeFromChairmansGroup((int) $dc->__get('activeRecord')->viceChairman1);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->__get('activeRecord')->viceChairman1);
        }

        if (0 !== (int) $dc->__get('activeRecord')->viceChairman2) {
            BSAMemberGroup::removeFromChairmansGroup((int) $dc->__get('activeRecord')->viceChairman2);
            $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->__get('activeRecord')->viceChairman2);
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
        if ($varValue !== $dc->__get('activeRecord')->$field) {
            if (0 !== (int) $varValue) {
                BSAMemberGroup::addToChairmansGroup((int) $varValue);
                $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $varValue);
            }

            if (0 !== (int) $dc->__get('activeRecord')->$field) {
                BSAMemberGroup::removeFromChairmansGroup((int) $dc->__get('activeRecord')->$field);
                $this->BSANewsletter->synchronizeNewsletterBySchiedsrichter((int) $dc->__get('activeRecord')->$field);
            }
        }

        return $varValue;
    }
}
