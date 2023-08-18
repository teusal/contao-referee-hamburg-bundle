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
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;

/*
 * Table tl_bsa_website_data_release
 */
$GLOBALS['TL_DCA']['tl_bsa_website_data_release'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'ondelete_callback' => [
            [tl_bsa_website_data_release::class, 'delete'],
        ],
        'onundo_callback' => [
            [tl_bsa_website_data_release::class, 'undoDelete'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'refereeId' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['refereeId'],
            'flag' => DataContainer::SORT_ASC,
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['refereeId', 'dateOfFormReceived', 'tstamp'],
            'showColumns' => true,
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
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
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => [],
        'default' => '{form_legend},refereeId,dateOfFormReceived;{release_legend},showStreet,showPostal,showCity,showDateOfBirth,showPhone1,showPhone2,showMobile,showFax,showPhoto,showEmail',
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
        'refereeId' => [
            'inputType' => 'select',
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'search' => true,
            'eval' => ['unique' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Schiedsrichter wählen', 'mandatory' => true, 'tl_class' => 'w50'],
            'options_callback' => ['teusal.referee.available_referees', 'getRefereeOptions'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'saveSchiedsrichter'],
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'dateOfFormReceived' => [
            'inputType' => 'text',
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'eval' => ['mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr w50 wizard'],
            'sql' => "varchar(10) NOT NULL default ''",
        ],
        'showDateOfBirth' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchDateOfBirth'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showStreet' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchStreet'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showPostal' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchZipcode'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showCity' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchCity'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showPhone1' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchPhone1'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showPhone2' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchPhone2'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showMobile' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchPhoneMobile'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showFax' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchFax'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showEmail' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchEmail'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'showPhoto' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [
                [tl_bsa_website_data_release::class, 'switchPhoto'],
            ],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_website_data_release.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_website_data_release extends Backend
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
     * adding an entry into the referees history.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function delete(DataContainer $dc, $undoId): void
    {
        SRHistory::insert($dc->activeRecord->refereeId, null, ['Web-Freigabe', 'REMOVE'], 'Die Freigaben des Schiedsrichters %s wurden gelöscht.', __METHOD__);
    }

    /**
     * adding an entry into the referees history.
     *
     * @param string        $table Table
     * @param array<mixed>  $row   Record data
     * @param DataContainer $dc    Data Container object
     */
    public function undoDelete($table, $row, DataContainer $dc): void
    {
        SRHistory::insert($row['refereeId'], null, ['Web-Freigabe', 'ADD'], 'Die Freigaben des Schiedsrichters %s wurden wiederhergestellt.', __METHOD__);
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function saveSchiedsrichter($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->activeRecord->refereeId) {
            SRHistory::insert($varValue, null, ['Web-Freigabe', 'ADD'], 'Die Freigaben des Schiedsrichters %s wurden angelegt.', __METHOD__);

            if (0 !== $dc->activeRecord->refereeId) {
                SRHistory::insert($dc->activeRecord->refereeId, null, ['Web-Freigabe', 'REMOVE'], 'Die Freigaben des Schiedsrichters %s wurden gelöscht.', __METHOD__);
            }
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchDateOfBirth($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showDateOfBirth !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showDateOfBirth', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchStreet($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showStreet !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showStreet', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchZipcode($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showPostal !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showPostal', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchCity($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showCity !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showCity', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchPhone1($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showPhone1 !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showPhone1', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchPhone2($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showPhone2 !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showPhone2', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchPhoneMobile($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showMobile !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showMobile', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchFax($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showFax !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showFax', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchEmail($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showEmail !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showEmail', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function switchPhoto($varValue, DataContainer $dc)
    {
        if ($dc->activeRecord->showPhoto !== $varValue) {
            $this->insertRefereeHistory($dc->activeRecord->refereeId, 'showPhoto', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param int    $intSR
     * @param string $field
     * @param mixed  $varValue
     */
    private function insertRefereeHistory($intSR, $field, $varValue): void
    {
        if (0 !== $intSR) {
            SRHistory::insert($intSR, null, ['Web-Freigabe', 'EDIT'], 'Die Freigabe "'.$GLOBALS['TL_LANG']['tl_bsa_website_data_release'][$field][2].'" des Schiedsrichters %s wurden '.($varValue ? 'aktiviert' : 'deaktiviert').'.', __METHOD__);
        }
    }
}
