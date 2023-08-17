<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Options;

use Contao\DataContainer;
use Contao\StringUtil;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubModel;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * This class filters transports while adding by backend users emailaddress.
 */
class RefereeOptions
{
    /**
     * clubs.
     *
     * @var array<int, array<string, mixed>>
     */
    private $arrClubs = [];

    /**
     * @var bool
     */
    private $includeDeleted;

    /**
     * Import the back end user object.
     *
     * @param bool $includeDeleted
     */
    public function __construct($includeDeleted)
    {
        $this->includeDeleted = $includeDeleted;

        // load the clubs in an array. the key is set by the id, the value is by nameShort.
        $objClub = ClubModel::findAll(['order' => 'nameShort']);

        if (isset($objClub)) {
            while ($objClub->next()) {
                $this->arrClubs[$objClub->id] = [
                    'number' => $objClub->number,
                    'nameShort' => StringUtil::specialchars($objClub->nameShort),
                    'visible' => $objClub->published,
                ];
            }
        }

        $this->arrClubs[0] = ['number' => '', 'nameShort' => 'vereinslos', 'visible' => false];
        $this->arrClubs[-1] = ['number' => '', 'nameShort' => 'gelöscht', 'visible' => false];
    }

    /**
     * returns all possible recipient options.
     *
     * @param DataContainer|null $dc Data Container object
     *
     * @return array<int, array<string, string>> the list of selectable options
     */
    public function getRefereeOptions($dc): array
    {
        $arrRecipientOptions = [];

        // add empty containers for all clubs
        foreach ($this->arrClubs as $club) {
            $arrRecipientOptions[$club['nameShort']] = [];
        }

        if (isset($dc)) {
            $strField = $dc->field;
            // as first add an option, if the selected referee is deleted
            $objReferee = RefereeModel::findByPk($dc->activeRecord->$strField);

            if (isset($objReferee) && $objReferee->deleted) {
                $arrRecipientOptions[$this->arrClubs[-1]['nameShort']][$objReferee->id] = StringUtil::specialchars($objReferee->nameReverse);
            }
        }

        // loading referees
        if ($this->includeDeleted) {
            $objReferee = RefereeModel::findAll(['order' => 'nameReverse']);
        } else {
            $objReferee = RefereeModel::findByDeleted('', ['order' => 'nameReverse']);
        }

        // sort each referee to his club
        if (isset($objReferee)) {
            while ($objReferee->next()) {
                $arrRecipientOptions[$this->arrClubs[($objReferee->deleted ? -1 : $objReferee->clubId)]['nameShort']][$objReferee->id] = StringUtil::specialchars($objReferee->nameReverse);
            }
        }

        // remove empty clubs
        foreach ($arrRecipientOptions as $clubName => $arrReferees) {
            if (empty($arrReferees)) {
                unset($arrRecipientOptions[$clubName]);
            }
        }

        return $arrRecipientOptions;
    }
}
