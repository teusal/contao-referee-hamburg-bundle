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
use Contao\Idna;
use Contao\Model;

/**
 * Reads and writes referees.
 *
 * @property int      $id
 * @property string   $cardNumber
 * @property string   $gender
 * @property string   $lastname
 * @property string   $firstname
 * @property string   $nameReverse
 * @property int      $club
 * @property string   $street
 * @property string   $postal
 * @property string   $city
 * @property string   $phone1
 * @property string   $phone2
 * @property string   $mobile
 * @property string   $fax
 * @property string   $email
 * @property string   $emailContactForm
 * @property int|null $dateOfBirth
 * @property int|null $dateOfRefereeExamination
 * @property string   $state
 * @property bool     $deleted
 */
class RefereeModel extends Model
{
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_referee';

    /**
     * return a friendly email address like "Max Mustermann <max.mustermann@example.com>".
     *
     * @return string friendly email address
     */
    public function getFriendlyEmail()
    {
        if (empty($this->email)) {
            return $this->email;
        }

        $name = trim($this->firstname.' '.$this->lastname);

        if (empty($name)) {
            return $this->email;
        }

        return Idna::encodeEmail(sprintf('%s [%s]', $name, $this->email));
    }

    /**
     * Find the referee by ID.
     *
     * @param mixed $refereeId The numeric ID from tl_bsa_referee
     *
     * @return RefereeModel|null The model or zero if there is no referee
     */
    public static function findReferee($refereeId)
    {
        return static::findByPk($refereeId);
    }

    /**
     * Indicates whether a person is a club referee.
     *
     * @param int $refereeId The numeric ID from tl_bsa_referee
     *
     * @return true|false true, if it is a club referee
     */
    public static function isClubReferee($refereeId)
    {
        $objSR = static::findReferee($refereeId);

        return isset($objSR) && $objSR->__get('clubId') && !$objSR->__get('deleted');
    }

    /**
     * Method provides all the birthday children of today.
     *
     * @param string $strWhere
     */
    public static function getPersonWithBirthdayToday($strWhere = ''): array
    {
        return static::getPersonWithBirthdayNextDays(0, 0, $strWhere);
    }

    /**
     * Method provides all birthday children of the next days.
     *
     * @param int    $intFirstDay
     * @param int    $intLastDay
     * @param string $strWhere
     */
    public static function getPersonWithBirthdayNextDays($intFirstDay, $intLastDay, $strWhere = ''): array
    {
        if ($intFirstDay > $intLastDay) {
            return [];
        }

        $arrWhere = [];

        for ($i = $intFirstDay; $i <= $intLastDay; ++$i) {
            $arrWhere[] = '(MONTH(dateOfBirthAsDate) = MONTH(ADDDATE(NOW(),'.$i.')) AND DAYOFMONTH(dateOfBirthAsDate) = DAYOFMONTH(ADDDATE(NOW(),'.$i.')))';
        }

        $strWhere = trim($strWhere);

        if ($strWhere) {
            $strWhere = 'AND '.$strWhere;
        }

        return Database::getInstance()->execute('SELECT * FROM tl_bsa_referee WHERE clubId<>0 AND deleted="" AND ('.implode(' OR ', $arrWhere).') '.$strWhere.'ORDER BY MONTH(dateOfBirthAsDate) ASC, DAY(dateOfBirthAsDate) ASC, nameReverse')
            ->fetchAllAssoc()
        ;
    }

    /**
     * Returns the age from the record of a referee.
     *
     * @param RefereeModel|array $objSR
     *
     * @return int|null
     */
    public static function getAge($objSR)
    {
        // TODO refactor to object usage. overwrite __get() and pass 'alter'
        if (!isset($objSR)) {
            return null;
        }

        if (\is_array($objSR)) {
            $arrSR = $objSR;
        } else {
            $arrSR = $objSR->row();
        }

        $alter = date('Y') - date('Y', (int) ($arrSR['dateOfBirth']));

        if (date('md', time()) < date('md', (int) ($arrSR['dateOfBirth']))) {
            --$alter;
        }

        return $alter;
    }
}
