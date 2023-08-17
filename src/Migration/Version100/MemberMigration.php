<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Migration\Version100;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\Idna;
use Doctrine\DBAL\Connection;

/**
 * Migration. Modify tl_member and tl_bsa_member_group_referee_assignment.
 */
class MemberMigration extends AbstractMigration
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var array<string>
     */
    private array $resultMessages = [];

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'Referee Bundle 1.0.0 Member Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldModifyMember()
            || $this->shouldRenameMemberGroupRefereeAssignmentTable()
            || $this->shouldModifyMemberGroupRefereeAssignmentColumns();
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($this->shouldModifyMember()) {
            $query = 'ALTER TABLE tl_member ';
            $query .= 'CHANGE COLUMN schiedsrichter refereeId INT UNSIGNED DEFAULT NULL AFTER tstamp';
            $this->connection->executeStatement($query);

            $this->resultMessages[] = 'Table tl_member successfully changed.';

            if ($schemaManager->tablesExist(['tl_bsa_schiedsrichter'])) {
                $query = 'SELECT sr.vorname firstname, sr.nachname lastname, sr.geburtsdatum dateOfBirth, sr.geschlecht gender, sr.strasse street, sr.plz postal, sr.ort city, sr.telefon1 phone, sr.telefon_mobil mobile, sr.fax, sr.email, sr.id FROM tl_bsa_schiedsrichter sr, tl_member member WHERE sr.id = member.refereeId';
            } else {
                $query = 'SELECT ref.firstname, ref.lastname, ref.dateOfBirth, ref.gender, ref.street, ref.postal, ref.city, ref.phone, ref.mobile, ref.fax, ref.email, ref.id FROM tl_bsa_referee ref, tl_member member WHERE ref.id = member.refereeId';
            }
            $arr = $this->connection->executeQuery($query)->fetchAllAssociative();

            foreach ($arr as $row) {
                $row['email'] = Idna::encodeEmail($row['firstname'].' '.$row['lastname'].' ['.$row['email'].']');

                switch ($row['gender']) {
                    case 'm':
                    case 'male':
                        $row['gender'] = 'male';
                        break;
                    case 'w':
                    case 'female':
                        $row['gender'] = 'female';
                        break;
                    case 'other':
                        $row['gender'] = 'other';
                        break;

                    default:
                        $row['gender'] = '';
                        break;
                }
                $this->connection->executeStatement('UPDATE tl_member SET firstname=?, lastname=?, dateOfBirth=IFNULL(?,""), gender=?, street=?, postal=?, city=?, phone=?, mobile=?, fax=?, email=? WHERE refereeId=?', array_values($row));
            }

            $this->resultMessages[] = 'Data of table tl_member successfully updated.';
        }

        if ($this->shouldRenameMemberGroupRefereeAssignmentTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_gruppenmitglieder TO tl_bsa_member_group_referee_assignment');
            $this->resultMessages[] = 'Table tl_bsa_gruppenmitglieder successfully renamed.';
        }

        if ($this->shouldModifyMemberGroupRefereeAssignmentColumns()) {
            $query = 'ALTER TABLE tl_bsa_member_group_referee_assignment ';
            $query .= 'RENAME COLUMN schiedsrichter TO refereeId ';
            $query .= 'RENAME COLUMN funktion TO function ';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_member_group_referee_assignment successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
        );
    }

    private function shouldModifyMember(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_member'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_member');

        return isset($columns['schiedsrichter']);
    }

    private function shouldRenameMemberGroupRefereeAssignmentTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_gruppenmitglieder']) && !$schemaManager->tablesExist(['tl_bsa_member_group_referee_assignment']);
    }

    private function shouldModifyMemberGroupRefereeAssignmentColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_member_group_referee_assignment');

        return isset($columns['schiedsrichter']);
    }
}
