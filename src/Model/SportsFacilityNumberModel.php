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
 * Reads and writes sports facility numbers.
 *
 * @property string|int $id
 * @property string|int $pid
 * @property string|int $tstamp
 * @property string     $dfbnetNumber
 *
 * @method static SportsFacilityNumberModel|null findById($id, array $opt=array())
 * @method static SportsFacilityNumberModel|null findByPk($id, array $opt=array())
 * @method static SportsFacilityNumberModel|null findByIdOrAlias($val, array $opt=array())
 * @method static SportsFacilityNumberModel|null findOneBy($col, $val, array $opt=array())
 * @method static SportsFacilityNumberModel|null findOneByPid($val, array $opt=array())
 * @method static SportsFacilityNumberModel|null findOneByTstamp($val, array $opt=array())
 * @method static SportsFacilityNumberModel|null findOneByDfbnetNumber($val, array $opt=array())
 *                                                                                                                                        -
 * @method static Collection|array<SportsFacilityNumberModel>|SportsFacilityNumberModel|null findByPid($val, array $opt=array())
 * @method static Collection|array<SportsFacilityNumberModel>|SportsFacilityNumberModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<SportsFacilityNumberModel>|SportsFacilityNumberModel|null findByDfbnetNumber($val, array $opt=array())
 * @method static Collection|array<SportsFacilityNumberModel>|SportsFacilityNumberModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<SportsFacilityNumberModel>|SportsFacilityNumberModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<SportsFacilityNumberModel>|SportsFacilityNumberModel|null findAll(array $opt=array())
 *                                                                                                                                        -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer DfbnetNumber($val, array $opt=array())
 */
class SportsFacilityNumberModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_sports_facility_number';
}

class_alias(SportsFacilityNumberModel::class, 'SportsFacilityNumberModel');
