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
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['default'] = BackendUser::getInstance()->email;
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['eval']['mandatory'] = true;

/* field definition */
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['replyToAddress'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'email', 'maxlength' => 128, 'decodeEntities' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(128) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['ccChairman'] = [
    'inputType' => 'checkbox',
    'eval' => [],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['infomailSent'] = [
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
    ->addField('replyToAddress', 'reply_to_legend', PaletteManipulator::POSITION_APPEND)
     // add palette and field for carbon copy options
    ->addLegend('cc_legend', 'reply_to_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('ccChairman', 'cc_legend', PaletteManipulator::POSITION_APPEND)
    // add palette and field for info text settings
    ->applyToPalette('default', 'tl_newsletter')
;
