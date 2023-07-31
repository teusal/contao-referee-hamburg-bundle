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

use Contao\Input;
use Contao\System;

class UserTransportValidator extends System
{
    /**
     * Kommende Geburtstage im Beckend auflisten.
     */
    public function getSystemMessages()
    {
        if (!empty(Input::get('do'))) {
            return null;
        }

        $availableTransports = System::getContainer()->get('contao.mailer.available_transports');

        if (empty($availableTransports->getTransports())) {
            return '<p class="tl_error">Es wurde keine Konfiguration zum Mailversand für Sie gefunden. Sie können so keine E-Mails versenden. Bitte wenden Sie sich an einen Administrator.</p>';
        }

        return null;
    }
}
