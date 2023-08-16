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
 * Reads and writes.
 *
 * @property string|int  $id
 * @property string|int  $tstamp
 * @property string|int  $refereeId
 * @property string      $nameReverse
 * @property string|int  $dateOfFormReceived
 * @property string|bool $showDateOfBirth
 * @property string|bool $showStreet
 * @property string|bool $showPostal
 * @property string|bool $showCity
 * @property string|bool $showPhone1
 * @property string|bool $showPhone2
 * @property string|bool $showMobile
 * @property string|bool $showFax
 * @property string|bool $showEmail
 * @property string|bool $showPhoto
 *
 * @method static WebsiteDataReleaseModel|null findById($id, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findByPk($id, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findByIdOrAlias($val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneBy($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByTstamp($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByRefereeId($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByNameReverse($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByDateOfFormReceived($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowDateOfBirth($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowStreet($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowPostal($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowCity($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowPhone1($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowPhone2($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowMobile($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowFax($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowEmail($col, $val, array $opt=array())
 * @method static WebsiteDataReleaseModel|null findOneByShowPhoto($col, $val, array $opt=array())
 *                                                                                                                                          -
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByRefereeId($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByNameReverse($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByDateOfFormReceived($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowDateOfBirth($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowStreet($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowPostal($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowCity($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowPhone1($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowPhone2($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowMobile($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowFax($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowEmail($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findByShowPhoto($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<WebsiteDataReleaseModel>|WebsiteDataReleaseModel|null findAll(array $opt=array())
 *                                                                                                                                          -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByRefereeId($val, array $opt=array())
 * @method static integer countByNameReverse($val, array $opt=array())
 * @method static integer countByDateOfFormReceived($val, array $opt=array())
 * @method static integer countByShowDateOfBirth($val, array $opt=array())
 * @method static integer countByShowStreet($val, array $opt=array())
 * @method static integer countByShowPostal($val, array $opt=array())
 * @method static integer countByShowCity($val, array $opt=array())
 * @method static integer countByShowPhone1($val, array $opt=array())
 * @method static integer countByShowPhone2($val, array $opt=array())
 * @method static integer countByShowMobile($val, array $opt=array())
 * @method static integer countByShowFax($val, array $opt=array())
 * @method static integer countByShowEmail($val, array $opt=array())
 * @method static integer countByShowPhoto($val, array $opt=array())
 */
class WebsiteDataReleaseModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_website_data_release';

    /**
     * Freigabe anhand des Schiedsrichters finden.
     *
     * @param mixed $refereeId Die numerische ID aus tl_bsa_referee
     *
     * @return WebsiteDataReleaseModel|null Das Model oder null wenn es keine Freigabe gibt
     */
    public static function findFreigabe($refereeId)
    {
        return self::findOneBy('refereeId', $refereeId, []);
    }
}

class_alias(WebsiteDataReleaseModel::class, 'WebsiteDataReleaseModel');
