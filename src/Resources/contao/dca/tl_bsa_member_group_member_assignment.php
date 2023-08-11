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
use Contao\Environment;
use Contao\Input;
use Contao\MemberGroupModel;
use Contao\Message;
use Contao\StringUtil;
use Teusal\ContaoRefereeHamburgBundle\Library\Addressbook\AddressbookSynchronizer;
use Teusal\ContaoRefereeHamburgBundle\Library\Member\BSAMemberGroup;
use Teusal\ContaoRefereeHamburgBundle\Library\Newsletter\BSANewsletter;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

$GLOBALS['TL_DCA']['tl_bsa_member_group_member_assignment'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'ptable' => 'tl_member_group',
        'onload_callback' => [
            [tl_bsa_member_group_member_assignment::class, 'onLoad'],
        ],
        'ondelete_callback' => [
            [tl_bsa_member_group_member_assignment::class, 'deleteGruppenmitglied'],
            [BSANewsletter::class, 'deleteGruppenmitglied'],
        ],
        'onsubmit_callback' => [
            [tl_bsa_member_group_member_assignment::class, 'submitGruppenmitglied'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'pid' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_PARENT,
            'fields' => ['sorting'],
            'panelLayout' => 'filter;search,limit',
            'headerFields' => ['name', 'automatik'],
        ],
        'label' => [
            'fields' => ['refereeId:tl_bsa_referee.nameReverse'],
        ],
        'global_operations' => [
            'halbautomatik' => [
                'href' => 'key=halbautomatik',
                'icon' => 'system/themes/default/images/reload.gif',
                'attributes' => 'onclick="if (!confirm(\'Sollen die Gruppenmitglieder anhand der Halbautomatik synchronisiert?\')) return false; Backend.getScrollOffset();"',
                'button_callback' => [tl_bsa_member_group_member_assignment::class, 'halbautomaticButton'],
            ],
            'sortAsc' => [
                'href' => 'key=sortAsc',
                'icon' => '/icon/sortascend.png',
                'attributes' => 'onclick="if (!confirm(\'Sollen alle Gruppenmitglieder einmalig aufsteigend nach Nachname und Vorname des Schiedsrichters sortiert werden?\')) return false; Backend.getScrollOffset();"',
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
                'attributes' => 'onclick="if(!confirm(\''.$GLOBALS['TL_LANG']['MSC']['deleteConfirm'].'\'))return false;Backend.getScrollOffset()"',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        '__selector__' => [],
        'default' => 'funktion,refereeId',
    ],

    // Subpalettes
    'subpalettes' => [
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'sorting' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'funktion' => [
            'exclude' => true,
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql' => "varchar(255) NOT NULL default ''",
        ],
        'refereeId' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'bitte wählen', 'tl_class' => 'w50 clr'],
            'foreignKey' => 'tl_bsa_referee.nameReverse',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'options_callback' => [tl_bsa_member_group_member_assignment::class, 'getSchiedsrichterNotDeleted'],
            'save_callback' => [
                [tl_bsa_member_group_member_assignment::class, 'validateDuplicate'],
                [tl_bsa_member_group_member_assignment::class, 'saveSchiedsrichter'],
                [BSANewsletter::class, 'saveSchiedsrichterWhileUpdateGruppenmitglied'],
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];

/**
 * Class tl_bsa_member_group_member_assignment.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_member_group_member_assignment extends Backend
{
    private $objMemberGroup;
    private $addressbookSync;

    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
        $this->import(AddressbookSynchronizer::class);

        // Die Gruppe ermiteln und speichern.
        $this->objMemberGroup = MemberGroupModel::findByPk(empty(Input::get('act')) ? Input::get('id') : CURRENT_ID);

        if (!isset($this->objMemberGroup)) {
            throw new Exception('Die Gruppe konnte nicht ermittelt werden.');
        }
    }

    /**
     * onload functionality.
     *
     * @param DataContainer|null $dc Data Container object or null
     */
    public function onLoad(DataContainer $dc): void
    {
        if ('halbautomatik' === Input::get('key')) {
            $somethingChanged = BSAMemberGroup::updateHalbautomaticGroup($this->objMemberGroup);

            if ($somethingChanged) {
                $this->executeSorting($dc);
            }
            $this->redirect(str_replace('&key=halbautomatik', '', Environment::get('request')));
        }

        if ('sortAsc' === Input::get('key')) {
            $this->executeSorting($dc);
            $this->redirect(str_replace('&key=sortAsc', '', Environment::get('request')));
        }
    }

    /**
     * Return the halbautomatic button.
     *
     * @param string|null $href          Button href
     * @param string      $label         Label
     * @param string      $title         Title
     * @param string      $class         Class
     * @param string      $attributes    HTML attributes
     * @param string      $table         Table
     * @param array       $rootRecordIds IDs of all root records
     *
     * @return string HTML for the button
     */
    public function halbautomaticButton($href, $label, $title, $class, $attributes, $table, $rootRecordIds)
    {
        return BSAMemberGroup::isHalbautomatic($this->objMemberGroup->__get('automatik')) ? '<a href="'.$this->addToUrl($href).'" class="'.$class.'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.$label.'</a> ' : '';
    }

    /**
     * Add the type of content element.
     *
     * @param DataContainer|null $dc Data Container object or null
     *
     * @return array
     */
    public function getSchiedsrichterNotDeleted(DataContainer $dc)
    {
        $objSR = RefereeModel::findBy('deleted', '', ['order' => 'nameReverse']);

        $options = [];

        if (null !== $objSR) {
            while ($objSR->next()) {
                $options[$objSR->id] = $objSR->__get('nameReverse');
            }
        }

        return $options;
    }

    /**
     * Add the type of content element.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function deleteGruppenmitglied(DataContainer $dc, $undoId): void
    {
        BSAMemberGroup::setGroupsToMember($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid, 0);
        SRHistory::insert($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
        AddressbookSynchronizer::executeSubmitSchiedsrichter($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid);
    }

    /**
     * Add the type of content element.
     *
     * @param DataContainer $dc Data Container object
     */
    public function submitGruppenmitglied(DataContainer $dc): void
    {
        if (isset($this->addressbookSync) && !empty($this->addressbookSync) && is_array($this->addressbookSync)) {
            foreach ($this->addressbookSync as $srId) {
                if ($srId > 0) {
                    AddressbookSynchronizer::executeSubmitSchiedsrichter($srId);
                }
            }
            $this->addressbookSync = null;
        }
    }

    /**
     * Add the type of content element.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function validateDuplicate($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->refereeId) {
            $objSR = RefereeModel::findReferee($varValue);

            if (isset($objSR) && $objSR->__get('deleted')) {
                throw new Exception('Gelöschte Schiedsrichter dürfen nicht aufgenommen werden.');
            }

            $ids = $this->Database->prepare('SELECT id FROM tl_bsa_member_group_member_assignment WHERE id!=? AND pid=? AND refereeId=?')
                ->execute($dc->id, $dc->__get('activeRecord')->pid, $varValue)
                ->fetchEach('id')
            ;

            if (!empty($ids) && is_array($ids)) {
                throw new Exception('Der Schiedsrichter ist bereits in dieser Gruppe, doppelte Einträge sind nicht erlaubt.');
            }
        }

        return $varValue;
    }

    /**
     * Add the type of content element.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function saveSchiedsrichter($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->refereeId) {
            BSAMemberGroup::setGroupsToMember($varValue, 0, $dc->__get('activeRecord')->pid);
            BSAMemberGroup::setGroupsToMember($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid, 0);

            SRHistory::insert($varValue, $dc->__get('activeRecord')->pid, ['Kader/Gruppe', 'ADD'], 'Der Schiedsrichter %s wurde in der Gruppe "%s" aufgenommen.', __METHOD__);

            if (0 !== $dc->__get('activeRecord')->refereeId) {
                SRHistory::insert($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }

            $this->addressbookSync = [$varValue, $dc->__get('activeRecord')->refereeId];
        }

        return $varValue;
    }

    /**
     * sorting.
     *
     * @param DataContainer $dc
     */
    private function executeSorting($dc): void
    {
        $arrID = $this->Database->prepare('SELECT gm.id FROM tl_bsa_member_group_member_assignment AS gm, tl_bsa_referee AS sr WHERE gm.refereeId=sr.id AND gm.pid=? ORDER BY sr.nameReverse ASC')
            ->execute($dc->id)
            ->fetchEach('id')
        ;

        foreach ($arrID as $index => $id) {
            $sorting = ($index + 1) * 128;
            $this->Database->prepare('UPDATE tl_bsa_member_group_member_assignment SET sorting=? WHERE id=?')
                ->execute($sorting, $id)
            ;
        }
        Message::addConfirmation('Die Sortierung wurde erfolgreich ausgeführt.');
    }
}
