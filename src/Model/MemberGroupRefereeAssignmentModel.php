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

use Contao\Database;
use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes member to member group assignments.
 *
 * @property string|int $id
 * @property string|int $pid
 * @property string|int $sorting
 * @property string|int $tstamp
 * @property string|int $refereeId
 * @property string     $function
 *
 * @method static MemberGroupRefereeAssignmentModel|null findById($id, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findByPk($id, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findByIdOrAlias($val, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findOneBy($col, $val, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findOneByPid($val, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findOneBySorting($val, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findOneByTstamp($val, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findOneByRefereeId($col, $val, array $opt=array())
 * @method static MemberGroupRefereeAssignmentModel|null findOneByFunction($col, $val, array $opt=array())
 *                                                                                                                                                       -
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findByPid($val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findBySorting($val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findByRefereeId($val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findByFunction($val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<MemberGroupRefereeAssignmentModel>|MemberGroupRefereeAssignmentModel|null findAll(array $opt=array())
 *                                                                                                                                                       -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countBySorting($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByRefereeId($val, array $opt=array())
 * @method static integer countByFunction($val, array $opt=array())
 */
class MemberGroupRefereeAssignmentModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_member_group_referee_assignment';

    /**
     * Prüft, ob ein Eintrag existiert.
     *
     * @param mixed $groupId Die numerische ID aus tl_member_group
     * @param mixed $srId    Die numerische ID aus tl_bsa_referee
     *
     * @return true|false
     */
    public static function exists($groupId, $srId)
    {
        $counted = Database::getInstance()->execute('SELECT id FROM tl_bsa_member_group_referee_assignment WHERE pid='.$groupId.' AND refereeId='.$srId)
            ->numRows;

        return $counted > 0;
    }
}

class_alias(MemberGroupRefereeAssignmentModel::class, 'MemberGroupRefereeAssignmentModel');
