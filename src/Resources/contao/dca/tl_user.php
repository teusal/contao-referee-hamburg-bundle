<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\DataContainer;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Contao\UserModel;

/*
 * change onload
 */
$GLOBALS['TL_DCA']['tl_user']['config']['onload_callback'][] = [bsa_user::class, 'onLoad'];

/*
 * Change palette
 */
foreach ($GLOBALS['TL_DCA']['tl_user']['palettes'] as $palette => $value) {
    if (is_string($value)) {
        $GLOBALS['TL_DCA']['tl_user']['palettes'][$palette] = str_replace('email;', 'email,signatur_html;', $value);
    }
}

/*
 * Add fields
 */
$GLOBALS['TL_DCA']['tl_user']['fields']['signatur_html'] = [
    'inputType' => 'textarea',
    'eval' => ['rte' => 'tinyNews', 'mandatory' => false, 'tl_class' => 'clr'],
    'sql' => 'mediumtext NULL',
];

/**
 * Class bsa_user.
 *
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class bsa_user extends tl_user
{
    /**
     * validates an existig mailer transport identified by set mailaddress and set a info if it isn't there.
     *
     * @param DataContainer|null $dc
     */
    public function onLoad(DataContainer $dc): void
    {
        $user = UserModel::findById($dc->id);

        if (!isset($user)) {
            return;
        }

        if (empty($user->email)) {
            return;
        }

        $availableTransports = System::getContainer()->get('contao.mailer.available_transports');

        if (!$availableTransports->existsTransportByEmail($user->email)) {
            if ('login' === Input::get('do')) {
                Message::addError('Es wurde keine Konfiguration zum Mailversand für Sie anhand Ihrer E-Mail-Adresse gefunden. Sie können so keine E-Mails versenden. Bitte wenden Sie sich an einen Administrator.');
            } else {
                Message::addError('Es wurde keine Konfiguration zum Mailversand für diesen User anhand seiner Mailadresse gefunden. Er kann so keine E-Mails versenden. Bitte wenden Sie sich an einen Administrator.');
            }
        }
    }
}
