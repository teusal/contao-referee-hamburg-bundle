<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Config;
use Contao\CoreBundle\DataContainer\PaletteManipulator;

/* change config */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['config']['enableVersioning'] = false;
/* add selector */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['__selector__'][] = 'sendInfomail';
$GLOBALS['TL_DCA']['tl_newsletter_channel']['palettes']['__selector__'][] = 'prependChannelInformation';
/* add subpalettes */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['subpalettes']['sendInfomail'] = 'infomailRecipients';
$GLOBALS['TL_DCA']['tl_newsletter_channel']['subpalettes']['prependChannelInformation'] = 'channelInformationText';

/* field changes */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['mailerTransport']['options_callback'] = ['contao.mailer.available_transports', 'getAllTransportOptions'];
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['sender']['default'] = 'bsa-'.Config::get('bsa_name').'@hfv.de';

/* field definition */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['writeRefereeHistory'] = [
    'inputType' => 'checkbox',
    'filter' => true,
    'default' => true,
    'sql' => "char(1) NOT NULL default '1'",
];
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['sendInfomail'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['infomailRecipients'] = [
    'inputType' => 'text',
    'default' => 'bsa-'.Config::get('bsa_name').'@hfv.de',
    'eval' => ['mandatory' => true, 'rgxp' => 'emails', 'maxlength' => 128, 'decodeEntities' => true, 'tl_class' => 'lng'],
    'sql' => "varchar(255) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['prependChannelInformation'] = [
    'inputType' => 'checkbox',
    'eval' => ['submitOnChange' => true],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['channelInformationText'] = [
    'inputType' => 'text',
    'eval' => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'long'],
    'sql' => "varchar(255) NOT NULL default ''",
];

/* change default palette, add legend and field */
PaletteManipulator::create()
    // remove some fields
    ->removeField('jumpTo')
    ->removeField('template')
    // add palette and field for referee history
    ->addLegend('schiedsrichter_historie_legend', 'title_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('writeRefereeHistory', 'schiedsrichter_historie_legend', PaletteManipulator::POSITION_APPEND)
    // add palette and field for info settings
    ->addLegend('info_legend', 'schiedsrichter_historie_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('sendInfomail', 'info_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('prependChannelInformation', 'info_legend', PaletteManipulator::POSITION_APPEND)
    // apply everything
    ->applyToPalette('default', 'tl_newsletter_channel')
;
