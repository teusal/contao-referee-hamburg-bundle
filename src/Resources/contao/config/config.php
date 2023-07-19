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

 /*
  * BACK END MENU STRUKTUR
  */
 ArrayUtil::arrayInsert($GLOBALS['BE_MOD'], 0, [
     'bsa' => [],
 ]);

/*
 * BACK END MODULES
 */
$GLOBALS['BE_MOD']['bsa']['saison'] = [
    'tables' => ['tl_bsa_season'],
];
