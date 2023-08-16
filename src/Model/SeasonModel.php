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
use Contao\Model\Collection;

/**
 * Reads and writes seasons.
 *
 * @property string|int      $id
 * @property string|int      $tstamp
 * @property string          $name
 * @property string|bool     $active
 * @property string|int|null $startDate
 * @property string|int|null $endDate
 *
 * @method static SeasonModel|null findById($id, array $opt=array())
 * @method static SeasonModel|null findByPk($id, array $opt=array())
 * @method static SeasonModel|null findByIdOrAlias($val, array $opt=array())
 * @method static SeasonModel|null findOneBy($col, $val, array $opt=array())
 * @method static SeasonModel|null findOneByTstamp($col, $val, array $opt=array())
 * @method static SeasonModel|null findOneByName($col, $val, array $opt=array())
 * @method static SeasonModel|null findOneByActive($col, $val, array $opt=array())
 * @method static SeasonModel|null findOneByStartDate($col, $val, array $opt=array())
 * @method static SeasonModel|null findOneByEndDate($col, $val, array $opt=array())
 *                                                                                                           -
 * @method static Collection|array<SeasonModel>|SeasonModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findByName($val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findByActive($val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findByStartDate($val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findByEndDate($val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<SeasonModel>|SeasonModel|null findAll(array $opt=array())
 *                                                                                                           -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByActive($val, array $opt=array())
 * @method static integer countByStartDate($val, array $opt=array())
 * @method static integer countByEndDate($val, array $opt=array())
 */
class SeasonModel extends Model
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
        try {
            $currentSeason = static::findOneBy('active', true, []);

            return isset($currentSeason) ? $currentSeason->id : null;
        } catch (\Exception $e) {
            return null;
        }
    }
}

class_alias(SeasonModel::class, 'SeasonModel');
