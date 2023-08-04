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
use Contao\DataContainer;
use Contao\DC_Table;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSchiedsrichterModel;

$GLOBALS['TL_DCA']['tl_bsa_schiedsrichter_historie'] = [
    // Config
    'config' => [
        'dataContainer' => DC_Table::class,
        'closed' => true,
        'notEditable' => true,
        'notDeletable' => true,
        'notCopyable' => true,
        'notCreatable' => true,
        'onload_callback' => [
            [tl_bsa_schiedsrichter_historie::class, 'replacePlaceholders'],
        ],
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'schiedsrichter,tstamp' => 'index',
                'action_group,action,tstamp' => 'index',
            ],
        ],
    ],

    // List
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTABLE,
            'fields' => ['tstamp DESC', 'id DESC'],
            'panelLayout' => 'filter;sort,search,limit',
        ],
        'label' => [
            'fields' => ['tstamp', 'text'],
            'format' => '<span style="color:#b3b3b3;padding-right:3px">[%s]</span><br/>%s',
            'label_callback' => [tl_bsa_schiedsrichter_historie::class, 'colorize'],
        ],
        'global_operations' => [
        ],
        'operations' => [
            'show' => [
                'href' => 'act=show',
                'icon' => 'show.gif',
            ],
        ],
    ],

    // Fields
    'fields' => [
        'id' => [
            'sql' => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_DAY_DESC,
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'schiedsrichter' => [
            'filter' => true,
            'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
            'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'reference_id' => [
            'sql' => 'int(10) unsigned NULL',
        ],
        'action_group' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'action' => [
            'filter' => true,
            'sorting' => true,
            'flag' => DataContainer::SORT_ASC,
            'sql' => "varchar(32) NOT NULL default ''",
        ],
        'text' => [
            'search' => true,
            'sql' => 'text NULL',
        ],
        'username' => [
            'filter' => true,
            'sql' => "varchar(64) NOT NULL default ''",
        ],
        'func' => [
            'sql' => "varchar(255) NOT NULL default ''",
        ],
    ],
];

/**
 * Class tl_bsa_schiedsrichter_historie.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_schiedsrichter_historie extends Backend
{
    /**
     * Colorize the log entries depending on their category.
     *
     * @param DataContainer|null $dc Data Container object or null
     */
    public function replacePlaceholders(DataContainer $dc): void
    {
        $rows = $this->Database->prepare('SELECT * FROM tl_bsa_schiedsrichter_historie WHERE text LIKE ?')
            ->execute('%\%s%')
            ->fetchAllAssoc()
        ;

        foreach ($rows as $row) {
            $objSR = BsaSchiedsrichterModel::findByPk($row['schiedsrichter']);

            if (isset($objSR)) {
                $srName = $objSR->__get('vorname').' '.$objSR->__get('nachname');
            } else {
                $srName = 'unbekannt (ID='.$row['schiedsrichter'].')';
            }

            if (!$objSR->verein) {
                $arrSearch = ['Der Schiedsrichter', 'des Schiedsrichters'];
                $arrReplace = ['Die Person', 'der Person'];
                $row['text'] = str_replace($arrSearch, $arrReplace, $row['text']);
            }

            if ($row['reference_id']) {
                switch ($row['action_group']) {
                    case 'E-Mail-Verteiler':
                    case 'E-Mail':
                        $res = $this->Database->prepare('SELECT title FROM tl_newsletter_channel WHERE id=?')->execute($row['reference_id']);

                        if ($res->next()) {
                            $referenceName = $res->__get('title');
                        }
                        break;

                    case 'Login':
                        $res = $this->Database->prepare('SELECT username FROM tl_member WHERE id=?')->execute($row['reference_id']);

                        if ($res->next()) {
                            $referenceName = $res->__get('username');
                        }
                        break;

                    case 'Kader/Gruppe':
                        $res = $this->Database->prepare('SELECT name FROM tl_member_group WHERE id=?')->execute($row['reference_id']);

                        if ($res->next()) {
                            $referenceName = $res->__get('name');
                        }
                        break;
                }
            }
            $this->Database->prepare('UPDATE tl_bsa_schiedsrichter_historie SET text=? WHERE id=?')
                ->execute(sprintf($row['text'], $srName, $referenceName), $row['id'])
            ;
        }
    }

    /**
     * Colorize the log entries depending on their category.
     *
     * @param array         $row     Record data
     * @param string        $label   Current label
     * @param DataContainer $dc      Data Container object
     * @param array         $columns with existing labels
     *
     * @return string
     */
    public function colorize($row, $label, DataContainer $dc, $columns)
    {
        switch ($row['action']) {
            case 'ADD':
                $label = preg_replace('@^(.*</span><br/>)(.*)$@U', '$1 <span class="tl_green">$2</span>', $label);
                break;

            case 'EDIT':
            case 'INFO':
                $label = preg_replace('@^(.*</span><br/>)(.*)$@U', '$1 <span class="tl_blue">$2</span>', $label);
                break;

            case 'REMOVE':
                $label = preg_replace('@^(.*</span><br/>)(.*)$@U', '$1 <span class="tl_red">$2</span>', $label);
                break;
        }

        return '<div title="'.$row['text'].'">'.$label.'</div>';
    }
}
