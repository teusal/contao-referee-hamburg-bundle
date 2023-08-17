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
 * Reads and writes sports facility club assignments.
 *
 * @property string|int        $id
 * @property string|int        $tstamp
 * @property string|int        $clubId
 * @property string|array|null $sportsFacilityIds
 *
 * @method static SportsFacilityClubAssignmentModel|null findById($id, array $opt=array())
 * @method static SportsFacilityClubAssignmentModel|null findByPk($id, array $opt=array())
 * @method static SportsFacilityClubAssignmentModel|null findByIdOrAlias($val, array $opt=array())
 * @method static SportsFacilityClubAssignmentModel|null findOneBy($col, $val, array $opt=array())
 * @method static SportsFacilityClubAssignmentModel|null findOneByTstamp($val, array $opt=array())
 * @method static SportsFacilityClubAssignmentModel|null findOneByClubId($val, array $opt=array())
 * @method static SportsFacilityClubAssignmentModel|null findOneBySportsFacilityIds($val, array $opt=array())
 *                                                                                                                                                             -
 * @method static Collection|array<SportsFacilityClubAssignmentModel>|SportsFacilityClubAssignmentModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<SportsFacilityClubAssignmentModel>|SportsFacilityClubAssignmentModel|null findByClubId($val, array $opt=array())
 * @method static Collection|array<SportsFacilityClubAssignmentModel>|SportsFacilityClubAssignmentModel|null findBySportsFacilityIds($val, array $opt=array())
 * @method static Collection|array<SportsFacilityClubAssignmentModel>|SportsFacilityClubAssignmentModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<SportsFacilityClubAssignmentModel>|SportsFacilityClubAssignmentModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<SportsFacilityClubAssignmentModel>|SportsFacilityClubAssignmentModel|null findAll(array $opt=array())
 *                                                                                                                                                             -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByClubId($val, array $opt=array())
 * @method static integer countBySportsFacilityIds($val, array $opt=array())
 */
class SportsFacilityClubAssignmentModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_sports_facility_club_assignment';
}

class_alias(SportsFacilityClubAssignmentModel::class, 'SportsFacilityClubAssignmentModel');
