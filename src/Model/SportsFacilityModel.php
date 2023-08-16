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
 * Reads and writes sports falities.
 *
 * @property string|int      $id
 * @property string|int      $tstamp
 * @property string          $name
 * @property string          $type
 * @property string          $street
 * @property string          $postal
 * @property string          $city
 * @property string          $address
 * @property string|null     $phone1
 * @property string|null     $phone1Description
 * @property string|null     $phone2
 * @property string|null     $phone2Description
 * @property string|bool     $hvvLink
 * @property string|null     $hvvId
 * @property string|int|null $groundskeeper
 * @property string|bool     $published
 *
 * @method static SportsFacilityModel|null findById($id, array $opt=array())
 * @method static SportsFacilityModel|null findByPk($id, array $opt=array())
 * @method static SportsFacilityModel|null findByIdOrAlias($val, array $opt=array())
 * @method static SportsFacilityModel|null findOneBy($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByTstamp($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByName($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByType($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByStreet($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByPostal($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByCity($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByAddress($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByPhone1($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByPhone1Description($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByPhone2($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByPhone2Description($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByHvvLink($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByHvvId($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByGroundskeeper($col, $val, array $opt=array())
 * @method static SportsFacilityModel|null findOneByPublished($col, $val, array $opt=array())
 *                                                                                                                                 -
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByName($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByType($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByStreet($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByPostal($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByCity($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByAddress($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByPhone1($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByPhone1Description($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByPhone2($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByPhone2Description($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByHvvLink($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByHvvId($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByGroundskeeper($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findByPublished($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<SportsFacilityModel>|SportsFacilityModel|null findAll(array $opt=array())
 *                                                                                                                                 -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByName($val, array $opt=array())
 * @method static integer countByType($val, array $opt=array())
 * @method static integer countByStreet($val, array $opt=array())
 * @method static integer countByPostal($val, array $opt=array())
 * @method static integer countByCity($val, array $opt=array())
 * @method static integer countByAddress($val, array $opt=array())
 * @method static integer countByPhone1($val, array $opt=array())
 * @method static integer countByPhone1Description($val, array $opt=array())
 * @method static integer countByPhone2($val, array $opt=array())
 * @method static integer countByPhone2Description($val, array $opt=array())
 * @method static integer countByHvvLink($val, array $opt=array())
 * @method static integer countByHvvId($val, array $opt=array())
 * @method static integer countByGroundskeeper($val, array $opt=array())
 * @method static integer countByPublished($val, array $opt=array())
 */
class SportsFacilityModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_sports_facility';
}

class_alias(SportsFacilityModel::class, 'SportsFacilityModel');
