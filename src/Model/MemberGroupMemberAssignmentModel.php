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

/**
 * Reads and writes member to member group assignments.
 *
 * @property int $refereeId
 */
class MemberGroupAssignmentMemberModel extends Model
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
