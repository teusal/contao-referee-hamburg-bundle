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
use Contao\Database\Result;
use Contao\Idna;
use Contao\Model;
use Contao\Model\Collection;

/**
 * Reads and writes referees.
 *
 * @property string|int        $id
 * @property string|int        $tstamp
 * @property string|null       $cardNumber
 * @property string            $gender
 * @property string            $lastname
 * @property string            $firstname
 * @property string            $nameReverse
 * @property string|int        $clubId
 * @property string            $street
 * @property string            $postal
 * @property string            $city
 * @property string            $phone1
 * @property string            $phone2
 * @property string            $mobile
 * @property string            $fax
 * @property string            $email
 * @property string            $emailContactForm
 * @property string|int|null   $dateOfBirth
 * @property string|null       $dateOfBirthAsDate
 * @property string|int|null   $dateOfRefereeExamination
 * @property string|null       $dateOfRefereeExaminationAsDate
 * @property string            $state
 * @property string|bool       $deleted
 * @property string|null       $image
 * @property string|null       $imagePrint
 * @property string|null       $imageExempted
 * @property string|bool       $searchable
 * @property string|bool       $isNew
 * @property string|null       $importKey
 * @property string|array|null $addressbookVcards
 *
 * @method static RefereeModel|null findById($id, array $opt=array())
 * @method static RefereeModel|null findByPk($id, array $opt=array())
 * @method static RefereeModel|null findByIdOrAlias($val, array $opt=array())
 * @method static RefereeModel|null findOneBy($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByTstamp($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByCardNumber($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByGender($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByLastname($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByFirstname($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByNameReverse($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByClubId($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByStreet($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByPostal($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByCity($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByPhone1($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByPhone2($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByMobile($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByFax($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByEmail($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByEmailContactForm($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByDateOfBirth($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByDateOfBirthAsDate($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByDateOfRefereeExamination($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByDateOfRefereeExaminationAsDate($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByState($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByDeleted($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByImage($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByImagePrint($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByImageExempted($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneBySearchable($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByIsNew($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByImportKey($col, $val, array $opt=array())
 * @method static RefereeModel|null findOneByAddressbookVcards($col, $val, array $opt=array())
 *                                                                                                                                -
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByTstamp($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByCardNumber($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByGender($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByLastname($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByFirstname($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByNameReverse($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByClubId($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByStreet($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByPostal($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByCity($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByPhone1($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByPhone2($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByMobile($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByFax($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByEmail($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByEmailContactForm($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByDateOfBirth($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByDateOfBirthAsDate($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByDateOfRefereeExamination($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByDateOfRefereeExaminationAsDate($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByState($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByDeleted($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByImage($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByImagePrint($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByImageExempted($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findBySearchable($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByIsNew($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByImportKey($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findByAddressbookVcards($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findMultipleByIds($val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findBy($col, $val, array $opt=array())
 * @method static Collection|array<RefereeModel>|RefereeModel|null findAll(array $opt=array())
 *                                                                                                                                -
 * @method static integer countById($id, array $opt=array())
 * @method static integer countByTstamp($val, array $opt=array())
 * @method static integer countByCardNumber($val, array $opt=array())
 * @method static integer countByGender($val, array $opt=array())
 * @method static integer countByLastname($val, array $opt=array())
 * @method static integer countByFirstname($val, array $opt=array())
 * @method static integer countByNameReverse($val, array $opt=array())
 * @method static integer countByClubId($val, array $opt=array())
 * @method static integer countByStreet($val, array $opt=array())
 * @method static integer countByPostal($val, array $opt=array())
 * @method static integer countByCity($val, array $opt=array())
 * @method static integer countByPhone1($val, array $opt=array())
 * @method static integer countByPhone2($val, array $opt=array())
 * @method static integer countByMobile($val, array $opt=array())
 * @method static integer countByFax($val, array $opt=array())
 * @method static integer countByEmail($val, array $opt=array())
 * @method static integer countByEmailContactForm($val, array $opt=array())
 * @method static integer countByDateOfBirth($val, array $opt=array())
 * @method static integer countByDateOfBirthAsDate($val, array $opt=array())
 * @method static integer countByDateOfRefereeExamination($val, array $opt=array())
 * @method static integer countByDateOfRefereeExaminationAsDate($val, array $opt=array())
 * @method static integer countByState($val, array $opt=array())
 * @method static integer countByDeleted($val, array $opt=array())
 * @method static integer countByImage($val, array $opt=array())
 * @method static integer countByImagePrint($val, array $opt=array())
 * @method static integer countByImageExempted($val, array $opt=array())
 * @method static integer countBySearchable($val, array $opt=array())
 * @method static integer countByIsNew($val, array $opt=array())
 * @method static integer countByImportKey($val, array $opt=array())
 * @method static integer countByAddressbookVcards($val, array $opt=array())
 */
class RefereeModel extends Model
{
    /**
     * age.
     *
     * @var int|null
     */
    public $age;

    /**
     * has birthday today or not.
     *
     * @var bool
     */
    public $hasBirthday = false;

    /**
     * friendly email address like "Max Mustermann <max.mustermann@example.com>".
     *
     * @var string
     */
    public $friendlyEmail = '';
    /**
     * Table name.
     *
     * @var string
     */
    protected static $strTable = 'tl_bsa_referee';

    /**
     * Load the relations and optionally process a result set.
     *
     * @param Result|array<mixed> $objResult An optional database result or array
     */
    public function __construct($objResult = null)
    {
        parent::__construct($objResult);

        if (null !== $this->dateOfBirth && !empty($this->dateOfBirth)) {
            $this->age = date('Y') - date('Y', (int) $this->dateOfBirth);

            if (date('md', time()) < date('md', (int) $this->dateOfBirth)) {
                --$this->age;
            }

            $this->hasBirthday = date('md', time()) === date('md', (int) $this->dateOfBirth);
        }

        if (!empty($this->email)) {
            $name = trim($this->firstname.' '.$this->lastname);

            if (empty($name)) {
                $this->friendlyEmail = $this->email;
            } else {
                $this->friendlyEmail = Idna::encodeEmail(sprintf('%s [%s]', $name, $this->email));
            }
        }
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
        $objReferee = static::findReferee($refereeId);

        return isset($objReferee) && $objReferee->clubId && !$objReferee->deleted;
    }

    /**
     * Method provides all the birthday children of today.
     *
     * @param string $strWhere
     *
     * @return Collection|array<RefereeModel>|RefereeModel|null a collection of referees or null if there are no birthday childs in the specified period
     */
    public static function getRefereesWithBirthdayToday($strWhere = '')
    {
        return static::getRefereesWithBirthdayNextDays(0, 0, $strWhere);
    }

    /**
     * Method provides all birthday children of the next days.
     *
     * @param int    $intFirstDay
     * @param int    $intLastDay
     * @param string $strWhere
     *
     * @return Collection|array<RefereeModel>|RefereeModel|null a collection of referees or null if there are no birthday childs in the specified period
     */
    public static function getRefereesWithBirthdayNextDays($intFirstDay, $intLastDay, $strWhere = '')
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

        $t = static::$strTable;
        $objResult = Database::getInstance()->execute("SELECT * FROM $t WHERE clubId<>0 AND deleted='' AND (".implode(' OR ', $arrWhere).") $strWhere ORDER BY MONTH(dateOfBirthAsDate) ASC, DAY(dateOfBirthAsDate) ASC, nameReverse");

        if ($objResult->numRows < 1) {
            return null;
        }

        return static::createCollectionFromDbResult($objResult, $t);
    }

    /**
     * Returns the age from the record of a referee.
     *
     * @param RefereeModel|array<string, mixed>|null $objReferee
     *
     * @return int|null
     */
    public static function getAge2($objReferee)
    {
        // TODO refactor to object usage. overwrite __get() and pass 'alter'
        if (!isset($objReferee)) {
            return null;
        }

        if (\is_array($objReferee)) {
            $arrSR = $objReferee;
        } else {
            $arrSR = $objReferee->row();
        }

        $alter = date('Y') - date('Y', (int) ($arrSR['dateOfBirth']));

        if (date('md', time()) < date('md', (int) ($arrSR['dateOfBirth']))) {
            --$alter;
        }

        return $alter;
    }
}

class_alias(RefereeModel::class, 'RefereeModel');
