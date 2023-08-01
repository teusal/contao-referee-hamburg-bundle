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
 * Migration. Modify columns in tl_newsletter_channel, tl_newsletter and tl_newsletter_recipients.
 */
class NewsletterMigration extends AbstractMigration
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
        return 'Referee Bundle 1.0.0 Newsletter Update';
    }

    public function shouldRun(): bool
    {
        return $this->shouldModifyNewsletterChannel()
            || $this->shouldModifyNewsletter()
            || $this->shouldModifyNewsletterRecipients()
            || $this->shouldUpdateNewsletterUnique();
    }

    public function run(): MigrationResult
    {
        if ($this->shouldModifyNewsletterChannel()) {
            $query  = 'ALTER TABLE tl_newsletter_channel ';
            $query .= 'DROP useSMTP, ';
            $query .= 'DROP smtpHost, ';
            $query .= 'DROP smtpUser, ';
            $query .= 'DROP smtpPass, ';
            $query .= 'DROP smtpEnc, ';
            $query .= 'DROP smtpPort, ';
            $query .= 'CHANGE COLUMN schiedsrichter_historie writeRefereeHistory CHAR(1) DEFAULT "1" NOT NULL AFTER jumpTo, ';
            $query .= 'ADD sendInfomail CHAR(1) DEFAULT "" NOT NULL, ';
            $query .= 'ADD infomailRecipients  VARCHAR(255) DEFAULT "" NOT NULL, ';
            $query .= 'ADD prependChannelInformation CHAR(1) DEFAULT "" NOT NULL, ';
            $query .= 'CHANGE COLUMN newsletter_info_text channelInformationText VARCHAR(255) DEFAULT "" NOT NULL AFTER prependChannelInformation ';
            $this->connection->executeQuery($query);

            $query  = 'UPDATE tl_newsletter_channel AS channel, (SELECT pid, info_to FROM tl_newsletter WHERE id IN (SELECT MAX(id) FROM tl_newsletter WHERE info_to != "" GROUP BY pid)) AS data ';
            $query .= 'SET channel.infomailRecipients = data.info_to ';
            $query .= 'WHERE channel.id = data.pid';
            $this->connection->executeQuery($query);

            $query  = 'UPDATE tl_newsletter_channel SET sendInfomail = "1" WHERE infomailRecipients != ""';
            $this->connection->executeQuery($query);

            $query  = 'UPDATE tl_newsletter_channel SET prependChannelInformation = "1" WHERE channelInformationText != ""';
            $this->connection->executeQuery($query);

            $this->resultMessages[] = 'Table "tl_newsletter_channel" successfully changed and updated.';
        }

        if ($this->shouldModifyNewsletter()) {
            $query  = 'ALTER TABLE tl_newsletter ';
            $query .= 'DROP newsletter_info_text, ';
            $query .= 'DROP info_to, ';
            $query .= 'CHANGE COLUMN reply_to replyToAddress VARCHAR(128) DEFAULT "" NOT NULL AFTER date, ';
            $query .= 'CHANGE COLUMN cc_obmann ccChairman CHAR(1) DEFAULT "" NOT NULL AFTER replyToAddress, ';
            $query .= 'CHANGE COLUMN is_info_to_sent infomailSent CHAR(1) DEFAULT "" NOT NULL AFTER ccChairman ';
            $this->connection->executeQuery($query);

            $this->resultMessages[] = 'Table "tl_newsletter" successfully changed.';
        }

        if ($this->shouldModifyNewsletterRecipients()) {
            $query  = 'ALTER TABLE tl_newsletter_recipients ';
            $query .= 'CHANGE COLUMN schiedsrichter refereeId INT UNSIGNED DEFAULT NULL AFTER token, ';
            $query .= 'CHANGE COLUMN nachname lastname VARCHAR(50) DEFAULT "" NOT NULL AFTER groups, ';
            $query .= 'CHANGE COLUMN vorname firstname VARCHAR(50) DEFAULT "" NOT NULL AFTER lastname, ';
            $query .= 'CHANGE COLUMN anrede_persoenlich salutationPersonal VARCHAR(25) DEFAULT "" NOT NULL AFTER token';
            $this->connection->executeQuery($query);

            $this->resultMessages[] = 'Table "tl_newsletter_recipients" successfully changed.';
        }

        if($this->shouldUpdateNewsletterUnique()) {
// CREATE INDEX pid_email ON tl_newsletter_recipients (pid, email);
// CREATE INDEX email ON tl_newsletter_recipients (email);
// CREATE UNIQUE INDEX pid_email_refereeid ON tl_newsletter_recipients (pid, email, refereeid);
            $query  = 'ALTER TABLE tl_newsletter_recipients ';
            $query .= 'DROP INDEX pid_email, ';
            $query .= 'ADD INDEX pid_email (pid, email), ';
            $query .= 'ADD UNIQUE INDEX pid_email_refereeid (pid, email, refereeId) ';
            $this->connection->executeQuery($query);

            $query  = 'INSERT INTO tl_newsletter_recipients (id, pid, tstamp, email, active, addedOn, confirmed, ip, token, refereeId, groups, lastname, firstname, salutationPersonal) ';
            $query .= 'SELECT id, pid, tstamp, email, active, addedOn, confirmed, ip, token, schiedsrichter, groups, nachname, vorname, anrede_persoenlich ';
            $query .= 'FROM tl_newsletter_recipients_backup WHERE id NOT IN (SELECT id FROM tl_newsletter_recipients)';
            $this->connection->executeQuery($query);

            $this->resultMessages[] = 'Uniques of table "tl_newsletter_recipients" successfully changed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n", $this->resultMessages) : null
        );
    }


    private function shouldModifyNewsletterChannel(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_newsletter_channel'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_newsletter_channel');

        return isset($columns['schiedsrichter_historie']);
    }

    private function shouldModifyNewsletter(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_newsletter'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_newsletter');

        return isset($columns['newsletter_info_text']);
    }

    private function shouldModifyNewsletterRecipients(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_newsletter_recipients'])) {
            return false;
        }

        $columns = $schemaManager->listTableColumns('tl_newsletter_recipients');

        return isset($columns['schiedsrichter']);
    }

    private function shouldUpdateNewsletterUnique(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist(['tl_newsletter_recipients'])) {
            return false;
        }
        if (!$schemaManager->tablesExist(['tl_newsletter_recipients_backup'])) {
            return false;
        }

        $indexes = $schemaManager->listTableIndexes('tl_newsletter_recipients');

        return !isset($indexes['pid_email_refereeid']);
    }
}
