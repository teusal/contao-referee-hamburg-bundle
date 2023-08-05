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
 * Reads and writes group members.
 *
 * @property int $schiedsrichter
 */
class BsaGruppenmitgliederModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_gruppenmitglieder';

    /**
     * Prüft, ob ein Eintrag existiert.
     *
     * @param mixed $groupId Die numerische ID aus tl_member_group
     * @param mixed $srId    Die numerische ID aus tl_bsa_schiedsrichter
     *
     * @return true|false
     */
    public static function exists($groupId, $srId)
    {
        $counted = Database::getInstance()->execute('SELECT id FROM tl_bsa_gruppenmitglieder WHERE pid='.$groupId.' AND schiedsrichter='.$srId)
            ->numRows;

        return $counted > 0;
    }
}
