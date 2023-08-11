<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Input;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;

if ('logins' === Input::get('do')) {
    /*
    * Change config
    */
    $GLOBALS['TL_DCA']['tl_member']['config']['notCreatable'] = true;
    $GLOBALS['TL_DCA']['tl_member']['config']['notCopyable'] = true;
    $GLOBALS['TL_DCA']['tl_member']['config']['notDeletable'] = true;
    $GLOBALS['TL_DCA']['tl_member']['edit']['buttons_callback'] = [[tl_bsa_member::class, 'disableButtons']];

    /*
     * Change list
     */
    unset($GLOBALS['TL_DCA']['tl_member']['list']['operations']['copy'],
          $GLOBALS['TL_DCA']['tl_member']['list']['operations']['delete'],
          $GLOBALS['TL_DCA']['tl_member']['list']['operations']['toggle']);

    $GLOBALS['TL_DCA']['tl_member']['list']['global_operations'] = [
        'createNeededLogins' => [
            'href' => 'key=createNeeded',
            'class' => '',
            'attributes' => 'onclick="Backend.getScrollOffset()" style="padding:2px 0 3px 20px;background:url(\'/system/themes/default/images/modules.gif\') no-repeat left center;"',
        ],
    ];
    $GLOBALS['TL_DCA']['tl_member']['list']['sorting']['filter'] = [['refereeId!=?', '0']];

    /*
     * Change palette
     */
    $GLOBALS['TL_DCA']['tl_member']['palettes']['default'] = '{personal_legend},firstname,lastname,email,refereeId;{groups_legend:hide},groups;{newsletter_legend:hide},newsletter;{login_legend},login';

    /*
     * Change fields
     */
    $GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['tl_class'] = 'w50 clr';
    // change evals
    $GLOBALS['TL_DCA']['tl_member']['fields']['firstname']['eval']['readonly'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['lastname']['eval']['readonly'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['readonly'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['groups']['eval']['disabled'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['newsletter']['eval']['disabled'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['login']['eval']['disabled'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['username']['eval']['readonly'] = true;
    $GLOBALS['TL_DCA']['tl_member']['fields']['password']['eval']['readonly'] = true;
    // change filters
    $GLOBALS['TL_DCA']['tl_member']['fields']['country']['filter'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['city']['filter'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['language']['filter'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['login']['filter'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['newsletter']['filter'] = true;
    // change sortings
    $GLOBALS['TL_DCA']['tl_member']['fields']['company']['sorting'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['country']['sorting'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['city']['sorting'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['state']['sorting'] = false;
    // change searches
    $GLOBALS['TL_DCA']['tl_member']['fields']['fax']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['company']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['mobile']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['city']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['postal']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['street']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['phone']['search'] = false;
    $GLOBALS['TL_DCA']['tl_member']['fields']['website']['search'] = false;
} elseif ('external_logins' === Input::get('do')) {
    /*
     * Change list
     */
    $GLOBALS['TL_DCA']['tl_member']['list']['sorting']['filter'] = [['refereeId IS NULL']];
}

/*
 * Change config
 */
$GLOBALS['TL_DCA']['tl_member']['config']['enableVersioning'] = false;

/*
 * Change fields
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['disable']['save_callback'][] = [SRHistory::class, 'insertByToggleMember'];
$GLOBALS['TL_DCA']['tl_member']['fields']['email']['eval']['rgxp'] = 'friendly';

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_member']['fields']['refereeId'] = [
    'inputType' => 'select',
    'eval' => ['multiple' => false, 'disabled' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'kein Schiedsrichter', 'unique' => true, 'tl_class' => 'w50 clr'],
    'foreignKey' => 'tl_bsa_referee.nameReverse',
    'relation' => ['type' => 'belongsTo', 'load' => 'eager'],
    'sql' => 'int(10) unsigned NULL',
];

/*
 * change callbacks
 */
$GLOBALS['TL_DCA']['tl_member']['config']['ondelete_callback'][] = [SRHistory::class, 'insertByDeleteMember'];
// disable newsletter synchronization by contao newsletter-bundle
unset($GLOBALS['TL_DCA']['tl_member']['config']['onload_callback']['Newsletter'],
      $GLOBALS['TL_DCA']['tl_member']['fields']['disable']['save_callback']['Newsletter'],
      $GLOBALS['TL_DCA']['tl_member']['fields']['newsletter']['save_callback']['Newsletter']);

/**
 * Class tl_bsa_member.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_member extends Backend
{
    /**
     * Adding the send testmail button.
     *
     * @param array         $arrButtons Array of strings
     * @param DataContainer $dc         Data Container object
     *
     * @return array
     */
    public function disableButtons($arrButtons, DataContainer $dc)
    {
        $arrButtons['save'] = preg_replace('/>/', ' disabled>', $arrButtons['save'], 1);
        $arrButtons['saveNclose'] = preg_replace('/>/', ' disabled>', $arrButtons['saveNclose'], 1);

        return $arrButtons;
    }
}
