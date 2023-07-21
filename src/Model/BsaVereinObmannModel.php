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

/**
 * Read and write verein obmann data.
 *
 * @property int $verein
 */
class BsaVereinObmannModel extends \Model
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

        return isset($objVerein) ? $objVereinObmann->verein : null;
    }
}
