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
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSchiedsrichterModel;

/*
 * Change palette
 */
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default'] .= ';{bsa_schiedsrichter_legend},refereeId,firstname,lastname,groups';

/*
 * Change keys
 */
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['config']['sql']['keys']['pid,email'] = 'index';
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['config']['sql']['keys']['pid,email,refereeId'] = 'unique';

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['refereeId'] = [
    'inputType' => 'select',
    'eval' => ['disabled' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein BSA-Schiedsrichter', 'tl_class' => 'clr'],
    'foreignKey' => 'tl_bsa_schiedsrichter.name_rev',
    'options_callback' => ['tl_bsa_newsletter_recipients', 'getSchiedsrichterNotDeleted'],
    'sql' => 'int(10) unsigned NULL',
];
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['groups'] = [
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_member_group.name',
    'eval' => ['disabled' => true, 'multiple' => true, 'tl_class' => 'clr'],
    'sql' => 'blob NULL',
    'relation' => ['type' => 'hasMany', 'load' => 'lazy'],
];
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['lastname'] = [
    'inputType' => 'text',
    'search' => true,
    'eval' => ['disabled' => true, 'maxlength' => 50, 'tl_class' => 'w50'],
    'sql' => "varchar(50) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['firstname'] = [
    'inputType' => 'text',
    'search' => true,
    'eval' => ['disabled' => true, 'maxlength' => 50, 'tl_class' => 'w50'],
    'sql' => "varchar(50) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['salutationPersonal'] = [
    'sql' => "varchar(25) NOT NULL default ''",
];

/**
 * Class tl_bsa_newsletter_recipients.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_newsletter_recipients extends Backend
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
     * Add the type of content element.
     *
     * @param array $arrRow
     *
     * @return string
     */
    public function getList($arrRow)
    {
        $objSR = BsaSchiedsrichterModel::findSchiedsrichter($arrRow['refereeId']);

        if (isset($objSR)) {
            return $objSR->__get('name_rev');
        }

        return '???';
    }

    /**
     * Add the type of content element.
     *
     * @param array
     *
     * @return string
     */
    public function getSchiedsrichterNotDeleted()
    {
        $objSR = BsaSchiedsrichterModel::findBy('deleted', '', ['order' => 'name_rev']);

        $options = [];

        if (null !== $objSR) {
            while ($objSR->next()) {
                if (strlen($objSR->__get('email'))) {
                    $options[$objSR->id] = $objSR->__get('name_rev');
                }
            }
        }

        return $options;
    }
}
