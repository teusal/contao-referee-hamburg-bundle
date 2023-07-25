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
 * Table tl_bsa_freigaben
 */
$GLOBALS['TL_DCA']['tl_bsa_freigaben'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onsubmit_callback' => [[tl_bsa_freigaben::class, 'setSRName']],
        'ondelete_callback' => [[tl_bsa_freigaben::class, 'deleteFreigabe']],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'schiedsrichter' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['name_rev'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'filter;search,limit',
        ],
        'label' => [
            'fields' => ['formular_erhalten_am', 'name_rev'],
            'format' => '<span style="color:#b3b3b3;padding-right:3px">[%s]</span> %s',
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
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => [],
        'default' => 'schiedsrichter,formular_erhalten_am;zeige_strasse,zeige_plz,zeige_ort,zeige_geburtsdatum,zeige_telefon1,zeige_telefon2,zeige_telefon_mobil,zeige_fax,zeige_foto,link_email',
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
        'schiedsrichter' => [
            'inputType' => 'select',
            'filter' => true,
            'eval' => ['unique' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Schiedsrichter wählen', 'mandatory' => true, 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
            'save_callback' => [[tl_bsa_freigaben::class, 'saveSchiedsrichter']],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'name_rev' => [
            'search' => true,
            'eval' => ['doNotShow' => true],
            'sql' => "varchar(103) NOT NULL default ''",
        ],
        'formular_erhalten_am' => [
            'inputType' => 'text',
            'flag' => DataContainer::SORT_MONTH_DESC,
            'eval' => ['mandatory' => true, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'clr w50 wizard'],
            'sql' => "varchar(11) NOT NULL default ''",
        ],
        'zeige_geburtsdatum' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchDateOfBirth']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_strasse' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchStreet']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_plz' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchZipcode']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_ort' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchCity']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_telefon1' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchPhone1']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_telefon2' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchPhone2']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_telefon_mobil' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchPhoneMobile']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_fax' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchFax']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'link_email' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchEmail']],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'zeige_foto' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'save_callback' => [[tl_bsa_freigaben::class, 'switchPhoto']],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_freigaben.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_freigaben extends Backend
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
     * updating the active record with the referees name revers.
     */
    public function setSRName(DataContainer $dc): void
    {
        $this->Database->execute('UPDATE tl_bsa_freigaben AS f, tl_bsa_schiedsrichter AS sr SET f.name_rev=sr.name_rev WHERE f.schiedsrichter=sr.id AND f.id='.$dc->id);
    }

    /**
     * adding an entry into the referees history.
     *
     * @param int $undoId
     */
    public function deleteFreigabe(DataContainer $dc, $undoId): void
    {
        SRHistory::insert($dc->__get('activeRecord')->schiedsrichter, null, ['Web-Freigabe', 'REMOVE'], 'Die Freigaben des Schiedsrichters %s wurden gelöscht.', __METHOD__);
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function saveSchiedsrichter($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->schiedsrichter) {
            SRHistory::insert($varValue, null, ['Web-Freigabe', 'ADD'], 'Die Freigaben des Schiedsrichters %s wurden angelegt.', __METHOD__);

            if (0 !== $dc->__get('activeRecord')->schiedsrichter) {
                SRHistory::insert($dc->__get('activeRecord')->schiedsrichter, null, ['Web-Freigabe', 'REMOVE'], 'Die Freigaben des Schiedsrichters %s wurden gelöscht.', __METHOD__);
            }
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchDateOfBirth($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_geburtsdatum !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_geburtsdatum', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchStreet($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_strasse !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_strasse', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchZipcode($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_plz !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_plz', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchCity($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_ort !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_ort', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchPhone1($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_telefon1 !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_telefon1', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchPhone2($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_telefon2 !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_telefon2', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchPhoneMobile($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_telefon_mobil !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_telefon_mobil', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchFax($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_fax !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_fax', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchEmail($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->link_email !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'link_email', $varValue);
        }

        return $varValue;
    }

    /**
     * adding an entry into the referees history.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function switchPhoto($varValue, DataContainer $dc)
    {
        if ($dc->__get('activeRecord')->zeige_foto !== $varValue) {
            $this->insertRefereeHistory($dc->__get('activeRecord')->schiedsrichter, 'zeige_foto', $varValue);
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
            SRHistory::insert($intSR, null, ['Web-Freigabe', 'EDIT'], 'Die Freigabe "'.$GLOBALS['TL_LANG']['tl_bsa_freigaben'][$field][2].'" des Schiedsrichters %s wurden '.($varValue ? 'aktiviert' : 'deaktiviert').'.', __METHOD__);
        }
    }
}
