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

use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinObmannModel;

/**
 * Class ModuleClubEmail.
 */
class ModuleClubEmail extends AbstractModuleEmail
{
    protected function getCarbonCopyOptions(): array
    {
        $arrOptions = parent::getCarbonCopyOptions();
        unset($arrOptions['chairmans_option']);

        return $arrOptions;
    }

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

    protected function getRecipientData($clubId): array
    {
        return [
            'refereeId' => null,
            'clubId' => $clubId,
            'email_addresses' => BsaVereinObmannModel::getEmailAddressesOfChairmans((int) $clubId),
        ];
    }
}
