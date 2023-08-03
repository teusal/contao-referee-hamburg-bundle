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
use Contao\Config;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\DataContainer;
use Contao\Date;
use Contao\DC_Table;
use Contao\Environment;
use Contao\Image;
use Contao\Input;
use Contao\StringUtil;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSeasonModel;

$GLOBALS['TL_DCA']['tl_bsa_season'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
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
            'fields' => ['name'],
            'flag' => DataContainer::SORT_ASC,
            'disableGrouping' => true,
        ],
        'label' => [
            'fields' => ['name', 'startDate', 'endDate'],
            'showColumns' => true,
            'label_callback' => [tl_bsa_season::class, 'getLabel'],
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'toggle' => [
                'href' => 'toggle=true',
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleAktiv(this)"',
                'button_callback' => [tl_bsa_season::class, 'toggleIcon'],
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => 'name;startDate,endDate;active',
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
            'sorting' => true,
            'eval' => ['mandatory' => true, 'maxlength' => 20],
            'sql' => "varchar(20) NOT NULL default ''",
        ],
        'startDate' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'save_callback' => [
                [tl_bsa_season::class, 'validateDate'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'endDate' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'save_callback' => [
                [tl_bsa_season::class, 'validateEndDate'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'active' => [
            'inputType' => 'checkbox',
            'filter' => true,
            // TODO refactor to use toggle
            // 'toggle' => true,
            'eval' => ['fallback' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
 * class tl_bsa_season.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_season extends Backend
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
     * converting the star ans end date into an readable string in the configured date format.
     *
     * @param array         $row          Record data
     * @param string        $currentLabel Current label
     * @param DataContainer $dc           Data Container object
     * @param array         $columns      Columns with existing labels
     *
     * @return array
     */
    public function getLabel($row, $currentLabel, DataContainer $dc, $columns)
    {
        $columns[1] = Date::parse(Config::get('dateFormat'), $columns[1]);
        $columns[2] = Date::parse(Config::get('dateFormat'), $columns[2]);

        return $columns;
    }

    /**
     * Test: The date must not be in any other season.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function validateDate($varValue, DataContainer $dc)
    {
        $objSaison = BsaSeasonModel::findOneBy(['id != ? AND startDate <= ? AND endDate > ?'], [(int) ($dc->id), $varValue, $varValue], []);

        if (isset($objSaison)) {
            $name = $objSaison->name;
            $start = Date::parse(Config::get('dateFormat'), $objSaison->startDate);
            $end = Date::parse(Config::get('dateFormat'), $objSaison->endDate - 1);

            throw new Exception('Datum liegt in der Saison '.$name.' ('.$start.' - '.$end.')');
        }

        return $varValue;
    }

    /**
     * Check: The end date must be smaller than the start date and should not be within another season.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function validateEndDate($varValue, DataContainer $dc)
    {
        if ($varValue <= $dc->activeRecord->startDate) {
            throw new Exception('Enddatum muss nach dem Startdatum sein');
        }

        $this->validateDate($varValue - 1, $dc);

        return $varValue;
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
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes, $table, $rootRecordIds, $childRecordIds, $circularReference, $previous, $next, DataContainer $dc): string
    {
        if (Input::get('toggle')) {
            if ($row['active']) {
                throw new Exception('Eine aktive Saison kann nicht aktiviert werden.');
            }

            $this->toggleActive(Input::get('tid'));
            $this->redirect(str_replace('tid='.Input::get('tid').'&', '', str_replace('toggle=true&', '', Environment::get('request'))));
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_bsa_season::active', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid='.$row['id'];

        if ($row['active']) {
            return Image::getHtml($icon, 'AKTIV', 'title="Diese Saison ist die aktuelle Saison."').' ';
        }

        $icon = 'invisible.gif';

        return '<a href="'.$this->addToUrl($href).'" title="'.StringUtil::specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label).'</a> ';
    }

    /**
     * setting a season to active.
     *
     * @param int $intId
     *
     * @throws AccessDeniedException
     */
    private function toggleActive($intId): void
    {
        // Check permissions
        if (!$this->User->isAdmin && !$this->User->hasAccess('tl_bsa_season::active', 'alexf')) {
            throw new AccessDeniedException('Not enough permissions to activate season ID "'.$intId.'"');
        }

        // Update the database
        $this->Database->prepare('UPDATE tl_bsa_season SET tstamp=?, active=(id=?)')
            ->execute(time(), $intId)
        ;
    }
}
