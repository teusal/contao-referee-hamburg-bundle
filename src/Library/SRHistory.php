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
     * Creates an entry in the history of a referee.
     *
     * @param int             $intSR
     * @param string|int|null $referenceId
     * @param array<string>   $arrAction
     * @param string          $strText
     * @param string          $strFunction
     */
    public static function insert($intSR, $referenceId, $arrAction, $strText, $strFunction): void
    {
        Database::getInstance()->prepare('INSERT INTO tl_bsa_referee_history (tstamp, refereeId, referenceId, actionGroup, action, text, username, func) VALUES(?, ?, ?, ?, ?, ?, ?, ?)')
            ->execute(
                time(),
                $intSR,
                $referenceId,
                $arrAction[0],
                $arrAction[1],
                StringUtil::specialchars($strText),
                (\strlen($GLOBALS['TL_USERNAME']) ? (\defined('TL_MODE') ? TL_MODE : 'XX').'::'.$GLOBALS['TL_USERNAME'] : ''),
                $strFunction
            )
        ;
    }

    /**
     * Creates an entry in a referee's history when restoring a referee.
     *
     * @param int $intSR
     */
    public static function insertByUndeleteSchiedsrichter($intSR): void
    {
        static::insertByDeleteSchiedsrichter($intSR, false);
    }

    /**
     * Creates an entry in the history of a referee when deleting a referee.
     *
     * @param int  $intSR
     * @param bool $deleted
     */
    public static function insertByDeleteSchiedsrichter($intSR, $deleted = true): void
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
     * Creates an entry in a referee's history when a member is deleted.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function insertByDeleteMember(DataContainer $dc, $undoId): void
    {
        if ($dc->__get('activeRecord')->refereeId) {
            static::insert($dc->__get('activeRecord')->refereeId, $dc->id, ['Login', 'REMOVE'], 'Der Login des Schiedsrichter %s mit dem Benutzernamen "%s" wurde gelöscht.', __METHOD__);
        }
    }

    /**
     * Creates an entry in a referee's history when activating or deactivating a login.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function insertByDisableMember($varValue, $dc)
    {
        $intSR = 0;
        $intMember = 0;

        if (isset($dc->__get('activeRecord')->refereeId)) {
            $intSR = $dc->__get('activeRecord')->refereeId;
            $intMember = $dc->id;
        } elseif ('schiedsrichter' === Input::get('do')) {
            if (Input::get('did')) {
                $intSR = Input::get('did');
            } elseif ('edit' === Input::get('act') && Input::get('id')) {
                $intSR = Input::get('id');
            }

            if ($intSR) {
                $member = Database::getInstance()->prepare('SELECT id FROM tl_member WHERE refereeId=?')->execute($intSR);

                if ($member->next()) {
                    $intMember = $member->__get('id');
                }
            }
        }

        if ($intSR) {
            static::insert($intSR, $intMember, ['Login', 'EDIT'], 'Der Login des Schiedsrichter %s mit dem Benutzernamen "%s" wurde '.($varValue ? 'deaktiviert' : 'aktiviert').'.', __METHOD__);
        }

        return $varValue;
    }

    /**
     * Creates an entry in a referee's history when unsubscribing newsletters from the frontend.
     *
     * @param string       $email    the recipient’s email address which has been removed
     * @param array<mixed> $channels the channels from which the recipient has unsubscribed
     */
    public function unsubscribeNewsletterToSRHistory($email, $channels): void
    {
        foreach ($channels as $channel) {
            $arrSR = Database::getInstance()->prepare('SELECT r.refereeId FROM tl_newsletter_recipients AS r, tl_bsa_referee AS sr WHERE r.refereeId=sr.id AND r.pid=? AND r.email=?')
                ->execute($channel, $email)
                ->fetchEach('refereeId')
            ;

            foreach ($arrSR as $sr) {
                static::insert($sr, $channel, ['E-Mail-Verteiler', 'REMOVE'], 'Der Schiedsrichter %s hat sich aus dem Verteiler "%s" ausgetragen.', __METHOD__);
            }
        }
    }
}
