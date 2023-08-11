<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Library\Member;

use Contao\Database;
use Contao\DataContainer;
use Contao\MemberGroupModel;
use Contao\Message;
use Contao\System;
use Teusal\ContaoRefereeHamburgBundle\Library\SRHistory;
use Teusal\ContaoRefereeHamburgBundle\Model\ClubChairmanModel;
use Teusal\ContaoRefereeHamburgBundle\Model\MemberGroupAssignmentMemberModel;
use Teusal\ContaoRefereeHamburgBundle\Model\RefereeModel;

/**
 * Class BSAMemberGroup.
 */
final class BSAMemberGroup extends System
{
    private const AUTOMATICS = [
        'vollautomatik' => [
            'alle',
            'alle_sr',
            'obleute',
            'U18',
            'Ü40',
            'm',
            'w',
            'aktive',
            'passive',
        ],
        'halbautomatik' => [
            '10_jahre',
            '25_jahre',
            '40_jahre',
            '50_jahre',
            '60_jahre',
            '70_jahre',
            'ohne_sitzung',
            'ohne_regelarbeit',
            'ohne_sitzung_regelarbeit',
        ],
    ];

    // temporäre Liste für neu angelegte Gruppen
    private static $cachedGroups = [];

    /**
     * returns all available options.
     *
     * @param DataContainer|null $dc Data Container object or null
     *
     * @return array available options
     */
    public function getAllAutomaticOptions(DataContainer $dc): array
    {
        return self::AUTOMATICS;
    }

    /**
     * takes care for automatic groups. adds or removes the referee from the automatic groups.
     *
     * @param int $intSR
     */
    public static function handleAutomaticGroups($intSR): void
    {
        $intSR = (int) $intSR;

        if (!\is_int($intSR) || 0 === $intSR) {
            throw new \Exception('wrong datatype or zero given');
        }

        $objSR = RefereeModel::findReferee($intSR);

        // zuerst für gelöschte SR alle automatischen Gruppen entfernen
        if (!isset($objSR) || $objSR->__get('deleted')) {
            self::deleteFromGroup($intSR, 'alle');
            self::deleteFromGroup($intSR, 'alle_sr');
            self::deleteFromGroup($intSR, 'obleute');
            self::deleteFromGroup($intSR, 'aktive');
            self::deleteFromGroup($intSR, 'passive');
            self::deleteFromGroup($intSR, 'U18');
            self::deleteFromGroup($intSR, 'Ü40');
            self::deleteFromGroup($intSR, 'm');
            self::deleteFromGroup($intSR, 'w');
            // Gruppen-IDs an das Mitglied schreiben
            self::setGroupsToMember($intSR);

            return;
        }

        $isClubReferee = RefereeModel::isClubReferee($intSR);
        $isChairman = ClubChairmanModel::isChairman($intSR);

        // Schiedsrichter zur Gruppe 'Alle Personen' hinzufügen
        if ($isClubReferee || $isChairman) {
            self::addToGroup($intSR, 'alle');
        } else {
            self::deleteFromGroup($intSR, 'alle');
        }

        // Schiedsrichter zur Gruppe 'Alle Schiedsrichter' hinzufügen
        if ($isClubReferee) {
            self::addToGroup($intSR, 'alle_sr');
        } else {
            self::deleteFromGroup($intSR, 'alle_sr');
        }

        // Schiedsrichter zur Gruppe 'Obleute' hinzufügen
        if ($isChairman) {
            self::addToGroup($intSR, 'obleute');
        } else {
            self::deleteFromGroup($intSR, 'obleute');
        }

        if (!$isClubReferee) {
            self::deleteFromGroup($intSR, 'aktive');
            self::deleteFromGroup($intSR, 'passive');
            self::deleteFromGroup($intSR, 'U18');
            self::deleteFromGroup($intSR, 'Ü40');
            self::deleteFromGroup($intSR, 'm');
            self::deleteFromGroup($intSR, 'w');
        } elseif ('passiv' === $objSR->__get('state')) {
            self::deleteFromGroup($intSR, 'aktive');
            self::addToGroup($intSR, 'passive');
            self::deleteFromGroup($intSR, 'U18');
            self::deleteFromGroup($intSR, 'Ü40');
            self::deleteFromGroup($intSR, 'm');
            self::deleteFromGroup($intSR, 'w');
        } elseif ('aktiv' === $objSR->__get('state')) {
            // Hinzufügen zu aktiven SR und entfernen aus den passiven
            self::addToGroup($intSR, 'aktive');
            self::deleteFromGroup($intSR, 'passive');

            // Ermittlung des Alters und Sortierung in die Gruppen U18 oder Ü40
            $srAlter = RefereeModel::getAge($objSR);

            if ($srAlter < 18) {
                self::addToGroup($intSR, 'U18');
                self::deleteFromGroup($intSR, 'Ü40');
            } elseif ($srAlter >= 40) {
                self::deleteFromGroup($intSR, 'U18');
                self::addToGroup($intSR, 'Ü40');
            } else {
                self::deleteFromGroup($intSR, 'U18');
                self::deleteFromGroup($intSR, 'Ü40');
            }

            // Aufteilung in männlich/weiblich
            if ('m' === $objSR->__get('gender') && $isClubReferee) {
                self::addToGroup($intSR, 'm');
                self::deleteFromGroup($intSR, 'w');
            } elseif ('w' === $objSR->__get('gender') && $isClubReferee) {
                self::deleteFromGroup($intSR, 'm');
                self::addToGroup($intSR, 'w');
            } else {
                self::deleteFromGroup($intSR, 'm');
                self::deleteFromGroup($intSR, 'w');
            }
        } else {
            self::deleteFromGroup($intSR, 'aktive');
            self::deleteFromGroup($intSR, 'passive');
            self::deleteFromGroup($intSR, 'U18');
            self::deleteFromGroup($intSR, 'Ü40');
            self::deleteFromGroup($intSR, 'm');
            self::deleteFromGroup($intSR, 'w');
        }

        // Gruppen-IDs an das Mitglied schreiben
        self::setGroupsToMember($intSR);
    }

    /**
     * takes care for members in a 'halbautomatic' group.
     *
     * @return bool true if at least one change was done
     */
    public static function updateHalbautomaticGroup(MemberGroupModel $group)
    {
        if (!isset($group)) {
            throw new \Exception('no group');
        }

        if (!self::isHalbautomatic($group->__get('automatik'))) {
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
                $query = 'SELECT id FROM tl_bsa_referee WHERE YEAR(CURDATE()) - YEAR(dateOfRefereeExaminationAsDate) = ? AND deleted = ?';
                $arrParams[] = (int) (substr($group->__get('automatik'), 0, 2));
                $arrParams[] = false;
                break;

            case 'ohne_sitzung':
                $query = 'SELECT id FROM tl_bsa_referee ';
                $query .= 'WHERE id NOT IN (';
                $query .= '    SELECT participiant.refereeId FROM tl_bsa_event AS event, tl_bsa_event_participiant AS participiant, tl_bsa_season AS season ';
                $query .= '    WHERE event.id = participiant.pid AND event.seasonId = season.id ';
                $query .= '    AND event.eventGroup = ? AND season.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND id IN (';
                $query .= '    SELECT participiant.refereeId FROM tl_bsa_event AS event, tl_bsa_event_participiant AS participiant, tl_bsa_season AS season ';
                $query .= '    WHERE event.id = participiant.pid AND event.seasonId = season.id ';
                $query .= '    AND event.eventGroup = ? AND season.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND state = ? AND deleted = ? ';
                $arrParams[] = 'sitzung';
                $arrParams[] = true;
                $arrParams[] = 'regelarbeit';
                $arrParams[] = true;
                $arrParams[] = 'aktiv';
                $arrParams[] = false;
                break;

            case 'ohne_regelarbeit':
                $query = 'SELECT id FROM tl_bsa_referee ';
                $query .= 'WHERE id IN (';
                $query .= '    SELECT participiant.refereeId FROM tl_bsa_event AS event, tl_bsa_event_participiant AS participiant, tl_bsa_season AS season ';
                $query .= '    WHERE event.id = participiant.pid AND event.seasonId = season.id ';
                $query .= '    AND event.eventGroup = ? AND season.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND id NOT IN (';
                $query .= '    SELECT participiant.refereeId FROM tl_bsa_event AS event, tl_bsa_event_participiant AS participiant, tl_bsa_season AS season ';
                $query .= '    WHERE event.id = participiant.pid AND event.seasonId = season.id ';
                $query .= '    AND event.eventGroup = ? AND season.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND state = ? AND deleted = ? ';
                $arrParams[] = 'sitzung';
                $arrParams[] = true;
                $arrParams[] = 'regelarbeit';
                $arrParams[] = true;
                $arrParams[] = 'aktiv';
                $arrParams[] = false;
                break;

            case 'ohne_sitzung_regelarbeit':
                $query = 'SELECT id FROM tl_bsa_referee ';
                $query .= 'WHERE id NOT IN (';
                $query .= '    SELECT participiant.refereeId FROM tl_bsa_event AS event, tl_bsa_event_participiant AS participiant, tl_bsa_season AS season ';
                $query .= '    WHERE event.id = participiant.pid AND event.seasonId = season.id ';
                $query .= '    AND event.eventGroup = ? AND season.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND id NOT IN (';
                $query .= '    SELECT participiant.refereeId FROM tl_bsa_event AS event, tl_bsa_event_participiant AS participiant, tl_bsa_season AS season ';
                $query .= '    WHERE event.id = participiant.pid AND event.seasonId = season.id ';
                $query .= '    AND event.eventGroup = ? AND season.aktiv = ? ';
                $query .= ') ';
                $query .= 'AND state = ? AND deleted = ? ';
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

        $arrFutureSR = Database::getInstance()->prepare($query)
            ->execute($arrParams)
            ->fetchEach('id')
        ;

        $arrExistingSR = Database::getInstance()->prepare('SELECT refereeId FROM tl_bsa_member_group_member_assignment WHERE pid=?')
            ->execute($group->id)
            ->fetchEach('refereeId')
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
                Database::getInstance()->prepare('DELETE FROM tl_bsa_member_group_member_assignment WHERE pid=? AND refereeId=?')
                    ->execute($group->id, $intSR)
                ;

                self::setGroupsToMember($intSR);

                SRHistory::insert($intSR, $group->id, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }
            Message::addConfirmation(\count($toDel).' Schiedsrichter*in(nen) wurde(n) aus dem/der Kader/Gruppe entfernt.');
        }

        if (!empty($toKeep)) {
            Message::addConfirmation(\count($toKeep).' Schiedsrichter*in(nen) wurde(n) unverändert in dem/der Kader/Gruppe belassen.');
        }

        if (!empty($toAdd)) {
            foreach ($toAdd as $intSR) {
                Database::getInstance()->prepare('INSERT INTO tl_bsa_member_group_member_assignment (pid, tstamp, refereeId) VALUES (?,?,?)')
                    ->execute($group->id, time(), $intSR)
                ;

                self::setGroupsToMember($intSR);

                SRHistory::insert($intSR, $group->id, ['Kader/Gruppe', 'ADD'], 'Der Schiedsrichter %s wurde in der Gruppe "%s" aufgenommen.', __METHOD__);
            }
            Message::addConfirmation(\count($toAdd).' Schiedsrichter*in(nen) wurde(n) dem/der Kader/Gruppe hinzugefügt.');
        }

        return true;
    }

    /**
     * Löscht einen Schiedsrichter aus allen Gruppen.
     */
    public static function deleteFromGroups($intSR): void
    {
        $intSR = (int) $intSR;

        if (!\is_int($intSR) || 0 === $intSR) {
            throw new \Exception('wrong datatype or zero given');
        }

        $arrGroupIds = Database::getInstance()->prepare('SELECT pid FROM tl_bsa_member_group_member_assignment WHERE refereeId=?')
            ->execute($intSR)
            ->fetchEach('pid')
        ;
        $res = Database::getInstance()->prepare('DELETE FROM tl_bsa_member_group_member_assignment WHERE refereeId=?')
            ->execute($intSR)
        ;

        if ($res->__get('affectedRows') > 0) {
            foreach ($arrGroupIds as $pid) {
                SRHistory::insert($intSR, $pid, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }
        }

        // Gruppen-IDs an das Mitglied schreiben
        self::setGroupsToMember($intSR);
    }

    /**
     * Ermittelt die Gruppen-IDs eines Schiedsrichters und setzt die Liste der Gruppen am Login tl_member.
     */
    public static function setGroupsToMember($intSR, $idToRemove = 0, $idToAdd = 0): void
    {
        $intSR = (int) $intSR;

        if (0 === $intSR) {
            return;
        }

        $query = 'SELECT DISTINCT tl_member_group.id FROM tl_member_group JOIN tl_bsa_member_group_member_assignment ON tl_member_group.id=tl_bsa_member_group_member_assignment.pid WHERE tl_bsa_member_group_member_assignment.refereeId=?';

        if ($idToRemove) {
            $query .= ' AND tl_member_group.id!='.$idToRemove;
        } elseif ($idToAdd) {
            $query .= ' OR tl_member_group.id='.$idToAdd;
        }
        $query .= ' ORDER BY tl_member_group.name';

        $arrGroupIds = Database::getInstance()->prepare($query)
            ->execute($intSR)
            ->fetchEach('id')
        ;

        Database::getInstance()->prepare('UPDATE tl_member SET groups=? WHERE refereeId=?')
            ->execute(serialize($arrGroupIds), $intSR)
        ;
    }

    /**
     * Fügt einen SR in die Gruppe Obleute ein.
     */
    public static function addToObleute($intSR): void
    {
        if (0 === $intSR) {
            return;
        }
        self::addToGroup($intSR, 'obleute');
    }

    /**
     * Entfernt einen SR aus der Gruppe Obleute.
     */
    public static function removeFromObleute($intSR): void
    {
        if (0 === $intSR) {
            return;
        }
        self::deleteFromGroup($intSR, 'obleute');
    }

    /**
     * Updating automatic newsletters on birthday.
     */
    public static function updateOnBirthday(): void
    {
        $arrSR = RefereeModel::getPersonWithBirthdayToday();

        if (empty($arrSR)) {
            System::log('BSA-Gruppenverwaltung wurde ausgeführt, es sind keine Gruppen an Geburtstagen zu aktualisieren.', 'BSA Gruppenverwaltung updateOnBirthday()', TL_CRON);

            return;
        }

        foreach ($arrSR as $sr) {
            self::handleAutomaticGroups($sr);
        }
        System::log('BSA-Gruppenverwaltung wurde ausgeführt, die Gruppen von '.\count($arrSR).' Geburtstagskind(ern) wurde(n) aktualisiert.', 'BSA Gruppenverwaltung updateOnBirthday()', TL_CRON);
    }

    /**
     * tells you if an automatic is an 'vollautomatic' or not.
     *
     * @param mixed $automaticKey
     */
    public static function isVollautomatic($automaticKey): bool
    {
        return !empty($automaticKey)
            && \in_array(
                $automaticKey,
                self::AUTOMATICS['vollautomatik'],
                true
            );
    }

    /**
     * tells you if an automatic is an 'halbautomatic' or not.
     *
     * @param mixed $automaticKey
     */
    public static function isHalbautomatic($automaticKey): bool
    {
        return !empty($automaticKey)
            && \in_array(
                $automaticKey,
                self::AUTOMATICS['halbautomatik'],
                true
            );
    }

    /**
     * clearing the cache of automatic grous.
     *
     * @param string|null $automaticKey
     */
    public static function clearCachedGroups($automaticKey = null): void
    {
        if (!isset($automaticKey)) {
            self::$cachedGroups = [];
        } else {
            unset(self::$cachedGroups[$automaticKey]);
        }
    }

    /**
     * Fügt einen Schiedsrichter in eine Automatikgruppe ein.
     */
    private static function addToGroup($intSR, $automaticKey): void
    {
        if (!\strlen($automaticKey)) {
            return;
        }

        $intGroup = self::getAutomatikGroupId($automaticKey);

        if ($intGroup) {
            if (!MemberGroupAssignmentMemberModel::exists($intGroup, $intSR)) {
                Database::getInstance()->prepare('INSERT INTO tl_bsa_member_group_member_assignment (pid, tstamp, refereeId) VALUES (?,?,?)')
                    ->execute($intGroup, time(), $intSR)
                ;

                SRHistory::insert($intSR, $intGroup, ['Kader/Gruppe', 'ADD'], 'Der Schiedsrichter %s wurde in der Gruppe "%s" aufgenommen.', __METHOD__);
            }
        }
    }

    /**
     * Löscht einen Schiedsrichter aus einer Automatikgruppe.
     */
    private static function deleteFromGroup($intSR, $automaticKey): void
    {
        if (empty($automaticKey)) {
            return;
        }

        $intGroup = self::getAutomatikGroupId($automaticKey);

        if ($intGroup) {
            $res = Database::getInstance()->prepare('DELETE FROM tl_bsa_member_group_member_assignment WHERE pid=? AND refereeId=?')
                ->execute($intGroup, $intSR)
            ;

            if ($res->__get('affectedRows') > 0) {
                SRHistory::insert($intSR, $intGroup, ['Kader/Gruppe', 'REMOVE'], 'Der Schiedsrichter %s wurde aus der Gruppe "%s" entfernt.', __METHOD__);
            }
        }
    }

    /**
     * Liefert die ID einer Gruppe.
     *
     * @param string $automaticKey
     *
     * @return int|null
     */
    private static function getAutomatikGroupId($automaticKey)
    {
        if (!\array_key_exists($automaticKey, self::$cachedGroups)) {
            $objGroup = MemberGroupModel::findOneBy('automatik', $automaticKey);

            if (isset($objGroup)) {
                self::$cachedGroups[$automaticKey] = $objGroup->id;
            }
        }

        return self::$cachedGroups[$automaticKey];
    }
}
