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
 * Migration to tl_bsa_referee_history and tl_bsa_referee. Rename tables and modify columns.
 */
class RefereeMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Referee History Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldRenameRefereeTable()
        || $this->shouldModifyRefereeColumns()
        || $this->shouldRenameRefereeHistoryTable()
        || $this->shouldModifyRefereeHistoryColumns();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldRenameRefereeTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_schiedsrichter TO tl_bsa_referee');
            $this->resultMessages[] = 'Table tl_bsa_schiedsrichter successfully renamed.';
        }

        if ($this->shouldModifyRefereeColumns()) {
            $query = 'ALTER TABLE tl_bsa_referee ';
            $query .= 'RENAME COLUMN image_exempted TO imageExempted, ';
            $query .= 'RENAME COLUMN image_print TO imagePrint, ';
            $query .= 'RENAME COLUMN geburtsdatum_date TO dateOfBirthAsDate, ';
            $query .= 'RENAME COLUMN sr_seit_date TO dateOfRefereeExaminationAsDate, ';
            $query .= 'RENAME COLUMN sr_seit TO dateOfRefereeExamination, ';
            $query .= 'RENAME COLUMN geburtsdatum TO dateOfBirth, ';
            $query .= 'RENAME COLUMN telefon_mobil TO mobile, ';
            $query .= 'RENAME COLUMN telefon2 TO phone2, ';
            $query .= 'RENAME COLUMN telefon1 TO phone1, ';
            $query .= 'RENAME COLUMN ort TO city, ';
            $query .= 'RENAME COLUMN strasse TO street, ';
            $query .= 'RENAME COLUMN vorname TO firstname, ';
            $query .= 'RENAME COLUMN nachname TO lastname, ';
            $query .= 'RENAME COLUMN ausweisnummer TO cardNumber, ';
            $query .= 'RENAME COLUMN geschlecht TO gender, ';
            $query .= 'RENAME COLUMN name_rev TO nameReverse, ';
            $query .= 'RENAME COLUMN verein TO clubId, ';
            $query .= 'RENAME COLUMN plz TO postal, ';
            $query .= 'RENAME COLUMN email_kontaktformular TO emailContactForm, ';
            $query .= 'RENAME COLUMN status TO state, ';
            $query .= 'RENAME COLUMN is_new TO isNew, ';
            $query .= 'RENAME COLUMN import_key TO importKey, ';
            $query .= 'RENAME COLUMN addressbook_vcards TO addressbookVcards';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_referee successfully renamed.';
        }

        if ($this->shouldRenameRefereeHistoryTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_schiedsrichter_historie TO tl_bsa_referee_history');
            $this->resultMessages[] = 'Table tl_bsa_schiedsrichter_historie successfully renamed.';
        }

        if ($this->shouldModifyRefereeHistoryColumns()) {
            $query = 'ALTER TABLE tl_bsa_referee_history ';
            $query .= 'RENAME COLUMN schiedsrichter TO refereeId, ';
            $query .= 'RENAME COLUMN reference_id TO referenceId, ';
            $query .= 'RENAME COLUMN action_group TO actionGroup ';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_referee_history successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
        );
    }

    private function shouldRenameRefereeTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_schiedsrichter']) && !$schemaManager->tablesExist(['tl_bsa_referee']);
    }

    private function shouldModifyRefereeColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_referee');

        return isset($columns['ausweisnummer']);
    }

    private function shouldRenameRefereeHistoryTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_schiedsrichter_historie']) && !$schemaManager->tablesExist(['tl_bsa_referee_history']);
    }

    private function shouldModifyRefereeHistoryColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_referee_history');

        return isset($columns['schiedsrichter']);
    }
}
