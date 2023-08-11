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

/**
 * Reads and writes.
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
