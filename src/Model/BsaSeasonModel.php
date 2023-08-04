<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Model;

use Contao\Model;

/**
 * Reads and writes seasons.
 *
 * @property string|int $id
 * @property string     $name
 * @property string|int $startDate
 * @property string|int $endDate
 */
class BsaSeasonModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_season';

    /**
     * provides the id of the current season.
     *
     * @return int|null
     */
    public static function getCurrentSeasonId()
    {
        $currentSeason = static::findOneBy('active', true, []);

        return isset($currentSeason) ? $currentSeason->id : null;
    }
}
