<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

/* field definition */
$GLOBALS['TL_DCA']['tl_newsletter_channel']['fields']['schiedsrichter_historie'] = [
    'inputType' => 'checkbox',
    'filter' => true,
    'default' => true,
    'sql' => "char(1) NOT NULL default '1'",
];

/* change default palette, add legend and field */
PaletteManipulator::create()
    ->addLegend('schiedsrichter_historie_legend')
    ->addField('custom_field', 'schiedsrichter_historie', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_newsletter_channel')
;
