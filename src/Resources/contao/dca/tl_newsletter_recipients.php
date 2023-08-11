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
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/*
 * Change palette
 */
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['palettes']['default'] .= ';{bsa_schiedsrichter_legend},refereeId,groups';

/*
 * Change fields
 */
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['email']['eval']['rgxp'] = 'friendly';

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_newsletter_recipients']['fields']['refereeId'] = [
    'inputType' => 'select',
    'eval' => ['disabled' => true, 'multiple' => false, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein BSA-Schiedsrichter', 'tl_class' => 'clr'],
    'foreignKey' => 'tl_bsa_referee.nameReverse',
    'options_callback' => [tl_bsa_newsletter_recipients::class, 'getSchiedsrichterNotDeleted'],
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
        $objSR = RefereeModel::findReferee($arrRow['refereeId']);

        if (isset($objSR)) {
            return $objSR->__get('nameReverse');
        }

        return '???';
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
                if (strlen($objSR->__get('email'))) {
                    $options[$objSR->id] = $objSR->__get('nameReverse');
                }
            }
        }

        return $options;
    }
}
