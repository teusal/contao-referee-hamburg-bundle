<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DC_File;
use Teusal\ContaoRefereeHamburgBundle\Library\Email\MatchesEmail;

$objEmail = new MatchesEmail();

/*
 * System configuration
 */
$GLOBALS['TL_DCA']['tl_bsa_export_matches_settings'] = [
    // Config
    'config' => [
        'dataContainer' => DC_File::class,
        'closed' => true,
    ],

    // Palettes
    'palettes' => [
        '__selector__' => [],
        'default' => '{ansetzungen_legend_export_legend},ansetzungen_export_folder,ansetzungen_export_filename,ansetzungen_export_delete_days;{ansetzungen_newsletter_legend},ansetzungen_newsletter,ansetzungen_mailer_transport,ansetzungen_subject,ansetzungen_text,ansetzungen_bcc',
    ],

    // Fields
    'fields' => [
        'ansetzungen_export_folder' => [
            'inputType' => 'fileTree',
            'eval' => ['mandatory' => true, 'multiple' => false, 'fieldType' => 'radio', 'files' => false],
        ],

        'ansetzungen_export_filename' => [
            'inputType' => 'text',
            'eval' => ['decodeEntities' => true, 'mandatory' => true, 'eval' => 'alnum', 'tl_class' => 'w50 clr'],
        ],

        'ansetzungen_export_delete_days' => [
            'inputType' => 'text',
            'default' => '30',
            'eval' => ['mandatory' => true, 'rgxp' => 'digit', 'tl_class' => 'w50 clr'],
        ],

        'ansetzungen_newsletter' => [
            'inputType' => 'select',
            'options_callback' => ['Newsletter', 'getNewsletters'],
            'eval' => ['mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => 'bitte Newsletter wählen', 'tl_class' => 'w50 clr'],
        ],

        'ansetzungen_mailer_transport' => $objEmail->getMailerTransportField(),
        'ansetzungen_subject' => $objEmail->getSubjectField(),
        'ansetzungen_text' => $objEmail->getTextField(),
        'ansetzungen_bcc' => $objEmail->getBccField(),
    ],
];
