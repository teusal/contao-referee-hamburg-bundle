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
 * Read and write club chairman data.
 *
 * @property string|int      $id
 * @property string|int      $tstamp
 * @property string|int      $clubId
 * @property string|int|null $chairman
 * @property string|int|null $viceChairman1
 * @property string|int|null $viceChairman2
 *
 * @method static ClubChairmanModel|null findById($id, array $opt=array())
 * @method static ClubChairmanModel|null findByPk($id, array $opt=array())
 * @method static ClubChairmanModel|null findByIdOrAlias($val, array $opt=array())
 * @method static ClubChairmanModel|null findOneBy($col, $val, array $opt=array())
 * @method static ClubChairmanModel|null findOneByTstamp($val, array $opt=array())
 * @method static ClubChairmanModel|null findOneByClubId($val, array $opt=array())
 * @method static ClubChairmanModel|null findOneByChairman($val, array $opt=array())
 * @method static ClubChairmanModel|null findOneByViceChairman1($val, array $opt=array())
 * @method static ClubChairmanModel|null findOneByViceChairman2($val, array $opt=array())
 *                                                                                                                         -
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findByClubId($val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findByChairman($val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findByViceChairman1($val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findByViceChairman2($val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<ClubChairmanModel>|ClubChairmanModel|null findAll(array $opt=array())
 *                                                                                                                         -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByClubId($val, array $opt=array())
 * @method static integer countByChairman($val, array $opt=array())
 * @method static integer countByViceChairman1($val, array $opt=array())
 * @method static integer countByViceChairman2($val, array $opt=array())
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
        $objReferee = RefereeModel::findReferee($refereeId);

        if (!isset($objReferee) || $objReferee->deleted) {
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
     * @return array<string> an array with all email addresses
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

class_alias(ClubChairmanModel::class, 'ClubChairmanModel');
