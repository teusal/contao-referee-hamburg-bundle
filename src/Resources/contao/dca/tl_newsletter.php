<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

use Contao\BackendUser;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\Input;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\Mailer\AvailableTransports;

/* field changes */
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['subject']['eval']['tl_class'] = 'lng';
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['options_callback'] = ['contao.mailer.available_transports', 'getSystemAndBackendUserTransportOptions'];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['load_callback'][] = [tl_bsa_newsletter::class, 'setDefaultMailerTransport'];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['mailerTransport']['eval']['mandatory'] = true;
unset($GLOBALS['TL_DCA']['tl_newsletter']['fields']['sender']['load_callback'],
      $GLOBALS['TL_DCA']['tl_newsletter']['fields']['senderName']['load_callback']);
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['sender']['save_callback'][] = ['tl_bsa_newsletter', 'validateOverwritingMailerTransport'];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['senderName']['save_callback'][] = ['tl_bsa_newsletter', 'validateOverwritingMailerTransport'];

if (!empty(BackendUser::getInstance()->__get('signatur_html'))) {
    $GLOBALS['TL_DCA']['tl_newsletter']['fields']['content']['default'] = '<p></p>'.BackendUser::getInstance()->__get('signatur_html');
}

/* field definition */
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['replyToAddress'] = [
    'inputType' => 'text',
    'eval' => ['rgxp' => 'email', 'maxlength' => 128, 'decodeEntities' => true, 'tl_class' => 'w50'],
    'sql' => "varchar(128) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['ccChairman'] = [
    'inputType' => 'checkbox',
    'eval' => [],
    'sql' => "char(1) NOT NULL default ''",
];
$GLOBALS['TL_DCA']['tl_newsletter']['fields']['infomailSent'] = [
    'sql' => "char(1) NOT NULL default ''",
];

/* change default palette, add legend and field */
PaletteManipulator::create()
    // remove some fields
    ->removeField('alias')
    ->removeField('text')
    ->removeField('template')
    ->removeField('sendText')
    ->removeField('externalImages')
     // add palette and field for reply options
    ->addLegend('reply_to_legend', 'sender_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('replyToAddress', 'reply_to_legend', PaletteManipulator::POSITION_APPEND)
     // add palette and field for carbon copy options
    ->addLegend('cc_legend', 'reply_to_legend', PaletteManipulator::POSITION_AFTER)
    ->addField('ccChairman', 'cc_legend', PaletteManipulator::POSITION_APPEND)
    // add palette and field for info text settings
    ->applyToPalette('default', 'tl_newsletter')
;

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_bsa_newsletter extends Backend
{
    /**
     * Import the back end user object.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(BackendUser::class, 'User');
    }

    /**
     * Add the sender address as placeholder.
     *
     * @param mixed         $varValue Currently stored value
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed New value to be loaded
     */
    public function setDefaultMailerTransport($varValue, DataContainer $dc)
    {
        if (empty($varValue) && $dc->__get('activeRecord') && $dc->__get('activeRecord')->pid) {
            $objChannel = $this->Database->prepare('SELECT mailerTransport FROM tl_newsletter_channel WHERE id=?')
                ->execute($dc->__get('activeRecord')->pid)
            ;

            $varValue = $objChannel->__get('mailerTransport') ?: $this->User->email;
        }

        return $varValue;
    }

    /**
     * Validates whether the value is empty if the set mailer transport defines a from address. If this is the case, an error is set in the field.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function validateOverwritingMailerTransport($varValue, DataContainer $dc)
    {
        if (!empty($varValue)) {
            /** @var AvailableTransports $availableTransports */
            $availableTransports = System::getContainer()->get('contao.mailer.available_transports');
            $tranport = $availableTransports->getTransport(Input::post('mailerTransport'));

            if (null !== $tranport && null !== $tranport->getFrom()) {
                throw new Exception('Der Mailer-Transport überschriebt die Einstellung. Dieses Feld muss leer bleiben oder ein Mailer-Transport ohne Absendereinstellungen gewählt werden.');
            }
        }

        return $varValue;
    }
}
