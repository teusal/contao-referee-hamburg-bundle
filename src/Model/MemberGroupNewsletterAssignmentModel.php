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
 * Reads and writes newsletter to member group assignments.
 *
 * @property string|int $id
 * @property string|int $pid
 * @property string|int $sorting
 * @property string|int $tstamp
 * @property string|int $newsletterChannelId
 *
 * @method static MemberGroupNewsletterAssignmentModel|null findById($id, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findByPk($id, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findByIdOrAlias($val, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findOneBy($col, $val, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findOneByPid($val, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findOneBySorting($val, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findOneByTstamp($val, array $opt=array())
 * @method static MemberGroupNewsletterAssignmentModel|null findOneByNewsletterChannelId($val, array $opt=array())
 *                                                                                                                                                                     -
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findByPid($val, array $opt=array())
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findBySorting($val, array $opt=array())
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findByNewsletterChannelId($val, array $opt=array())
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<MemberGroupNewsletterAssignmentModel>|MemberGroupNewsletterAssignmentModel|null findAll(array $opt=array())
 *                                                                                                                                                                     -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByPid($val, array $opt=array())
 * @method static integer countBySorting($val, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByNewsletterChannelId($val, array $opt=array())
 */
class MemberGroupNewsletterAssignmentModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_member_group_newsletter_assignment';
}

class_alias(MemberGroupNewsletterAssignmentModel::class, 'MemberGroupNewsletterAssignmentModel');
