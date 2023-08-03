<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library;

use Contao\Database;
use Contao\DataContainer;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;

/**
 * Class SRHistory.
 */
class SRHistory extends System
{
    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Erzeugt einen Eintrag im Verlauf eines Schiedsrichters.
     */
    public static function insert($intSR, $referenceId, $arrAction, $strText, $strFunction): void
    {
        Database::getInstance()->prepare('INSERT INTO tl_bsa_schiedsrichter_historie (tstamp, schiedsrichter, reference_id, action_group, action, text, username, func) VALUES(?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(
                time(),
                $intSR,
                $referenceId,
                $arrAction[0],
                $arrAction[1],
                StringUtil::specialchars($strText),
                (\strlen($GLOBALS['TL_USERNAME']) ? TL_MODE.'::'.$GLOBALS['TL_USERNAME'] : ''),
                $strFunction
            )
        ;
    }

    /**
     * Erzeugt einen Eintrag im Verlauf eines Schiedsrichters beim Wiederherstellen eines Schiedsrichters.
     */
    public static function insertByUndeleteSchiedsrichter($intSR): void
    {
        static::insertByDeleteSchiedsrichter($intSR, 0);
    }

    /**
     * Erzeugt einen Eintrag im Verlauf eines Schiedsrichters beim Löschen eines Schiedsrichters.
     */
    public static function insertByDeleteSchiedsrichter($intSR, $deleted = 1): void
    {
        if ($deleted) {
            $action = 'REMOVE';
            $description = 'gelöscht';
        } else {
            $action = 'ADD';
            $description = 'wiederhergestellt';
        }
        static::insert($intSR, null, ['Schiedsrichter', $action], 'Der Schiedsrichter %s wurde '.$description.'.', __METHOD__);
    }

    /**
     * Erzeugt einen Eintrag im Verlauf eines Schiedsrichters beim Löschen eines Mitglieds.
     */
    public function insertByDeleteMember(DataContainer $dc, $undoId): void
    {
        if ($dc->__get('activeRecord')->schiedsrichter) {
            static::insert($dc->__get('activeRecord')->schiedsrichter, $dc->id, ['Login', 'REMOVE'], 'Der Login des Schiedsrichter %s mit dem Benutzernamen "%s" wurde gelöscht.', __METHOD__);
        }
    }

    /**
     * Erzeugt einen Eintrag im Verlauf eines Schiedsrichters beim Aktivieren oder Deaktivieren eines Logins.
     */
    public function insertByToggleMember($blnVisible, $dc)
    {
        $intSR = 0;
        $intMember = 0;

        if (isset($dc->__get('activeRecord')->schiedsrichter)) {
            $intSR = $dc->__get('activeRecord')->schiedsrichter;
            $intMember = $dc->id;
        } elseif ('schiedsrichter' === Input::get('do')) {
            if (Input::get('did')) {
                $intSR = Input::get('did');
            } elseif ('edit' === Input::get('act') && Input::get('id')) {
                $intSR = Input::get('id');
            }

            if ($intSR) {
                $member = Database::getInstance()->prepare('SELECT id FROM tl_member WHERE schiedsrichter=?')->execute($intSR);

                if ($member->next()) {
                    $intMember = $member->__get('id');
                }
            }
        }

        if ($intSR) {
            static::insert($intSR, $intMember, ['Login', 'EDIT'], 'Der Login des Schiedsrichter %s mit dem Benutzernamen "%s" wurde '.($blnVisible ? 'aktiviert' : 'deaktiviert').'.', __METHOD__);
        }

        return $blnVisible;
    }

    /**
     * Erzeugt einen Eintrag im Verlauf eines Schiedsrichters beim Austragen von Newsletter aus dem Frontend heraus.
     */
    public function unsubscribeNewsletterToSRHistory($varInput, $arrRemove): void
    {
        foreach ($arrRemove as $channel) {
            $arrSR = Database::getInstance()->prepare('SELECT r.schiedsrichter FROM tl_newsletter_recipients AS r, tl_bsa_schiedsrichter AS sr WHERE r.schiedsrichter=sr.id AND r.pid=? AND r.email=?')
                ->execute($channel, $varInput)
                ->fetchEach('schiedsrichter')
            ;

            foreach ($arrSR as $sr) {
                static::insert($sr, $channel, ['E-Mail-Verteiler', 'REMOVE'], 'Der Schiedsrichter %s hat sich aus dem Verteiler "%s" ausgetragen.', __METHOD__);
            }
        }
    }
}
