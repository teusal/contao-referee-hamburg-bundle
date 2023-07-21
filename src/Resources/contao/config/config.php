<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

 use Contao\ArrayUtil;
 use Teusal\ContaoRefereeHamburgBundle\Model\BsaFreigabenModel;
 use Teusal\ContaoRefereeHamburgBundle\Model\BsaSeasonModel;
 use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinModel;
 use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinObmannModel;

 /*
  * BACK END MENU STRUKTUR
  */
 ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 0, [
     'bsa' => [
         'saison' => [
             'tables' => ['tl_bsa_season'],
         ],
     ],
     'bsa_sportplatz' => [],
     'bsa_verein_schiedsrichter' => [
         'verein' => [
             'tables' => ['tl_bsa_verein'],
         ],
         'obmann' => [
             'tables' => ['tl_bsa_verein_obmann'],
         ],
         'vereinslos' => [
             'tables' => ['tl_bsa_schiedsrichter'],
         ],
         'schiedsrichter' => [
             'tables' => ['tl_bsa_schiedsrichter'],
         ],
         'freigaben' => [
             'tables' => ['tl_bsa_freigaben'],
         ],
         'schiedsrichter_historie' => [
             'tables' => ['tl_bsa_schiedsrichter_historie'],
             'icon' => 'system/themes/default/images/show.gif',
         ],
     ],
     'bsa_member' => [],
     'bsa_veranstaltung' => [],
     'bsa_newsletter' => [],
     'bsa_ansetzungen' => [],
     'bsa_beobachtungen' => [],
     'bsa_dfbnet' => [],
     'bsa_anwaerter' => [],
     // 'bsa_regelarbeit_online' => [],
     // 'bsa_regeln' => [],
     // 'bsa_strafen' => [],
 ]);

// Models
// $GLOBALS['TL_MODELS'] = [
//     'tl_bsa_freigaben' => BsaFreigabenModel::class,
//     'tl_bsa_schiedsrichter' => BsaSchiedsrichterModel::class,
//     'tl_bsa_season' => BsaSeasonModel::class,
//     'tl_bsa_verein_obmann' => BsaVereinObmannModel::class,
//     'tl_bsa_verein' => BsaVereinModel::class,
//     'tl_bsa_gruppenmitglieder' => BsaGruppenmitgliederModel::class,
// ];
