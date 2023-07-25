<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

uksort(
    $GLOBALS['BE_MOD']['content'],
    static function ($a, $b) {
        $arrSort = ['calendar', 'news', 'dlh_googlemaps', 'article'];
        $aIndex = array_search($a, $arrSort, true);
        $bIndex = array_search($b, $arrSort, true);

        if (false === $aIndex && false === $bIndex) {
            return 0;
        }

        if (false !== $aIndex && false === $bIndex) {
            return -1;
        }

        if (false === $aIndex && false !== $bIndex) {
            return 1;
        }

        if ($aIndex === $bIndex) {
            return 0;
        }

        if ($aIndex < $bIndex) {
            return -1;
        }

        if ($aIndex > $bIndex) {
            return 1;
        }
    }
);
