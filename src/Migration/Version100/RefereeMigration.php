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
        return 'Referee Bundle 1.0.0 Referee Update';
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
            $query .= 'CHANGE geschlecht gender VARCHAR(32) DEFAULT \'\' NOT NULL, ';
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

            $query = 'DROP TRIGGER `schiedsrichter_insert_set_name_rev`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger schiedsrichter_insert_set_name_rev successfully dropped.';

            $query = 'DROP TRIGGER `schiedsrichter_insert_update_verein_anzahl_sr`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger schiedsrichter_insert_update_verein_anzahl_sr successfully dropped.';

            $query = 'DROP TRIGGER `schiedsrichter_update_set_name_rev`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger schiedsrichter_update_set_name_rev successfully dropped.';

            $query = 'DROP TRIGGER `schiedsrichter_update_update_verein_anzahl_sr`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger schiedsrichter_update_update_verein_anzahl_sr successfully dropped.';

            $query = 'DROP TRIGGER `schiedsrichter_delete_update_verein_anzahl_sr`';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger schiedsrichter_delete_update_verein_anzahl_sr successfully dropped.';

            $query =
'CREATE TRIGGER `referee_insert_set_name_reverse` BEFORE INSERT ON `tl_bsa_referee` FOR EACH ROW
BEGIN
	SET NEW.nameReverse= CONCAT_WS(\', \', NEW.lastname, NEW.firstname);
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger referee_insert_set_name_reverse successfully created.';

            $query =
'CREATE TRIGGER `referee_insert_quantitiy_to_club` AFTER INSERT ON `tl_bsa_referee` FOR EACH ROW
BEGIN
        UPDATE tl_bsa_club
                SET refereesActiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = NEW.clubId AND state = \'aktiv\' AND deleted = \'\'),
                    refereesPassiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = NEW.clubId AND state = \'passiv\' AND deleted = \'\')
        WHERE id = NEW.clubId;
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger referee_insert_quantitiy_to_club successfully created.';

            $query =
'CREATE TRIGGER `referee_update_set_name_reverse` BEFORE UPDATE ON `tl_bsa_referee` FOR EACH ROW
BEGIN
	SET NEW.nameReverse = CONCAT_WS(\', \', NEW.lastname, NEW.firstname);
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger referee_update_set_name_reverse successfully created.';

            $query =
'CREATE TRIGGER `referee_update_quantitiy_to_club` AFTER UPDATE ON `tl_bsa_referee` FOR EACH ROW
BEGIN
    IF OLD.clubId<>NEW.clubId THEN
        UPDATE tl_bsa_club
            SET refereesActiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = NEW.clubId AND state = \'aktiv\' AND deleted = \'\'),
                refereesPassiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = NEW.clubId AND state = \'passiv\' AND deleted = \'\')
            WHERE id = NEW.clubId;
        UPDATE tl_bsa_club
            SET refereesActiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = OLD.clubId AND state = \'aktiv\' AND deleted = \'\'),
                refereesPassiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = OLD.clubId AND state = \'passiv\' AND deleted = \'\')
            WHERE id = OLD.clubId;
    ELSEIF OLD.deleted<>NEW.deleted OR OLD.state<>NEW.state THEN
        UPDATE tl_bsa_club
            SET refereesActiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = NEW.clubId AND state = \'aktiv\' AND deleted = \'\'),
                refereesPassiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = NEW.clubId AND state = \'passiv\' AND deleted = \'\')
            WHERE id = NEW.clubId;
    END IF;
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger referee_update_quantitiy_to_club successfully created.';

            $query =
'CREATE TRIGGER `referee_delete_quantitiy_to_club` AFTER DELETE ON `tl_bsa_referee` FOR EACH ROW
BEGIN
    UPDATE tl_bsa_club
        SET refereesActiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = OLD.clubId AND state = \'aktiv\' AND deleted = \'\'),
            refereesPassiveQuantity = (SELECT count(*) FROM tl_bsa_referee WHERE clubId = OLD.clubId AND state = \'passiv\' AND deleted = \'\')
        WHERE id = OLD.clubId;
END';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Trigger referee_delete_quantitiy_to_club successfully created.';

            $query = 'UPDATE tl_bsa_referee SET gender=\'male\' WHERE gender=\'m\'';
            $this->connection->executeStatement($query);
            $query = 'UPDATE tl_bsa_referee SET gender=\'female\' WHERE gender=\'w\'';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Gender of tl_bsa_referee is updated.';
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
