<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\BackendUser;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

/* field changes */
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['subject']['eval']['tl_class'] = 'lng';
unset($GLOBALS['TL_DCA']['tl_newsletter']['fields']['sender']['load_callback'], $GLOBALS['TL_DCA']['tl_newsletter']['fields']['senderName']['load_callback']);

if (!empty(BackendUser::getInstance()->__get('signatur_html'))) {
    $GLOBALS['TL_DCA']['tl_newsletter']['fields']['content']['default'] = '<p></p>'.BackendUser::getInstance()->__get('signatur_html');
}
// $GLOBALS['TL_DCA']['tl_newsletter']['fields']['sender']['default'] = BackendUser::getInstance()->email;
// $GLOBALS['TL_DCA']['tl_newsletter']['fields']['senderName']['default'] = BackendUser::getInstance()->name.' ('.$GLOBALS['BSA_NAMES'][Config::get('bsa_name')].')';
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['default'] = BackendUser::getInstance()->email;
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['eval']['mandatory'] = true;

/* field definition */
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['reply_to'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'email', 'maxlength' => 128, 'decodeEntities' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(128) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['cc_obmann'] = [
    'inputType' => 'checkbox',
    'eval' => [],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['is_infomail_sent'] = [
    'sql' => "char(1) NOT NULL default ''",
];

/* change default palette, add legend and field */
PaletteManipulator::create()
    // remove some fields
    ->removeField('alias')
    ->removeField('text')
    ->removeField('template')
    ->removeField('sendText')
    ->removeField('externalImages')
     // add palette and field for reply options
    ->addLegend('reply_to_legend', 'sender_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('reply_to', 'reply_to_legend', PaletteManipulator::POSITION_APPEND)
     // add palette and field for carbon copy options
    ->addLegend('cc_legend', 'reply_to_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('cc_obmann', 'cc_legend', PaletteManipulator::POSITION_APPEND)
    // add palette and field for info text settings
    ->applyToPalette('default', 'tl_newsletter')
;
