<?php

declare(strict_types=1);

/*
 * This file is part of Contao Referee Hamburg Bundle.
 *
 * (c) Alexander Teuscher
 *
 * @license LGPL-3.0-or-later
 */

namespace Teusal\ContaoRefereeHamburgBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * Migration to tl_bsa_season and modify columns.
 */
class BsaSeasonMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Season Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldRenameTable()
            || $this->shouldModifyColumns();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldRenameTable()) {
            $this->connection->executeQuery('RENAME TABLE tl_bsa_saison TO tl_bsa_season');
            $this->resultMessages[] = 'Table "tl_bsa_saison" successfully renamed.';
        }

        if ($this->shouldModifyColumns()) {
            $this->connection->executeQuery('ALTER TABLE tl_bsa_season RENAME COLUMN aktiv TO active');
            $this->resultMessages[] = 'Column "aktiv" successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n", $this->resultMessages) : null
        );
    }

    private function shouldRenameTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_saison']) && !$schemaManager->tablesExist(['tl_bsa_season']);
    }

    private function shouldModifyColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_season');

        return isset($columns['aktiv'])
            && !isset($columns['active']);
    }
}
