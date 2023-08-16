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
 * Reads and writes event participiants.
 *
 * @property string|int $id
 * @property string|int $pid
 * @property string|int $tstamp
 * @property string|int $refereeId
 * @property string     $refereeNameReverse
 * @property string     $type
 *
 * @method static EventParticipiantModel|null findById($id, array $opt=array())
 * @method static EventParticipiantModel|null findByPk($id, array $opt=array())
 * @method static EventParticipiantModel|null findByIdOrAlias($val, array $opt=array())
 * @method static EventParticipiantModel|null findOneBy($col, $val, array $opt=array())
 * @method static EventParticipiantModel|null findOneByPid($val, array $opt=array())
 * @method static EventParticipiantModel|null findOneByTstamp($val, array $opt=array())
 * @method static EventParticipiantModel|null findOneByRefereeId($col, $val, array $opt=array())
 * @method static EventParticipiantModel|null findOneByRefereeNameReverse($col, $val, array $opt=array())
 * @method static EventParticipiantModel|null findOneByType($col, $val, array $opt=array())
 *                                                                                                                                        -
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findByPid($val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findByRefereeId($val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findByRefereeNameReverse($val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findByType($val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<EventParticipiantModel>|EventParticipiantModel|null findAll(array $opt=array())
 *                                                                                                                                        -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByRefereeId($val, array $opt=array())
 * @method static integer countByRefereeNameReverse($val, array $opt=array())
 * @method static integer countByType($val, array $opt=array())
 */
class EventParticipiantModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_event_participiant';
}

class_alias(EventParticipiantModel::class, 'EventParticipiantModel');
