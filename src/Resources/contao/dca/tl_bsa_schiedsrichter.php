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
use Contao\Date;
use Contao\DC_Table;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Teusal\ContaoPhoneNumberNormalizerBundle\Library\PhoneNumberNormalizer;
use Teusal\ContaoRefereeHamburgBundle\Library\Addressbook\AddressbookSynchronizer;
use Teusal\ContaoRefereeHamburgBundle\Library\Member\BSAMember;
use Teusal\ContaoRefereeHamburgBundle\Library\Newsletter\BSANewsletter;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaFreigabenModel;

if ('schiedsrichter' === Input::get('do')) {
    $palette = 'vorname,nachname,ausweisnummer,verein;geschlecht;geburtsdatum,geburtsdatum_date;strasse,plz,ort;telefon1,telefon2,telefon_mobil,fax,email,email_kontaktformular;sr_seit,sr_seit_date,status;image,image_print,image_exempted;searchable,deleted';
    $sortingFilter = [['verein IS NOT NULL AND verein>?', '0']];
    $sortingPanelLayout = 'filter;search,limit';
    $sortingFields = ['verein', 'name_rev'];
} else {
    $palette = 'vorname,nachname;geschlecht;strasse,plz,ort;telefon1,telefon2,telefon_mobil,fax;email;deleted';
    $sortingFilter = [['verein IS NULL OR verein=?', '0']];
    $sortingPanelLayout = 'search,limit';
    $sortingFields = ['name_rev'];
}

$GLOBALS['TL_DCA']['tl_bsa_schiedsrichter'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'notDeletable' => true,
        'onsubmit_callback' => [
            [tl_bsa_schiedsrichter::class, 'submit'],
            [BSAMember::class, 'executeSubmitSchiedsrichter'],
            [BSANewsletter::class, 'executeSubmitSchiedsrichter'],
            [AddressbookSynchronizer::class, 'executeSubmitSchiedsrichter'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'ausweisnummer' => 'unique',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'fields' => $sortingFields,
            'panelLayout' => $sortingPanelLayout,
            'filter' => $sortingFilter,
        ],
        'label' => [
            'fields' => ['name_rev'],
            'label_callback' => [tl_bsa_schiedsrichter::class, 'listSchiedsrichter'],
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'delete' => [
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\')) return false; Backend.getScrollOffset();return AjaxRequest.executeDelete(this)"',
                'button_callback' => [tl_bsa_schiedsrichter::class, 'deleteIcon'],
            ],
            'undo' => [
                'icon' => 'undo.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.executeUndo(this)"',
                'button_callback' => [tl_bsa_schiedsrichter::class, 'undoIcon'],
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
        'default' => $palette,
    ],

    // Subpalettes
    'subpalettes' => [
        '' => '',
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'ausweisnummer' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 12, 'minlength' => 12, 'unique' => true, 'rgxp' => 'digit', 'tl_class' => 'w50'],
            'sql' => 'varchar(12) NULL',
        ],
        'geschlecht' => [
            'inputType' => 'select',
            'filter' => true,
            'options' => ['m', 'w', 'd'],
            'reference' => &$GLOBALS['TL_LANG']['genders'],
            'eval' => ['mandatory' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'keine Angabe', 'maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => "varchar(1) NOT NULL default ''",
        ],
        'nachname' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'vorname' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 50, 'tl_class' => 'w50'],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'name_rev' => [
            'sql' => "varchar(103) NOT NULL default ''",
        ],
        'verein' => [
            'inputType' => 'select',
            'filter' => true,
            'eval' => ['multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'Verein wählen', 'mandatory' => true, 'tl_class' => 'w50'],
            'foreignKey' => 'tl_bsa_verein.name_kurz',
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'strasse' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'long'],
            'sql' => "varchar(100) NOT NULL default ''",
        ],
        'plz' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 5, 'tl_class' => 'w50'],
            'sql' => "varchar(5) NOT NULL default ''",
        ],
        'ort' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 100, 'tl_class' => 'w50'],
            'sql' => "varchar(100) NOT NULL default ''",
        ],
        'telefon1' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'telefon2' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'telefon_mobil' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'fax' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 50, 'tl_class' => 'w50'],
            'save_callback' => [
                [PhoneNumberNormalizer::class, 'format'],
            ],
            'sql' => "varchar(50) NOT NULL default ''",
        ],
        'email' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 100, 'rgxp' => 'email', 'tl_class' => 'long clr'],
            'sql' => "varchar(150) NOT NULL default ''",
        ],
        'email_kontaktformular' => [
            'inputType' => 'text',
            'search' => true,
            'eval' => ['maxlength' => 100, 'rgxp' => 'email', 'tl_class' => 'long'],
            'sql' => "varchar(150) NOT NULL default ''",
        ],
        'geburtsdatum' => [
            'default' => '0',
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(11) NULL',
        ],
        'sr_seit' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'rgxp' => 'date', 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'sql' => 'int(11) NULL',
        ],
        'status' => [
            'filter' => true,
            'inputType' => 'text',
            'eval' => ['maxlength' => 25, 'tl_class' => 'w50'],
            'sql' => "varchar(25) NOT NULL default ''",
        ],
        'deleted' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => ['disabled' => true],
            'sql' => "char(1) NOT NULL default ''",
        ],
        'geburtsdatum_date' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'minlength' => 10, 'tl_class' => 'w50'],
            'save_callback' => [
                [tl_bsa_schiedsrichter::class, 'setDBGeburtsdatum'],
            ],
            'sql' => 'date NULL',
        ],
        'sr_seit_date' => [
            'inputType' => 'text',
            'eval' => ['maxlength' => 10, 'minlength' => 10, 'tl_class' => 'w50'],
            'save_callback' => [
                [tl_bsa_schiedsrichter::class, 'setSRSeitDatum'],
            ],
            'sql' => 'date NULL',
        ],
        'image' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,gif,jpeg,png', 'path' => 'files/Bilder/Schiedsrichter/web/Einzelfotos'],
            'sql' => 'binary(16) NULL',
        ],
        'image_print' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,gif,jpeg,png', 'path' => 'files/Bilder/Schiedsrichter/druck/Einzelfotos'],
            'sql' => 'binary(16) NULL',
        ],
        'image_exempted' => [
            'inputType' => 'fileTree',
            'eval' => ['fieldType' => 'radio', 'files' => true, 'filesOnly' => true, 'extensions' => 'jpg,gif,jpeg,png', 'path' => 'files/Bilder/Schiedsrichter/web/Freisteller'],
            'sql' => 'binary(16) NULL',
        ],
        'searchable' => [
            'filter' => true,
            'inputType' => 'checkbox',
            'eval' => [],
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'is_new' => [
            'exclude' => true,
            'sql' => "char(1) NOT NULL default '1'",
        ],
        'import_key' => [
            'exclude' => true,
            'eval' => ['doNotShow' => true],
            'sql' => 'varchar(6) NULL',
        ],
        'addressbook_vcards' => [
            'exclude' => true,
            'sql' => 'blob NULL',
        ],
    ],
];

/**
 * Class tl_bsa_schiedsrichter.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_schiedsrichter extends Backend
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
     * Return the "toggle visibility" button.
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
        if (null !== Input::get('did') && strlen(Input::get('did'))) {
            $this->executeDelete(Input::get('did'));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_bsa_schiedsrichter::deleted', 'alexf')) {
            return '';
        }

        if ($row['deleted']) {
            return Image::getHtml(preg_replace('/\.gif$/i', '__.gif', $icon)).' ';
        }

        $href .= '&amp;did='.$row['id'];

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Return the "toggle visibility" button.
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
    public function undoIcon($row, $href, $label, $title, $icon, $attributes, $table, $rootRecordIds, $childRecordIds, $circularReference, $previous, $next, DataContainer $dc): string
    {
        if (null !== Input::get('dud') && strlen(Input::get('dud'))) {
            $this->executeUndo(Input::get('dud'));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_bsa_schiedsrichter::deleted', 'alexf')) {
            return '';
        }

        if (!$row['deleted']) {
            return Image::getHtml(preg_replace('/\.gif$/i', '_.gif', $icon)).' ';
        }

        $href .= '&amp;dud='.$row['id'];

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * Delete a referee.
     *
     * @param mixed $intId
     */
    public function executeDelete($intId): void
    {
        $intId = (int) $intId;

        // Check permissions
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_bsa_schiedsrichter::deleted', 'alexf')) {
            $this->log('Not enough permissions to activate/deactivate member ID "'.$intId.'"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_bsa_schiedsrichter SET tstamp=$time, deleted='1' WHERE id=?")
            ->execute($intId)
        ;

        // add histroy to referee
        SRHistory::insertByDeleteSchiedsrichter($intId);
    }

    /**
     * Disable/enable.
     *
     * @param mixed $intId
     */
    public function executeUndo($intId): void
    {
        $intId = (int) $intId;

        // Check permissions
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_bsa_schiedsrichter::deleted', 'alexf')) {
            $this->log('Not enough permissions to activate/deactivate member ID "'.$intId.'"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $time = time();

        // Update the database
        $this->Database->prepare("UPDATE tl_bsa_schiedsrichter SET tstamp=$time, deleted='' WHERE id=?")
            ->execute($intId)
        ;

        // add histroy to referee
        SRHistory::insertByUndeleteSchiedsrichter($intId);
    }

    /**
     * sets the date of birth in format 'Y-m-d' into this field.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function setDBGeburtsdatum($varValue, DataContainer $dc)
    {
        $geburtsdatum = new Date($this->Input->post('geburtsdatum'));

        return date('Y-m-d', $geburtsdatum->__get('tstamp'));
    }

    /**
     * sets the referee since date in format 'Y-m-d' into this field.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function setSRSeitDatum($varValue, DataContainer $dc)
    {
        $srSeit = new Date($this->Input->post('sr_seit'));

        return date('Y-m-d', $srSeit->__get('tstamp'));
    }

    /**
     * Add the type of input field.
     *
     * @param array         $arrRow  Record data
     * @param string        $label   Current label
     * @param DataContainer $dc      Data Container object
     * @param array         $columns Columns with existing labels
     *
     * @return string
     */
    public function listSchiedsrichter($arrRow, $label, $dc, $columns)
    {
        if ($arrRow['deleted']) {
            $style = 'text-decoration:line-through;';
            $title = 'Der Schiedsrichter wurde gelöscht.';
            $bold = false;
        } else {
            $key = 'unpublished';
            $title = 'Keine Freigaben für die Website.';
            $bold = true;

            $freigabe = BsaFreigabenModel::findFreigabe($arrRow['id']);

            if (isset($freigabe)) {
                $key = 'published';

                $objDate = new Date($freigabe->__get('formular_erhalten_am'));
                $title = 'Freigaben erteilt am '.$objDate->__get('date');
            }
        }

        $name = $arrRow['name_rev'];

        if ($bold) {
            $name = '<strong>'.$name.'</strong>';
        }

        return '<div class="cte_type '.$key.'" style="'.$style.'" title="'.$title.'">'.$name.'</div>';
    }

    /**
     * Add the action to the referees history.
     *
     * @param DataContainer $dc Data Container object
     */
    public function submit(DataContainer $dc): void
    {
        if ($dc->__get('activeRecord')->is_new) {
            SRHistory::insert($dc->id, null, ['Schiedsrichter', 'ADD'], 'Der Schiedsrichter %s wurde manuell angelegt.', __METHOD__);
            $this->Database->prepare('UPDATE tl_bsa_schiedsrichter SET is_new=? WHERE id=?')
                ->execute('', $dc->id)
            ;
        } else {
            SRHistory::insert($dc->id, null, ['Schiedsrichter', 'EDIT'], 'Der Schiedsrichter %s wurde manuell bearbeitet.', __METHOD__);
        }
    }
}
