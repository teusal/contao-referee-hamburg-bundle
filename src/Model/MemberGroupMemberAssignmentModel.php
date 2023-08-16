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
 * @method static MemberGroupMemberAssignmentModel|null findById($id, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findByPk($id, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findByIdOrAlias($val, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findOneBy($col, $val, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findOneByPid($val, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findOneBySorting($val, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findOneByTstamp($val, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findOneByRefereeId($col, $val, array $opt=array())
 * @method static MemberGroupMemberAssignmentModel|null findOneByFunction($col, $val, array $opt=array())
 *                                                                                                                                                     -
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findByPid($val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findBySorting($val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findByRefereeId($val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findByFunction($val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<MemberGroupMemberAssignmentModel>|MemberGroupMemberAssignmentModel|null findAll(array $opt=array())
 *                                                                                                                                                     -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countBySorting($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByRefereeId($val, array $opt=array())
 * @method static integer countByFunction($val, array $opt=array())
 */
class MemberGroupMemberAssignmentModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_member_group_member_assignment';

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
        $counted = Database::getInstance()->execute('SELECT id FROM tl_bsa_member_group_member_assignment WHERE pid='.$groupId.' AND refereeId='.$srId)
            ->numRows;

        return $counted > 0;
    }
}

class_alias(MemberGroupMemberAssignmentModel::class, 'MemberGroupMemberAssignmentModel');
