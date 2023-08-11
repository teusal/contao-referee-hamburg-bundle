<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Newsletter;

use Contao\BackendUser;
use Contao\Database;
use Contao\DataContainer;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * Class BSANewsletter.
 */
class BSANewsletter extends System
{
    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(Database::class, 'Database');
        $this->import(BackendUser::class, 'User');
    }

    /**
     * synchronize data in neewsletter recipients of a referee.
     *
     * @param DataContainer $dc Data Container object
     */
    public function executeSubmitSchiedsrichter(DataContainer $dc): void
    {
        $this->synchronizeNewsletterBySchiedsrichter($dc->id);
    }

    /**
     * Synchronisiert die Einträge der Newsletterempfänger für einen Schiedsrichter.
     */
    public function synchronizeNewsletterBySchiedsrichter($intSR, $idsToRemove = null, $idsToAdd = null): void
    {
        $intSR = (int) $intSR;

        if (!\is_int($intSR) || 0 === $intSR) {
            throw new \Exception('wrong datatype or zero given :'.$intSR);
        }

        $objSR = RefereeModel::findReferee($intSR);

        if (!isset($objSR)) {
            throw new \Exception('Der Schiedsrichter zu ID '.$intSR.' wurde nicht in der Datenbank gefunden.');
        }

        // Die akutell benötigten Newsletter ermitteln
        $arrChannelsAndGroups = $this->getChannelsAndGroups($objSR->id, $idsToRemove, $idsToAdd);

        $email = $objSR->getFriendlyEmail();

        if ($objSR->deleted || !\strlen($email) || !\is_array($arrChannelsAndGroups) || empty($arrChannelsAndGroups)) {
            // Der Schiedsrichter hat keine E-Mailadresse oder bekommt über Gruppen keine Newsletter.
            // Also entfernen wir die Einträge aus allen Newslettern
            $arrDeletePids = $this->Database->prepare('SELECT pid FROM tl_newsletter_recipients WHERE refereeId=?')
                ->execute($objSR->id)
                ->fetchEach('pid')
            ;
            $res = $this->Database->prepare('DELETE FROM tl_newsletter_recipients WHERE refereeId=?')
                ->execute($objSR->id)
            ;

            if ($res->__get('affectedRows') > 0) {
                foreach ($arrDeletePids as $pid) {
                    SRHistory::insert($objSR->id, $pid, ['E-Mail-Verteiler', 'REMOVE'], 'Der Schiedsrichter %s wurde aus dem Verteiler "%s" entfernt.', __METHOD__);
                }
            }

            // Damit ist dann in diesem Fall auch alles erledigt.
            return;
        }

        // Alle bestehenden Eintragungen der Empfänger löschen, die nicht in den aktuellen Newsletterkanälen sind
        $strWhere = 'refereeId='.$objSR->id.' AND pid NOT IN ('.implode(',', array_keys($arrChannelsAndGroups)).') AND groups IS NOT NULL';
        $arrRemovePids = $this->Database->execute('SELECT pid FROM tl_newsletter_recipients WHERE '.$strWhere)
            ->fetchEach('pid')
        ;

        if (\count($arrRemovePids) > 0) {
            $res = $this->Database->execute('DELETE FROM tl_newsletter_recipients WHERE '.$strWhere);

            foreach ($arrRemovePids as $pid) {
                SRHistory::insert($objSR->id, $pid, ['E-Mail-Verteiler', 'REMOVE'], 'Der Schiedsrichter %s wurde aus dem Verteiler "%s" entfernt.', __METHOD__);
            }
        }

        foreach ($arrChannelsAndGroups as $channelId => $arrGroupIds) {
            $objRecipients = $this->Database->prepare('SELECT id FROM tl_newsletter_recipients WHERE pid=? AND refereeId=?')
                ->execute($channelId, $objSR->id)
            ;

            if (0 === $objRecipients->numRows) {
                // Ein zweiter Versuch des Ladens ohne SR-ID, damit Einträge gefunden werden, die zuvor ohne ID eingetragen wurden
                $objRecipients = $this->Database->prepare('SELECT id FROM tl_newsletter_recipients WHERE pid=? AND email=? AND refereeId=?')
                    ->execute($channelId, $email, null)
                ;
            }

            if (0 === $objRecipients->numRows) {
                // bisher kein Eintrag... Also einen neuen Datensatz anlegen
                $res = $this->Database->prepare('INSERT INTO tl_newsletter_recipients (pid,tstamp,email,active,addedOn,refereeId,groups) VALUES (?,?,?,?,?,?,?)')
                    ->execute($channelId, time(), $email, '1', time(), $objSR->id, serialize($arrGroupIds))
                ;
                SRHistory::insert($objSR->id, $channelId, ['E-Mail-Verteiler', 'ADD'], 'Der Schiedsrichter %s wurde zum Verteiler "%s" hinzugefügt.', __METHOD__);
            } else {
                $isUpdated = false;
                // bestehenden Datensatz aktualisieren...
                $res = $this->Database->prepare('UPDATE tl_newsletter_recipients SET email=? WHERE id=?')
                    ->execute($email, $objRecipients->__get('id'))
                ;

                if ($res->__get('affectedRows') > 0) {
                    $isUpdated = true;
                    SRHistory::insert($objSR->id, null, ['E-Mail-Verteiler', 'CHANGE'], 'Die E-Mail-Adresse des Schiedsrichters %s wurde im Verteiler "%s" aktualisiert.', __METHOD__);
                }

                // bestehenden Datensatz aktualisieren...
                $res = $this->Database->prepare('UPDATE tl_newsletter_recipients SET refereeId=?, groups=? WHERE id=?')
                    ->execute($objSR->id, serialize($arrGroupIds), $objRecipients->__get('id'))
                ;

                if ($res->__get('affectedRows') > 0) {
                    $isUpdated = true;
                }

                // Timestamp setzen, wenn es eine Änderung gab
                if ($isUpdated) {
                    $res = $this->Database->prepare('UPDATE tl_newsletter_recipients SET tstamp=? WHERE pid=? AND refereeId=?')
                        ->execute(time(), $channelId, $objSR->id)
                    ;
                }
            }
        }
    }

    /**
     * Löscht alle Einträge zu einer Gruppe aus der Newsletter-Empfängerliste.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function deleteGruppe(DataContainer $dc, $undoId): void
    {
        $arrSR = $this->getSchiedsrichterByGroup($dc->id);

        foreach ($arrSR as $intSR) {
            $this->synchronizeNewsletterBySchiedsrichter($intSR, $dc->id, null);
        }
    }

    /**
     * Löscht den Schiedsrichter aus der Empfängerliste beim Entfernen eines Gruppenmitgliedes.
     *
     * @param int $undoId
     */
    public function deleteGruppenmitglied(DataContainer $dc, $undoId): void
    {
        if ((int) ($dc->__get('activeRecord')->refereeId)) {
            $this->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid, null);
        }
    }

    /**
     * Löscht den Schiedsrichter aus der Empfängerliste beim Entfernen eines Gruppenmitgliedes.
     *
     * @param DataContainer $dc     Data Container object
     * @param int           $undoId The ID of the tl_undo database record
     */
    public function deleteNewsletterzuordnung(DataContainer $dc, $undoId): void
    {
        $toDelete = [
            'newsletter_channel_id' => $dc->__get('activeRecord')->newsletterChannelId,
            'member_group_id' => $dc->__get('activeRecord')->pid,
        ];
        $arrSR = $this->getSchiedsrichterByGroup($dc->__get('activeRecord')->pid);

        foreach ($arrSR as $intSR) {
            $this->synchronizeNewsletterBySchiedsrichter($intSR, $toDelete, null);
        }
    }

    /**
     * Verwaltet die Änderung eines Schiedsrichters beim Anlegen oder Ändern eines Gruppenmitglieds.
     *
     * @param mixed         $varValue Value to be saved
     * @param DataContainer $dc       Data Container object
     *
     * @return mixed
     */
    public function saveSchiedsrichterWhileUpdateGruppenmitglied($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->refereeId) {
            if ((int) $varValue) {
                $this->synchronizeNewsletterBySchiedsrichter($varValue, null, $dc->__get('activeRecord')->pid);
            }

            if ((int) ($dc->__get('activeRecord')->refereeId)) {
                $this->synchronizeNewsletterBySchiedsrichter($dc->__get('activeRecord')->refereeId, $dc->__get('activeRecord')->pid, null);
            }
        }

        return $varValue;
    }

    /**
     * Verwaltet die Änderung einer Newsletterzuordnung einer Gruppe.
     *
     * @param mixed $varValue
     *
     * @return mixed
     */
    public function saveNewsletterzuordnung($varValue, DataContainer $dc)
    {
        if ($varValue !== $dc->__get('activeRecord')->newsletterChannelId) {
            $toDelete = [
                'newsletter_channel_id' => $dc->__get('activeRecord')->newsletterChannelId,
                'member_group_id' => $dc->__get('activeRecord')->pid,
            ];
            $toAdd = [
                'newsletter_channel_id' => (int) $varValue,
                'member_group_id' => $dc->__get('activeRecord')->pid,
            ];

            $arrSR = $this->getSchiedsrichterByGroup($dc->__get('activeRecord')->pid);

            foreach ($arrSR as $intSR) {
                $this->synchronizeNewsletterBySchiedsrichter($intSR, $toDelete, $toAdd);
            }
        }

        return $varValue;
    }

    /**
     * Liefert die Liste der Notwendigen Newsletterkanäle und die dafür verantwortlichen Gruppen.
     *
     * @param int        $intSR
     * @param array|null $idsToRemove
     * @param array|null $idsToAdd
     */
    private function getChannelsAndGroups($intSR, $idsToRemove, $idsToAdd)
    {
        $arrGroupIds = $this->Database->prepare('SELECT pid FROM tl_bsa_member_group_member_assignment WHERE refereeId=?')
            ->execute($intSR)
            ->fetchEach('pid')
        ;

        if (!\is_array($arrGroupIds) || empty($arrGroupIds)) {
            return [];
        }

        if (isset($idsToRemove) && !\is_array($idsToRemove) && \in_array($idsToRemove, $arrGroupIds, true)) {
            unset($arrGroupIds[array_search($idsToRemove, $arrGroupIds, true)]);
        }

        if (isset($idsToAdd) && !\is_array($idsToAdd) && !\in_array($idsToAdd, $arrGroupIds, true)) {
            $arrGroupIds[] = $idsToAdd;
        }

        $arrChannelsAndGroups = [];

        if (!empty($arrGroupIds)) {
            $arrChannelIds = $this->Database->execute('SELECT tl_bsa_member_group_newsletter_assignment.newsletterChannelId, tl_bsa_member_group_newsletter_assignment.pid AS member_group FROM tl_bsa_member_group_newsletter_assignment, tl_newsletter_channel WHERE tl_bsa_member_group_newsletter_assignment.newsletterChannelId=tl_newsletter_channel.id AND tl_bsa_member_group_newsletter_assignment.pid IN ('.implode(',', $arrGroupIds).')')
                ->fetchAllAssoc()
            ;

            foreach ($arrChannelIds as $row) {
                $arrChannelsAndGroups[$row['newsletterChannelId']][] = $row['member_group'];
            }
        }

        if (isset($idsToRemove) && \is_array($idsToRemove) && \array_key_exists($idsToRemove['newsletter_channel_id'], $arrChannelsAndGroups)) {
            unset($arrChannelsAndGroups[$idsToRemove['newsletter_channel_id']][array_search($idsToRemove['member_group_id'], $arrChannelsAndGroups[$idsToRemove['newsletter_channel_id']], true)]);

            if (empty($arrChannelsAndGroups[$idsToRemove['newsletter_channel_id']])) {
                unset($arrChannelsAndGroups[$idsToRemove['newsletter_channel_id']]);
            }
        }

        if (isset($idsToAdd) && \is_array($idsToAdd) && $idsToAdd['member_group_id'] && (!\array_key_exists($idsToAdd['newsletter_channel_id'], $arrChannelsAndGroups) || !\in_array($idsToAdd['member_group_id'], $arrChannelsAndGroups[$idsToAdd['newsletter_channel_id']], true))) {
            $arrChannelsAndGroups[$idsToAdd['newsletter_channel_id']][] = $idsToAdd['member_group_id'];
        }

        return $arrChannelsAndGroups;
    }

    /**
     * Liefert die Schiedsrichter einer Gruppe.
     */
    private function getSchiedsrichterByGroup($intGroup)
    {
        return $this->Database->prepare('SELECT refereeId FROM tl_bsa_member_group_member_assignment WHERE pid=?')
            ->execute($intGroup)
            ->fetchEach('refereeId')
        ;
    }
}
