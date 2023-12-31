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
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
        ],
        'operations' => [
            'edit' => [
                'label' => &$GLOBALS['TL_LANG']['tl_bsa_season']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ],
            'toggle' => [
                'label' => &$GLOBALS['TL_LANG']['tl_bsa_season']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleAktiv(this)"',
                'button_callback' => ['tl_bsa_season', 'toggleIcon'],
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
                ['tl_bsa_season', 'validateDate'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'endDate' => [
            'inputType' => 'text',
            'eval' => ['rgxp' => 'date', 'mandatory' => true, 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'],
            'save_callback' => [
                ['tl_bsa_season', 'validateDate'],
                ['tl_bsa_season', 'validateEndDate'],
            ],
            'sql' => 'int(10) unsigned NULL',
        ],
        'active' => [
            'inputType' => 'checkbox',
            'filter' => true,
            'toggle' => true,
            'eval' => ['fallback' => true, 'tl_class' => 'clr'],
            'sql' => "char(1) NOT NULL default ''",
        ],
    ],
];

/**
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
     * Test: The date must not be in any other season.
     *
     * @param mixed $varValue
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function validateDate($varValue, DataContainer $dc)
    {
        $objSaison = BsaSeasonModel::findOneBy(['id!=? AND startDate<=? AND endDate>?'], [(int) ($dc->id), $varValue, $varValue], []);

        if (isset($objSaison)) {
            $name = $objSaison->name;
            $start = Date::parse(Config::get('dateFormat'), $objSaison->startDate);
            $end = Date::parse(Config::get('dateFormat'), $objSaison->endDate - 1);

            throw new Exception('Datum liegt in der Saison '.$name.' ('.$start.' - '.$end.')');
        }

        return $varValue;
    }

    /**
     * Check: The end date must be smaller than the start date.
     *
     * @param mixed $varValue
     *
     * @throws Exception
     *
     * @return mixed
     */
    public function validateEndDate($varValue, DataContainer $dc)
    {
        if ($varValue <= $dc->activeRecord->startDate) {
            throw new Exception('Enddatum muss nach dem Startdatum sein');
        }

        return $varValue;
    }

    /**
     * Return the "toggle visibility" button.
     *
     * @param array  $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid')) && !$row['active']) {
            $this->toggleActive(Input::get('tid'));
            $this->redirect($this->getReferer());
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
