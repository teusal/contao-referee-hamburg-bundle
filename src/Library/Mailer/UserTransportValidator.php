<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Mailer;

use Contao\BackendUser;
use Contao\Input;
use Contao\System;

class UserTransportValidator extends System
{
    /**
     * checking an existing mailer transport for the current logged in beackend user.
     *
     * @return string|null an error message if there is no mailer transport
     */
    public function getSystemMessages()
    {
        if (TL_MODE !== 'BE') {
            return null;
        }

        if (!empty(Input::get('do'))) {
            return null;
        }

        /** @var AvailableTransports $availableTransports */
        $availableTransports = System::getContainer()->get('contao.mailer.available_transports');

        if (!$availableTransports->existsTransport(BackendUser::getInstance()->email)) {
            return '<p class="tl_error">Es wurde keine Konfiguration zum Mailversand für Sie gefunden. Sie können so keine E-Mails versenden. Bitte wenden Sie sich an einen Administrator.</p>';
        }

        return null;
    }
}
