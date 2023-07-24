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
 * Reads and writes referees.
 *
 * @property int    $id
 * @property int    $verein
 * @property string $vorname
 * @property string $nachname
 * @property string $geschlecht
 */
class BsaSchiedsrichterModel extends \Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_schiedsrichter';

    /**
     * Schiedsrichters anhand der ID finden.
     *
     * @param mixed $schiedsrichterId Die numerische ID aus tl_bsa_schiedsrichter
     *
     * @return BsaSchiedsrichterModel|null Das Model oder null wenn es keinen Schiedsrichter gibt
     */
    public static function findSchiedsrichter($schiedsrichterId)
    {
        return static::findByPk($schiedsrichterId);
    }

    /**
     * Zeigt an, ob eine Person ein Vereinsschiedsrichter ist.
     *
     * @param int $schiedsrichterId Die numerische ID aus tl_bsa_schiedsrichter
     *
     * @return true|false true, wenn es sich um einen Vereinsschiedsrichter handelt
     */
    public static function isVereinsschiedsrichter($schiedsrichterId)
    {
        $objSR = static::findSchiedsrichter($schiedsrichterId);

        return isset($objSR) && $objSR->__get('verein') && !$objSR->__get('deleted');
    }

    /**
     * Methode liefert alle Geburtstagskinder von heute.
     */
    public static function getPersonWithBirthdayToday($strWhere = '')
    {
        return static::getPersonWithBirthdayNextDays(0, 0, $strWhere);
    }

    /**
     * Methode liefert alle Geburtstagskinder der nächsten Tage.
     */
    public static function getPersonWithBirthdayNextDays($intFirstDay, $intLastDay, $strWhere = '')
    {
        if ($intFirstDay > $intLastDay) {
            return [];
        }

        $arrWhere = [];

        for ($i = $intFirstDay; $i <= $intLastDay; ++$i) {
            $arrWhere[] = '(MONTH(geburtsdatum_date) = MONTH(ADDDATE(NOW(),'.$i.')) AND DAYOFMONTH(geburtsdatum_date) = DAYOFMONTH(ADDDATE(NOW(),'.$i.')))';
        }

        $strWhere = trim($strWhere);

        if ($strWhere) {
            $strWhere = 'AND '.$strWhere;
        }

        return Database::getInstance()->execute('SELECT * FROM tl_bsa_schiedsrichter WHERE verein<>0 AND deleted="" AND ('.implode(' OR ', $arrWhere).') '.$strWhere.'ORDER BY MONTH(geburtsdatum_date) ASC, DAY(geburtsdatum_date) ASC, name_rev')
            ->fetchAllAssoc()
        ;
    }

    /**
     * Liefert das alter aus dem Datensatz eines Schiedsrichters.
     */
    public static function getAlter($objSR)
    {
        if (!isset($objSR)) {
            return false;
        }

        if (\is_array($objSR)) {
            $arrSR = $objSR;
        } else {
            $arrSR = $objSR->row();
        }

        $alter = date('Y') - date('Y', $arrSR['geburtsdatum']);

        if (date('md', time()) < date('md', $arrSR['geburtsdatum'])) {
            --$alter;
        }

        return $alter;
    }
}