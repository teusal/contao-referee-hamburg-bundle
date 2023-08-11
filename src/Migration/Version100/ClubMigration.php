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
 * Migration to tl_bsa_club as well as tl_bsa_club_chairman and modify columns.
 */
class ClubMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Club Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldRenameClubTable()
            || $this->shouldModifyClubColumns()
            || $this->shouldRenameClubChairmanTable()
            || $this->shouldModifyClubChairmanColumns();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldRenameClubTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_verein TO tl_bsa_club');
            $this->resultMessages[] = 'Table tl_bsa_verein successfully renamed.';
        }

        if ($this->shouldModifyClubColumns()) {
            $query = 'ALTER TABLE tl_bsa_club ';
            $query .= 'RENAME COLUMN name_kurz TO nameShort, ';
            $query .= 'RENAME COLUMN nummer TO number, ';
            $query .= 'RENAME COLUMN name_zusatz TO nameAddition, ';
            $query .= 'RENAME COLUMN strasse TO street, ';
            $query .= 'RENAME COLUMN plz TO postal, ';
            $query .= 'RENAME COLUMN ort TO city, ';
            $query .= 'RENAME COLUMN telefon1 TO phone1, ';
            $query .= 'RENAME COLUMN telefon2 TO phone2, ';
            $query .= 'RENAME COLUMN anzahl_schiedsrichter_aktiv TO refereesActiveQuantity, ';
            $query .= 'RENAME COLUMN anzahl_schiedsrichter_passiv TO refereesPassiveQuantity, ';
            $query .= 'RENAME COLUMN anzeigen TO published';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_club successfully renamed.';
        }

        if ($this->shouldRenameClubChairmanTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_verein_obmann TO tl_bsa_club_chairman');
            $this->resultMessages[] = 'Table tl_bsa_verein_obmann successfully renamed.';
        }

        if ($this->shouldModifyClubChairmanColumns()) {
            $query = 'ALTER TABLE tl_bsa_club_chairman ';
            $query .= 'RENAME COLUMN verein TO clubId, ';
            $query .= 'RENAME COLUMN obmann TO chairman, ';
            $query .= 'RENAME COLUMN stellv_obmann_1 TO viceChairman1, ';
            $query .= 'RENAME COLUMN stellv_obmann_2 TO viceChairman2';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_club_chairman successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
        );
    }

    private function shouldRenameClubTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_verein']) && !$schemaManager->tablesExist(['tl_bsa_club']);
    }

    private function shouldModifyClubColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_club');

        return isset($columns['name_kurz']);
    }

    private function shouldRenameClubChairmanTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_verein_obmann']) && !$schemaManager->tablesExist(['tl_bsa_club_chairman']);
    }

    private function shouldModifyClubChairmanColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_club_chairman');

        return isset($columns['verein']);
    }
}
