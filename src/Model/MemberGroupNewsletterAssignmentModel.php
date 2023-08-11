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
 * Reads and writes newsletter to member group assignments.
 *
 * @property int $refereeId
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
