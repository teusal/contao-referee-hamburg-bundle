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

use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;

/**
 * Class ModuleClubEmail.
 */
class ModuleClubEmail extends AbstractModuleEmail
{
    /**
     * returns all possible carbon copy options.
     *
     * @return array<string, array<string, string>> the list of selectable options
     */
    protected function getCarbonCopyOptions(): array
    {
        $arrOptions = parent::getCarbonCopyOptions();
        unset($arrOptions['chairmans_option']);

        return $arrOptions;
    }

    /**
     * returns all possible recipient options.
     *
     * @return array<int, array<string, string>> the list of selectable options
     */
    protected function getRecipientOptions(): array
    {
        $arrRecipientOptions = [];

        // as first add an blank option
        $arrRecipientOptions[] = ['value' => '', 'label' => 'Bitte einen Empfänger wählen'];

        foreach ($this->arrClubs as $id => $club) {
            if (0 === $id) {
                // we don't wanna have 'vereinslos' as an option
                continue;
            }
            $arrRecipientOptions[] = ['value' => $id, 'label' => $club['nameShort']];
        }

        return $arrRecipientOptions;
    }

    /**
     * returns the recipient data for the specified club.
     *
     * @param mixed $clubId The id of the club
     *
     * @return array<string, mixed> the list of recipient data
     */
    protected function getRecipientData($clubId): array
    {
        return [
            'refereeId' => null,
            'clubId' => $clubId,
            'email_addresses' => ClubChairmanModel::getEmailAddressesOfChairmans((int) $clubId),
        ];
    }
}
