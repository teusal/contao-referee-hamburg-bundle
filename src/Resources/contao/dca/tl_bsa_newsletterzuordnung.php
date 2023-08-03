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
use Teusal\ContaoRefereeHamburgBundle\Library\BSANewsletter;

$GLOBALS['TL_DCA']['tl_bsa_newsletterzuordnung'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => false,
        'ptable' => 'tl_member_group',
        'notSortable' => true,
        'onsubmit_callback' => [
            [tl_bsa_newsletterzuordnung::class, 'doSorting'],
        ],
        'ondelete_callback' => [
            [BSANewsletter::class, 'deleteNewsletterzuordnung'],
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
            'headerFields' => ['name'],
        ],
        'label' => [
            'fields' => ['newsletter_channel:tl_newsletter_channel.title'],
        ],
        'global_operations' => [
            'all' => [
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"',
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
        'default' => 'newsletter_channel',
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
        'newsletter_channel' => [
            'inputType' => 'select',
            'eval' => ['mandatory' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'bitte wählen'],
            'foreignKey' => 'tl_newsletter_channel.title',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'save_callback' => [
                [tl_bsa_newsletterzuordnung::class, 'validateDuplicate'],
                [BSANewsletter::class, 'saveNewsletterzuordnung'],
            ],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
    ],
];

/**
 * Class tl_bsa_newsletterzuordnung.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_newsletterzuordnung extends Backend
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
     * Prüft auf doppelte Einträge von Newslettern innerhalb einer Gruppe.
     */
    public function validateDuplicate($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->newsletter_channel) {
            $ids = $this->Database->prepare('SELECT id FROM tl_bsa_newsletterzuordnung WHERE id!=? AND pid=? AND newsletter_channel=?')
                ->execute($dc->id, $dc->__get('activeRecord')->pid, $varValue)
                ->fetchEach('id')
            ;

            if (!empty($ids) && is_array($ids)) {
                throw new Exception('Der Newsletter ist dieser Gruppe bereits zugeordnet, doppelte Einträge sind nicht erlaubt.');
            }
        }

        return $varValue;
    }

    /**
     * Sortiert die Datensätze dieser Gruppe alphabethisch anhand des Newsletters.
     *
     * @param DataContainer $dc Data Container object
     */
    public function doSorting(DataContainer $dc): void
    {
        $arrIds = $this->Database->prepare('SELECT z.id FROM tl_bsa_newsletterzuordnung AS z, tl_newsletter_channel AS c WHERE c.id=z.newsletter_channel AND z.pid=? ORDER BY c.title')
            ->execute($dc->__get('activeRecord')->pid)
            ->fetchEach('id')
        ;

        for ($i = 0; $i < count($arrIds); ++$i) {
            $this->Database->prepare('UPDATE tl_bsa_newsletterzuordnung SET sorting=? WHERE id=?')
                ->execute($i, $arrIds[$i])
            ;
        }
    }
}
