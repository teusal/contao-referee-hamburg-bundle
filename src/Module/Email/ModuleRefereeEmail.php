<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Module\Email;

use Contao\StringUtil;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;

/**
 * Class ModuleRefereeEmail.
 */
class ModuleRefereeEmail extends AbstractModuleEmail
{
    protected function getRecipientOptions(): array
    {
        $arrRecipientOptions = [];

        // as first add an blank option
        $arrRecipientOptions[] = ['value' => '', 'label' => 'Bitte einen Empfänger wählen'];

        // add empty containers for all clubs
        foreach ($this->arrClubs as $club) {
            $arrRecipientOptions[$club['nameShort']] = [];
        }

        // loading all referees and sort each to his club
        $objReferee = RefereeModel::findBy(['deleted=?', 'email<>?'], ['', ''], ['order' => 'nameReverse']);

        if (isset($objReferee)) {
            while ($objReferee->next()) {
                if ($objReferee->__get('clubId') || ClubChairmanModel::isChairman($objReferee->id)) {
                    $arrRecipientOptions[$this->arrClubs[$objReferee->__get('clubId')]['nameShort']][] = [
                        'value' => $objReferee->id,
                        'label' => StringUtil::specialchars($objReferee->__get('nameReverse').' <'.$objReferee->__get('email').'>'),
                    ];
                }
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

    protected function getRecipientData($refereeId): array
    {
        $objReferee = RefereeModel::findByPk($refereeId);

        return [
            'refereeId' => $objReferee->id,
            'clubId' => $objReferee->clubId,
            'email_addresses' => [$objReferee->email],
        ];
    }
}
