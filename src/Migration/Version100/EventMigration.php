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
 * Migration to tl_bsa_event as well as tl_bsa_event_participiant and modify columns.
 */
class EventMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Event Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldRenameEventTable()
            || $this->shouldModifyEventColumns()
            || $this->shouldRenameEventParticipiantTable()
            || $this->shouldModifyEventParticipiantColumns();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldRenameEventTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_veranstaltung TO tl_bsa_event');
            $this->resultMessages[] = 'Table tl_bsa_veranstaltung successfully renamed.';
        }

        if ($this->shouldModifyEventColumns()) {
            $query = 'ALTER TABLE tl_bsa_event ';
            $query .= 'RENAME COLUMN saison TO seasonId, ';
            $query .= 'RENAME COLUMN datum TO date, ';
            $query .= 'RENAME COLUMN veranstaltungsgruppe TO eventGroup, ';
            $query .= 'RENAME COLUMN typ TO type';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_event successfully renamed.';
        }

        if ($this->shouldRenameEventParticipiantTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_teilnehmer TO tl_bsa_event_participiant');
            $this->resultMessages[] = 'Table tl_bsa_teilnehmer successfully renamed.';
        }

        if ($this->shouldModifyEventParticipiantColumns()) {
            $query = 'ALTER TABLE tl_bsa_event_participiant ';
            $query .= 'RENAME COLUMN sr_id TO refereeId, ';
            $query .= 'RENAME COLUMN sr TO refereeNameReverse, ';
            $query .= 'RENAME COLUMN typ TO type';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_event_participiant successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
        );
    }

    private function shouldRenameEventTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_veranstaltung']) && !$schemaManager->tablesExist(['tl_bsa_event']);
    }

    private function shouldModifyEventColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_event');

        return isset($columns['veranstaltungsgruppe']);
    }

    private function shouldRenameEventParticipiantTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_teilnehmer']) && !$schemaManager->tablesExist(['tl_bsa_event_participiant']);
    }

    private function shouldModifyEventParticipiantColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_event_participiant');

        return isset($columns['sr_id']);
    }
}
