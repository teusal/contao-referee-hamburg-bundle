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
 * Read and write club chairman data.
 *
 * @method static ClubChairmanModel|null findOneBy($col, $val, array $opt=array())
 *
 * @property int $club
 */
class ClubChairmanModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_club_chairman';

    /**
     * Indicates whether a person is a club chairman.
     *
     * @param int $refereeId The numeric ID from tl_bsa_referee
     *
     * @return true|false true, if it is a club chairman
     */
    public static function isChairman($refereeId)
    {
        $clubId = static::getClubOfChairman($refereeId);

        return null !== $clubId;
    }

    /**
     * Returns the club ID if a person is a club chairman.
     *
     * @param int $refereeId The numeric ID from tl_bsa_referee
     *
     * @return int|null ID of the club or null
     */
    public static function getClubOfChairman($refereeId)
    {
        $objSR = RefereeModel::findReferee($refereeId);

        if (!isset($objSR) || $objSR->__get('deleted')) {
            return null;
        }

        $objVereinObmann = static::findOneBy(['chairman=? OR viceChairman1=? OR viceChairman2=?'], [$refereeId, $refereeId, $refereeId]);

        return isset($objVereinObmann) ? $objVereinObmann->clubId : null;
    }

    /**
     * returns the list of email addresses assigned by a chairman or a vice chairman to the specified club.
     *
     * @param int $clubId the id of the club
     *
     * @return array an array with all email addresses
     */
    public static function getEmailAddressesOfChairmans(int $clubId): array
    {
        return Database::getInstance()
            ->prepare('SELECT tl_bsa_referee.email FROM tl_bsa_referee JOIN tl_bsa_club_chairman ON (tl_bsa_referee.id=tl_bsa_club_chairman.chairman OR tl_bsa_referee.id=tl_bsa_club_chairman.viceChairman1 OR tl_bsa_referee.id=tl_bsa_club_chairman.viceChairman2) WHERE tl_bsa_club_chairman.clubId=? AND tl_bsa_referee.email<>? ORDER BY tl_bsa_referee.email')
            ->execute($clubId, '')
            ->fetchEach('email')
    ;
    }
}
