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
 * Reads and writes clubs.
 *
 * @property string|int  $id
 * @property string|int  $tstamp
 * @property string      $name
 * @property string      $nameShort
 * @property string      $number
 * @property string|null $nameAddition
 * @property string|null $street
 * @property string|null $postal
 * @property string|null $city
 * @property string|null $phone1
 * @property string|null $phone2
 * @property string|null $fax
 * @property string|null $email
 * @property string|null $image
 * @property string|null $homepage1
 * @property string|null $homepage2
 * @property string|bool $published
 * @property string|int  $refereesActiveQuantity
 * @property string|int  $refereesPassiveQuantity
 *
 * @method static ClubModel|null findById($id, array $opt=array())
 * @method static ClubModel|null findByPk($id, array $opt=array())
 * @method static ClubModel|null findByIdOrAlias($val, array $opt=array())
 * @method static ClubModel|null findOneBy($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByTstamp($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByName($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByNameShort($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByNumber($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByNameAddition($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByStreet($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByPostal($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByCity($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByPhone1($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByPhone2($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByFax($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByEmail($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByImage($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByHomepage1($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByHomepage2($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByPublished($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByRefereesActiveQuantity($col, $val, array $opt=array())
 * @method static ClubModel|null findOneByRefereesPassiveQuantity($col, $val, array $opt=array())
 *                                                                                                                   -
 * @method static Collection|array<ClubModel>|ClubModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByName($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByNameShort($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByNumber($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByNameAddition($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByStreet($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByPostal($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByCity($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByPhone1($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByPhone2($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByFax($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByEmail($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByImage($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByHomepage1($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByHomepage2($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByPublished($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByRefereesActiveQuantity($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findByRefereesPassiveQuantity($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<ClubModel>|ClubModel|null findAll(array $opt=array())
 *                                                                                                                   -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByNameShort($val, array $opt=array())
 * @method static integer countByNumber($val, array $opt=array())
 * @method static integer countByNameAddition($val, array $opt=array())
 * @method static integer countByStreet($val, array $opt=array())
 * @method static integer countByPostal($val, array $opt=array())
 * @method static integer countByCity($val, array $opt=array())
 * @method static integer countByPhone1($val, array $opt=array())
 * @method static integer countByPhone2($val, array $opt=array())
 * @method static integer countByFax($val, array $opt=array())
 * @method static integer countByEmail($val, array $opt=array())
 * @method static integer countByImage($val, array $opt=array())
 * @method static integer countByHomepage1($val, array $opt=array())
 * @method static integer countByHomepage2($val, array $opt=array())
 * @method static integer countByPublished($val, array $opt=array())
 * @method static integer countByRefereesActiveQuantity($val, array $opt=array())
 * @method static integer countByRefereesPassiveQuantity($val, array $opt=array())
 */
class ClubModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_club';

    /**
     * Verein anhand der ID finden.
     *
     * @param mixed $vereinId Die numerische ID aus tl_bsa_club
     *
     * @return ClubModel|null Das Model oder null wenn es keinen Verein gibt
     */
    public static function findVerein($vereinId)
    {
        return static::findByPk($vereinId, []);
    }
}

class_alias(ClubModel::class, 'ClubModel');
