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
 * Reads and writes events.
 *
 * @property string|int $id
 * @property string|int $tstamp
 * @property string     $eventGroup
 * @property string|int $date
 * @property string|int $seasonId
 * @property string     $type
 * @property string     $name
 *
 * @method static EventModel|null findById($id, array $opt=array())
 * @method static EventModel|null findByPk($id, array $opt=array())
 * @method static EventModel|null findByIdOrAlias($val, array $opt=array())
 * @method static EventModel|null findOneBy($col, $val, array $opt=array())
 * @method static EventModel|null findOneByTstamp($col, $val, array $opt=array())
 * @method static EventModel|null findOneByEventGroup($col, $val, array $opt=array())
 * @method static EventModel|null findOneByDate($col, $val, array $opt=array())
 * @method static EventModel|null findOneBySeasonId($col, $val, array $opt=array())
 * @method static EventModel|null findOneByType($col, $val, array $opt=array())
 * @method static EventModel|null findOneByName($col, $val, array $opt=array())
 *                                                                                                         -
 * @method static Collection|array<EventModel>|EventModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findByEventGroup($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findByDate($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findBySeasonId($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findByType($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findByName($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<EventModel>|EventModel|null findAll(array $opt=array())
 *                                                                                                         -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByEventGroup($val, array $opt=array())
 * @method static integer countByDate($val, array $opt=array())
 * @method static integer countBySeasonId($val, array $opt=array())
 * @method static integer countByType($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 */
class EventModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_event';
}

class_alias(EventModel::class, 'EventModel');
