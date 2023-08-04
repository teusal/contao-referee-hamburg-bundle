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
use Teusal\ContaoRefereeHamburgBundle\Model\BsaVeranstaltungModel;

/**
 * Class AbstractEventParticipiantHandler
 */
abstract class AbstractEventParticipiantHandler extends Backend
{
    /**
     * @var BsaVeranstaltungModel $objEvent
     */
    protected $objEvent;

    /**
     * @var array<int> $arrAlreadyRegisteredParticipiants
     */
    protected $arrAlreadyRegisteredParticipiants;

    /**
     * called to execute registration or import
     *
     * @return string form for backend as html
     */
    public abstract function execute() :string;
    /**
     * Import the back end user object
     */
    public final function __construct()
    {
        parent::__construct();

        $this->import(BackendUser::class, 'User');
        System::loadLanguageFile('tl_bsa_veranstaltung');

        if (Input::get('key') == 'spiele' || Input::get('key') == 'besucher' || Input::get('key') == 'import') {
            // Load and cache the event.
            $this->objEvent = BsaVeranstaltungModel::findByPk(Input::get('id'));
            if (!isset($this->objEvent)) {
                throw new \Exception("Die Veranstaltung konnte nicht ermittelt werden.");
            }

            // Load and cache the participiants
            $this->arrAlreadyRegisteredParticipiants = $this->Database->execute("SELECT sr_id FROM tl_bsa_teilnehmer WHERE pid=" . $this->objEvent->__get('id'))
                ->fetchEach('sr_id');
        }
    }

    /**
     * returns true if the specified participiant is already registered
     *
     * @param mixed $participiantId ID of the searched participiant
     *
     * @return bool true if already registered
     */
    protected final function isAlreadyRegistered($participiantId) :bool {
        return in_array($participiantId, $this->arrAlreadyRegisteredParticipiants);
    }

    /**
     * returns the back button as html string
     *
     * @return string the back button
     */
    protected final function getBackButton()
    {
        return '<div id="tl_buttons">
    <a href="'. StringUtil::ampersand(str_replace('&key='.Input::get('key'), '', Environment::get('request'))) .'" class="header_back" title="'. StringUtil::specialchars($GLOBALS['TL_LANG']['MSC']['backBTTitle']) .'" accesskey="b">'. $GLOBALS['TL_LANG']['MSC']['backBT'] .'</a>
</div>';
    }

    /**
     * returns the header as html string
     *
     * @return string the header
     */
    protected final function getHeader() :string {
        return '<div class="tl_header" style="padding-left: 15px; border-bottom-style: none;">
    <table class="tl_header_table">
    <tr>
        <td><span class="tl_label">'. $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['datum'][0] .':</span> </td>
        <td>'. Date::parse(Config::get('dateFormat'), $this->objEvent->datum) .'</td>
    </tr>
    <tr>
        <td><span class="tl_label">'. $GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typ'][0] .':</span> </td>
        <td>'. ($GLOBALS['TL_LANG']['tl_bsa_veranstaltung']['typen'][$this->objEvent->typ] ?: $this->objEvent->typ) .'</td>
    </tr>
    </table>
</div>';
    }
}
