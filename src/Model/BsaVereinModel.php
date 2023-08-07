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
 * Reads and writes clubs.
 *
 * @property string $nummer
 * @property string $name_kurz
 * @property bool   $anzeigen
 */
class BsaVereinModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_verein';

    /**
     * Verein anhand der ID finden.
     *
     * @param mixed $vereinId Die numerische ID aus tl_bsa_verein
     *
     * @return BsaVereinModel|null Das Model oder null wenn es keinen Verein gibt
     */
    public static function findVerein($vereinId)
    {
        return static::findByPk($vereinId, []);
    }
}
