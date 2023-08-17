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
 * Migration to tl_bsa_website_data_release and modify columns.
 */
class WebsiteDataReleaseMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Wesite Data Release Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldRenameTable()
            || $this->shouldModifyColumns();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldRenameTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_freigaben TO tl_bsa_website_data_release');
            $this->resultMessages[] = 'Table tl_bsa_freigaben successfully renamed.';
        }

        if ($this->shouldModifyColumns()) {
            $query = 'ALTER TABLE tl_bsa_website_data_release ';
            $query .= 'RENAME COLUMN schiedsrichter TO refereeId, ';
            $query .= 'RENAME COLUMN zeige_foto TO showPhoto, ';
            $query .= 'RENAME COLUMN link_email TO showEmail, ';
            $query .= 'RENAME COLUMN zeige_fax TO showFax, ';
            $query .= 'RENAME COLUMN zeige_telefon_mobil TO showMobile, ';
            $query .= 'RENAME COLUMN zeige_telefon2 TO showPhone2, ';
            $query .= 'RENAME COLUMN zeige_telefon1 TO showPhone1, ';
            $query .= 'RENAME COLUMN zeige_ort TO showCity, ';
            $query .= 'RENAME COLUMN zeige_plz TO showPostal, ';
            $query .= 'RENAME COLUMN zeige_strasse TO showStreet, ';
            $query .= 'RENAME COLUMN zeige_geburtsdatum TO showDateOfBirth, ';
            $query .= 'RENAME COLUMN formular_erhalten_am TO dateOfFormReceived';

            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_website_data_release successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
        );
    }

    private function shouldRenameTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_freigaben']) && !$schemaManager->tablesExist(['tl_bsa_website_data_release']);
    }

    private function shouldModifyColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_website_data_release');

        return isset($columns['schiedsrichter']);
    }
}
