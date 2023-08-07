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
 * Read and write verein obmann data.
 *
 * @method static BsaVereinObmannModel|null findOneBy($col, $val, array $opt=array())
 *
 * @property int $verein
 */
class BsaVereinObmannModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_verein_obmann';

    /**
     * Zeigt an, ob eine Person ein Vereinsobmann ist.
     *
     * @param int $schiedsrichterId Die numerische ID aus tl_bsa_schiedsrichter
     *
     * @return true|false true, wenn es sich um einen Vereinsobmann handelt
     */
    public static function isVereinsobmann($schiedsrichterId)
    {
        $intVereinId = static::getVereinOfObmann($schiedsrichterId);

        return null !== $intVereinId;
    }

    /**
     * Liefert die Vereins-ID, wenn eine Person ein Vereinsobmann ist.
     *
     * @param int $schiedsrichterId Die numerische ID aus tl_bsa_schiedsrichter
     *
     * @return int|null ID des Vereins oder null
     */
    public static function getVereinOfObmann($schiedsrichterId)
    {
        $objSR = BsaSchiedsrichterModel::findSchiedsrichter($schiedsrichterId);

        if (!isset($objSR) || $objSR->__get('deleted')) {
            return null;
        }

        $objVereinObmann = static::findOneBy(['obmann=? OR stellv_obmann_1=? OR stellv_obmann_2=?'], [$schiedsrichterId, $schiedsrichterId, $schiedsrichterId]);

        return isset($objVereinObmann) ? $objVereinObmann->verein : null;
    }

    /**
     * returns the list of email addresses assigned by a chairman or a vice chairman to the specified club.
     *
     * @param int $clubId the id of the club
     *
     * @return array an array with all email addresses
     */
    public static function getEmailAddressesOfChairmans(int $clubId): array
    {
        return Database::getInstance()
            ->prepare('SELECT tl_bsa_schiedsrichter.email FROM tl_bsa_schiedsrichter JOIN tl_bsa_verein_obmann ON (tl_bsa_schiedsrichter.id=tl_bsa_verein_obmann.obmann OR tl_bsa_schiedsrichter.id=tl_bsa_verein_obmann.stellv_obmann_1 OR tl_bsa_schiedsrichter.id=tl_bsa_verein_obmann.stellv_obmann_2) WHERE tl_bsa_verein_obmann.verein=? AND tl_bsa_schiedsrichter.email<>? ORDER BY tl_bsa_schiedsrichter.email')
            ->execute($clubId, '')
            ->fetchEach('email')
    ;
    }
}
