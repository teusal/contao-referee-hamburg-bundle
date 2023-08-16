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
use Doctrine\DBAL\Connection;

/**
 * Migration to tl_bsa_sports_facility as well as tl_bsa_sports_facility_number and modify columns.
 */
class SportsFacilityMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Sports Facility Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldRenameSportsFacilityTable()
            || $this->shouldModifySportsFacilityColumns()
            || $this->shouldRenameSportsFacilityNumberTable()
            || $this->shouldModifySportsFacilityNumberColumns()
            || $this->shouldRenameSportsFacilityClubAssignmentTable()
            || $this->shouldModifySportsFacilityClubAssignmentColumns();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldRenameSportsFacilityTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_sportplatz TO tl_bsa_sports_facility');
            $this->resultMessages[] = 'Table tl_bsa_sportplatz successfully renamed.';
        }

        if ($this->shouldModifySportsFacilityColumns()) {
            $query = 'ALTER TABLE tl_bsa_sports_facility ';
            $query .= 'RENAME COLUMN hvv_id TO hvvId, ';
            $query .= 'RENAME COLUMN telefon2_beschreibung TO phone2Description, ';
            $query .= 'RENAME COLUMN telefon2 TO phone2, ';
            $query .= 'RENAME COLUMN telefon1_beschreibung TO phone1Description, ';
            $query .= 'RENAME COLUMN telefon1 TO phone1, ';
            $query .= 'RENAME COLUMN typ TO type, ';
            $query .= 'RENAME COLUMN strasse TO street, ';
            $query .= 'RENAME COLUMN plz TO postal, ';
            $query .= 'RENAME COLUMN ort TO city, ';
            $query .= 'RENAME COLUMN anschrift TO address, ';
            $query .= 'RENAME COLUMN link_hvv TO hvvLink, ';
            $query .= 'RENAME COLUMN platzwart TO groundskeeper, ';
            $query .= 'RENAME COLUMN anzeigen TO published';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_sports_facility successfully renamed.';

            $query = 'DROP TRIGGER `sportplatz_insert_set_anschrift`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger sportplatz_insert_set_anschrift successfully dropped.';

            $query =
'CREATE TRIGGER `sports_facility_insert_set_address` BEFORE INSERT ON `tl_bsa_sports_facility` FOR EACH ROW
BEGIN
    SET NEW.address = CONCAT(NEW.street,\', \', NEW.postal, \' \', NEW.city);
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger sports_facility_insert_set_address successfully created.';

            $query = 'DROP TRIGGER `sportplatz_update_set_anschrift`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger sportplatz_update_set_anschrift successfully dropped.';

            $query =
'CREATE TRIGGER `sports_facility_update_set_address` BEFORE UPDATE ON `tl_bsa_sports_facility` FOR EACH ROW
BEGIN
    SET NEW.address = CONCAT(NEW.street, \', \', NEW.postal, \' \', NEW.city);
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger sports_facility_update_set_address successfully created.';
        }

        if ($this->shouldRenameSportsFacilityNumberTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_sportplatz_nummer TO tl_bsa_sports_facility_number');
            $this->resultMessages[] = 'Table tl_bsa_sportplatz_nummer successfully renamed.';
        }

        if ($this->shouldModifySportsFacilityNumberColumns()) {
            $query = 'ALTER TABLE tl_bsa_sports_facility_number ';
            $query .= 'RENAME COLUMN dfbnet_nummer TO dfbnetNumber';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_sports_facility_number successfully renamed.';
        }

        if ($this->shouldRenameSportsFacilityClubAssignmentTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_sportplatz_zuordnung TO tl_bsa_sports_facility_club_assignment');
            $this->resultMessages[] = 'Table tl_bsa_sportplatz_zuordnung successfully renamed.';
        }

        if ($this->shouldModifySportsFacilityClubAssignmentColumns()) {
            $query = 'ALTER TABLE tl_bsa_sports_facility_club_assignment ';
            $query .= 'RENAME COLUMN verein TO clubId, ';
            $query .= 'RENAME COLUMN sportplaetze TO sportsFacilityIds';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_sports_facility_club_assignment successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
        );
    }

    private function shouldRenameSportsFacilityTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_sportplatz']) && !$schemaManager->tablesExist(['tl_bsa_sports_facility']);
    }

    private function shouldModifySportsFacilityColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_sports_facility');

        return isset($columns['anzeigen']);
    }

    private function shouldRenameSportsFacilityNumberTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_sportplatz_nummer']) && !$schemaManager->tablesExist(['tl_bsa_sports_facility_number']);
    }

    private function shouldModifySportsFacilityNumberColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_sports_facility_number');

        return isset($columns['dfbnet_nummer']);
    }

    private function shouldRenameSportsFacilityClubAssignmentTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_sportplatz_zuordnung']) && !$schemaManager->tablesExist(['tl_bsa_sports_facility_club_assignment']);
    }

    private function shouldModifySportsFacilityClubAssignmentColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_sports_facility_club_assignment');

        return isset($columns['verein']);
    }
}
