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

use Contao\BackendUser;
use Contao\Database;
use Contao\MemberGroupModel;
use Contao\Message;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaGruppenmitgliederModel;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaSchiedsrichterModel;
use Teusal\ContaoRefereeHamburgBundle\Model\BsaVereinObmannModel;

/**
 * Class BSAMemberGroup.
 */
class BSAMemberGroup extends System
{
    // temporäre Liste für neu angelegte Gruppen
    public $cachedGroups = [];

    /**
     * Konstruktor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->import(Database::class);
        $this->import(BackendUser::class, 'User');
    }

    /**
     * takes care for automatic groups. adds or removes the sr from the automatic groups.
     *
     * @param int $intSR
     */
    public function handleAutomaticGroups($intSR): void
    {
        $intSR = (int) $intSR;

        if (!\is_int($intSR) || 0 === $intSR) {
            throw new \Exception('wrong datatype or zero given');
        }

        $objSR = BsaSchiedsrichterModel::findSchiedsrichter($intSR);

        // zuerst für gelöschte SR alle automatischen Gruppen entfernen
        if (!isset($objSR) || $objSR->__get('deleted')) {
            $this->deleteFromGroup($intSR, 'alle');
            $this->deleteFromGroup($intSR, 'alle_sr');
            $this->deleteFromGroup($intSR, 'obleute');
            $this->deleteFromGroup($intSR, 'aktive');
            $this->deleteFromGroup($intSR, 'passive');
            $this->deleteFromGroup($intSR, 'U18');
            $this->deleteFromGroup($intSR, 'Ü40');
            $this->deleteFromGroup($intSR, 'm');
            $this->deleteFromGroup($intSR, 'w');
            // Gruppen-IDs an das Mitglied schreiben
            $this->setGroupsToMember($intSR);

            return;
        }

        $isVereinsschiedsrichter = BsaSchiedsrichterModel::isVereinsschiedsrichter($intSR);
        $isVereinsobmann = BsaVereinObmannModel::isVereinsobmann($intSR);

        // Schiedsrichter zur Gruppe 'Alle Personen' hinzufügen
        if ($isVereinsschiedsrichter || $isVereinsobmann) {
            $this->addToGroup($intSR, 'alle');
        } else {
            $this->deleteFromGroup($intSR, 'alle');
        }

        // Schiedsrichter zur Gruppe 'Alle Schiedsrichter' hinzufügen
        if ($isVereinsschiedsrichter) {
            $this->addToGroup($intSR, 'alle_sr');
        } else {
            $this->deleteFromGroup($intSR, 'alle_sr');
        }

        // Schiedsrichter zur Gruppe 'Obleute' hinzufügen
        if ($isVereinsobmann) {
            $this->addToGroup($intSR, 'obleute');
        } else {
            $this->deleteFromGroup($intSR, 'obleute');
        }

        if (!$isVereinsschiedsrichter) {
            $this->deleteFromGroup($intSR, 'aktive');
            $this->deleteFromGroup($intSR, 'passive');
            $this->deleteFromGroup($intSR, 'U18');
            $this->deleteFromGroup($intSR, 'Ü40');
            $this->deleteFromGroup($intSR, 'm');
            $this->deleteFromGroup($intSR, 'w');
        } elseif ('passiv' === $objSR->__get('status')) {
            $this->deleteFromGroup($intSR, 'aktive');
            $this->addToGroup($intSR, 'passive');
            $this->deleteFromGroup($intSR, 'U18');
            $this->deleteFromGroup($intSR, 'Ü40');
            $this->deleteFromGroup($intSR, 'm');
            $this->deleteFromGroup($intSR, 'w');
        } elseif ('aktiv' === $objSR->__get('status')) {
            // Hinzufügen zu aktiven SR und entfernen aus den passiven
            $this->addToGroup($intSR, 'aktive');
            $this->deleteFromGroup($intSR, 'passive');

            // Ermittlung des Alters und Sortierung in die Gruppen U18 oder Ü40
            $srAlter = BsaSchiedsrichterModel::getAlter($objSR);

            if ($srAlter < 18) {
                $this->addToGroup($intSR, 'U18');
                $this->deleteFromGroup($intSR, 'Ü40');
            } elseif ($srAlter >= 40) {
                $this->deleteFromGroup($intSR, 'U18');
                $this->addToGroup($intSR, 'Ü40');
            } else {
                $this->deleteFromGroup($intSR, 'U18');
                $this->deleteFromGroup($intSR, 'Ü40');
            }

            // Aufteilung in männlich/weiblich
            if ('m' === $objSR->__get('geschlecht') && $isVereinsschiedsrichter) {
                $this->addToGroup($intSR, 'm');
                $this->deleteFromGroup($intSR, 'w');
            } elseif ('w' === $objSR->__get('geschlecht') && $isVereinsschiedsrichter) {
                $this->deleteFromGroup($intSR, 'm');
                $this->addToGroup($intSR, 'w');
            } else {
                $this->deleteFromGroup($intSR, 'm');
                $this->deleteFromGroup($intSR, 'w');
            }
        } else {
            $this->deleteFromGroup($intSR, 'aktive');
            $this->deleteFromGroup($intSR, 'passive');
            $this->deleteFromGroup($intSR, 'U18');
            $this->deleteFromGroup($intSR, 'Ü40');
            $this->deleteFromGroup($intSR, 'm');
            $this->deleteFromGroup($intSR, 'w');
        }

        // Gruppen-IDs an das Mitglied schreiben
        $this->setGroupsToMember($intSR);
    }

    /**
     * takes care for members in a 'halbautomatic' group.
     *
     * @return bool true if at least one change was done
     */
    public function updateHalbautomaticGroup(MemberGroupModel $group)
    {
        if (!isset($group)) {
            throw new \Exception('no group');
        }

        if (!static::isHalbautomatic($group->__get('automatik'))) {
            throw new \Exception('not a halbautomatic group');
        }

        $arrParams = [];

        switch ($group->__get('automatik')) {
            case '10_jahre':
            case '25_jahre':
            case '40_jahre':
            case '50_jahre':
            case '60_jahre':
            case '70_jahre':
                $query = 'SELECT id FROM tl_bsa_schiedsrichter WHERE YEAR(CURDATE()) - YEAR(sr_seit_date) = ? AND deleted = ?';
                $arrParams[] = (int) (substr($group->__get('automatik'), 0, 2));
                $arrParams[] = false;
                break;

            case 'ohne_sitzung':
                $query = 'SELECT id FROM tl_bsa_schiedsrichter ';
                $query .= 'WHERE id NOT IN (';
                $query .= '    SELECT t.sr_id FROM tl_bsa_veranstaltung AS v, tl_bsa_teilnehmer AS t, tl_bsa_saison AS s ';
                $query .= '    WHERE v.id = t.pid AND v.saison = s.id ';
                $query .= '    AND v.veranstaltungsgruppe = ? AND s.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND id IN (';
                $query .= '    SELECT t.sr_id FROM tl_bsa_veranstaltung AS v, tl_bsa_teilnehmer AS t, tl_bsa_saison AS s ';
                $query .= '    WHERE v.id = t.pid AND v.saison = s.id ';
                $query .= '    AND v.veranstaltungsgruppe = ? AND s.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND status = ? AND deleted = ? ';
                $arrParams[] = 'sitzung';
                $arrParams[] = true;
                $arrParams[] = 'regelarbeit';
                $arrParams[] = true;
                $arrParams[] = 'aktiv';
                $arrParams[] = false;
                break;

            case 'ohne_regelarbeit':
                $query = 'SELECT id FROM tl_bsa_schiedsrichter ';
                $query .= 'WHERE id IN (';
                $query .= '    SELECT t.sr_id FROM tl_bsa_veranstaltung AS v, tl_bsa_teilnehmer AS t, tl_bsa_saison AS s ';
                $query .= '    WHERE v.id = t.pid AND v.saison = s.id ';
                $query .= '    AND v.veranstaltungsgruppe = ? AND s.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND id NOT IN (';
                $query .= '    SELECT t.sr_id FROM tl_bsa_veranstaltung AS v, tl_bsa_teilnehmer AS t, tl_bsa_saison AS s ';
                $query .= '    WHERE v.id = t.pid AND v.saison = s.id ';
                $query .= '    AND v.veranstaltungsgruppe = ? AND s.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND status = ? AND deleted = ? ';
                $arrParams[] = 'sitzung';
                $arrParams[] = true;
                $arrParams[] = 'regelarbeit';
                $arrParams[] = true;
                $arrParams[] = 'aktiv';
                $arrParams[] = false;
                break;

            case 'ohne_sitzung_regelarbeit':
                $query = 'SELECT id FROM tl_bsa_schiedsrichter ';
                $query .= 'WHERE id NOT IN (';
                $query .= '    SELECT t.sr_id FROM tl_bsa_veranstaltung AS v, tl_bsa_teilnehmer AS t, tl_bsa_saison AS s ';
                $query .= '    WHERE v.id = t.pid AND v.saison = s.id ';
                $query .= '    AND v.veranstaltungsgruppe = ? AND s.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND id NOT IN (';
                $query .= '    SELECT t.sr_id FROM tl_bsa_veranstaltung AS v, tl_bsa_teilnehmer AS t, tl_bsa_saison AS s ';
                $query .= '    WHERE v.id = t.pid AND v.saison = s.id ';
                $query .= '    AND v.veranstaltungsgruppe = ? AND s.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND status = ? AND deleted = ? ';
                $arrParams[] = 'sitzung';
                $arrParams[] = true;
                $arrParams[] = 'regelarbeit';
                $arrParams[] = true;
                $arrParams[] = 'aktiv';
                $arrParams[] = false;
                break;

            default:
                throw new \Exception('unknown halbautomatic '.$group->__get('automatik'));
        }

        $arrFutureSR = $this->Database->prepare($query)
            ->execute($arrParams)
            ->fetchEach('id')
        ;

        $arrExistingSR = $this->Database->prepare('SELECT schiedsrichter FROM tl_bsa_gruppenmitglieder WHERE pid=?')
            ->execute($group->id)
            ->fetchEach('schiedsrichter')
        ;

        $toDel = array_diff($arrExistingSR, $arrFutureSR);
        $toAdd = array_diff($arrFutureSR, $arrExistingSR);
        $toKeep = array_intersect($arrFutureSR, $arrExistingSR);

        if (empty($toDel) && empty($toAdd)) {
            Message::addInfo('Es gibt keine Veränderungen.');

            return false;
        }

        if (!empty($toDel)) {
            foreach ($toDel as $intSR) {
                $this->Database->prepare('DELETE FROM tl_bsa_gruppenmitglieder WHERE pid=? AND schiedsrichter=?')
                    ->execute($group->id, $intSR)
                ;

                $this->setGroupsToMember($intSR);

                SRHistory::insert($intSR, $group->id, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }
            Message::addConfirmation(\count($toDel).' Schiedsrichter*in(nen) wurde(n) aus dem/der Kader/Gruppe entfernt.');
        }

        if (!empty($toKeep)) {
            Message::addConfirmation(\count($toKeep).' Schiedsrichter*in(nen) wurde(n) unverändert in dem/der Kader/Gruppe belassen.');
        }

        if (!empty($toAdd)) {
            foreach ($toAdd as $intSR) {
                $this->Database->prepare('INSERT INTO tl_bsa_gruppenmitglieder (pid, tstamp, schiedsrichter) VALUES (?,?,?)')
                    ->execute($group->id, time(), $intSR)
                ;

                $this->setGroupsToMember($intSR);

                SRHistory::insert($intSR, $group->id, ['Kader/Gruppe', 'ADD'], 'Der Schiedsrichter %s wurde in der Gruppe "%s" aufgenommen.', __METHOD__);
            }
            Message::addConfirmation(\count($toAdd).' Schiedsrichter*in(nen) wurde(n) dem/der Kader/Gruppe hinzugefügt.');
        }

        return true;
    }

    /**
     * Löscht einen Schiedsrichter aus allen Gruppen.
     */
    public function deleteFromGroups($intSR): void
    {
        $intSR = (int) $intSR;

        if (!\is_int($intSR) || 0 === $intSR) {
            throw new \Exception('wrong datatype or zero given');
        }

        $arrGroupIds = $this->Database->prepare('SELECT pid FROM tl_bsa_gruppenmitglieder WHERE schiedsrichter=?')
            ->execute($intSR)
            ->fetchEach('pid')
        ;
        $res = $this->Database->prepare('DELETE FROM tl_bsa_gruppenmitglieder WHERE schiedsrichter=?')
            ->execute($intSR)
        ;

        if ($res->__get('affectedRows') > 0) {
            foreach ($arrGroupIds as $pid) {
                SRHistory::insert($intSR, $pid, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }
        }

        // Gruppen-IDs an das Mitglied schreiben
        $this->setGroupsToMember($intSR);
    }

    /**
     * Ermittelt die Gruppen-IDs eines Schiedsrichters und setzt die Liste der Gruppen am Login tl_member.
     */
    public function setGroupsToMember($intSR, $idToRemove = 0, $idToAdd = 0): void
    {
        $intSR = (int) $intSR;

        if (0 === $intSR) {
            return;
        }

        $query = 'SELECT DISTINCT tl_member_group.id FROM tl_member_group JOIN tl_bsa_gruppenmitglieder ON tl_member_group.id=tl_bsa_gruppenmitglieder.pid WHERE tl_bsa_gruppenmitglieder.schiedsrichter=?';

        if ($idToRemove) {
            $query .= ' AND tl_member_group.id!='.$idToRemove;
        } elseif ($idToAdd) {
            $query .= ' OR tl_member_group.id='.$idToAdd;
        }
        $query .= ' ORDER BY tl_member_group.name';

        $arrGroupIds = $this->Database->prepare($query)
            ->execute($intSR)
            ->fetchEach('id')
        ;

        $this->Database->prepare('UPDATE tl_member SET groups=? WHERE schiedsrichter=?')
            ->execute(serialize($arrGroupIds), $intSR)
        ;
    }

    /**
     * Fügt einen SR in die Gruppe Obleute ein.
     */
    public function addToObleute($intSR): void
    {
        if (0 === $intSR) {
            return;
        }
        $this->addToGroup($intSR, 'obleute');
    }

    /**
     * Entfernt einen SR aus der Gruppe Obleute.
     */
    public function removeFromObleute($intSR): void
    {
        if (0 === $intSR) {
            return;
        }
        $this->deleteFromGroup($intSR, 'obleute');
    }

    /**
     * Updating automatic newsletters on birthday.
     */
    public function updateOnBirthday(): void
    {
        $arrSR = BsaSchiedsrichterModel::getPersonWithBirthdayToday();

        if (empty($arrSR)) {
            System::log('BSA-Gruppenverwaltung wurde ausgeführt, es sind keine Gruppen an Geburtstagen zu aktualisieren.', 'BSA Gruppenverwaltung updateOnBirthday()', TL_CRON);

            return;
        }

        foreach ($arrSR as $sr) {
            $this->handleAutomaticGroups($sr);
        }
        System::log('BSA-Gruppenverwaltung wurde ausgeführt, die Gruppen von '.\count($arrSR).' Geburtstagskind(ern) wurde(n) aktualisiert.', 'BSA Gruppenverwaltung updateOnBirthday()', TL_CRON);
    }

    /**
     * tells you if an automatic is an 'halbautomatic' or not.
     *
     * @param mixed $automatic
     */
    public static function isHalbautomatic($automatic): bool
    {
        return isset($automatic)
            && \strlen($automatic)
            && \in_array(
                $automatic,
                $GLOBALS['TL_DCA']['tl_member_group']['fields']['automatik']['options']['halbautomatik'],
                true
            );
    }

    /**
     * Fügt einen Schiedsrichter in eine Automatikgruppe ein.
     */
    private function addToGroup($intSR, $automaticKey): void
    {
        if (!\strlen($automaticKey)) {
            return;
        }

        $intGroup = $this->getAutomatikGroupId($automaticKey);

        if ($intGroup) {
            if (!BsaGruppenmitgliederModel::exists($intGroup, $intSR)) {
                $this->Database->prepare('INSERT INTO tl_bsa_gruppenmitglieder (pid, tstamp, schiedsrichter) VALUES (?,?,?)')
                    ->execute($intGroup, time(), $intSR)
                ;

                SRHistory::insert($intSR, $intGroup, ['Kader/Gruppe', 'ADD'], 'Der Schiedsrichter %s wurde in der Gruppe "%s" aufgenommen.', __METHOD__);
            }
        }
    }

    /**
     * Löscht einen Schiedsrichter aus einer Automatikgruppe.
     */
    private function deleteFromGroup($intSR, $automaticKey): void
    {
        if (!\strlen($automaticKey)) {
            return;
        }

        $intGroup = $this->getAutomatikGroupId($automaticKey);

        if ($intGroup) {
            $res = $this->Database->prepare('DELETE FROM tl_bsa_gruppenmitglieder WHERE pid=? AND schiedsrichter=?')
                ->execute($intGroup, $intSR)
            ;

            if ($res->__get('affectedRows') > 0) {
                SRHistory::insert($intSR, $intGroup, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }
        }
    }

    /**
     * Liefert die ID einer Gruppe.
     */
    private function getAutomatikGroupId($automaticKey)
    {
        $intGroup = $this->cachedGroups[$automaticKey];

        if (!$intGroup) {
            $objGroup = MemberGroupModel::findOneBy('automatik', $automaticKey);

            if (isset($objGroup)) {
                $this->cachedGroups[$automaticKey] = $objGroup->id;
                $intGroup = $objGroup->id;
            }
        }

        return $intGroup;
    }
}
