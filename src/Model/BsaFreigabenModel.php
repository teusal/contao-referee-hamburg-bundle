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
class BsaFreigabenModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_freigaben';

    /**
     * Freigabe anhand des Schiedsrichters finden.
     *
     * @param mixed $schiedsrichterId Die numerische ID aus tl_bsa_schiedsrichter
     *
     * @return BsaFreigabenModel|null Das Model oder null wenn es keine Freigabe gibt
     */
    public static function findFreigabe($schiedsrichterId)
    {
        return self::findOneBy('schiedsrichter', $schiedsrichterId, []);
    }
}
