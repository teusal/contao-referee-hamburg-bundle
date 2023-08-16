<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Event;

use Contao\Backend;
use Contao\BackendUser;
use Contao\Config;
use Contao\Date;
use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Model\EventModel;

/**
 * Class AbstractEventParticipiantHandler.
 */
abstract class AbstractEventParticipiantHandler extends Backend
{
    /**
     * The event for dealing with the recipients.
     */
    protected EventModel $objEvent;

    /**
     * @var array<int>
     */
    protected $arrAlreadyRegisteredParticipiants;

    /**
     * Import the back end user object.
     */
    final public function __construct()
    {
        parent::__construct();

        $this->import(BackendUser::class, 'User');
        System::loadLanguageFile('tl_bsa_event');

        if ('spiele' === Input::get('key') || 'besucher' === Input::get('key') || 'import' === Input::get('key')) {
            // Load and cache the event.
            $objEvent = EventModel::findByPk(Input::get('id'));

            if (isset($objEvent)) {
                $this->objEvent = $objEvent;
            } else {
                throw new \Exception('Die Veranstaltung konnte nicht ermittelt werden.');
            }

            // Load and cache the participiants
            $this->arrAlreadyRegisteredParticipiants = $this->Database->execute('SELECT refereeId FROM tl_bsa_event_participiant WHERE pid='.$this->objEvent->id)
                ->fetchEach('refereeId')
            ;
        }
    }

    /**
     * called to execute registration or import.
     *
     * @return string form for backend as html
     */
    abstract public function execute(): string;

    /**
     * returns true if the specified participiant is already registered.
     *
     * @param mixed $participiantId ID of the searched participiant
     *
     * @return bool true if already registered
     */
    final protected function isAlreadyRegistered($participiantId): bool
    {
        return \in_array($participiantId, $this->arrAlreadyRegisteredParticipiants, true);
    }

    /**
     * returns the back button as html string.
     *
     * @return string the back button
     */
    final protected function getBackButton()
    {
        return '<div id="tl_buttons">
    <a href="'.StringUtil::ampersand(str_replace('&key='.Input::get('key'), '', Environment::get('request'))).'" class="header_back" title="'.StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']).'" accesskey="b">'.$GLOBALS['TL_LANG']['MSC']['backBT'].'</a>
</div>';
    }

    /**
     * returns the header as html string.
     *
     * @return string the header
     */
    final protected function getHeader(): string
    {
        return '<div class="tl_header" style="padding-left: 15px; border-bottom-style: none;">
    <table class="tl_header_table">
    <tr>
        <td><span class="tl_label">'.$GLOBALS['TL_LANG']['tl_bsa_event']['date'][0].':</span> </td>
        <td>'.Date::parse(Config::get('dateFormat'), $this->objEvent->date).'</td>
    </tr>
    <tr>
        <td><span class="tl_label">'.$GLOBALS['TL_LANG']['tl_bsa_event']['type'][0].':</span> </td>
        <td>'.($GLOBALS['TL_LANG']['tl_bsa_event']['typen'][$this->objEvent->type] ?: $this->objEvent->type).'</td>
    </tr>
    </table>
</div>';
    }
}
