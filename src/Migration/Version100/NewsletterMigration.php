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

use Contao\Config;
use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Contao\Idna;
use Doctrine\DBAL\Connection;

/**
 * Migration. Modify tl_newsletter_channel, tl_newsletter, tl_newsletter_recipients and tl_bsa_member_group_newsletter_assignment.
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
            || $this->shouldRenameNewsletterAssignmentTable()
            || $this->shouldModifyNewsletterAssignmentColumns();
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();

        if ($this->shouldModifyNewsletterChannel()) {
            $query = 'ALTER TABLE tl_newsletter_channel ';
            $query .= 'DROP useSMTP, ';
            $query .= 'DROP smtpHost, ';
            $query .= 'DROP smtpUser, ';
            $query .= 'DROP smtpPass, ';
            $query .= 'DROP smtpEnc, ';
            $query .= 'DROP smtpPort, ';
            $query .= 'ADD template VARCHAR(32) DEFAULT "" NOT NULL AFTER jumpTo, ';
            $query .= 'ADD mailerTransport VARCHAR(255) DEFAULT "" NOT NULL AFTER template, ';
            $query .= 'ADD sender VARCHAR(255) DEFAULT "" NOT NULL AFTER mailerTransport, ';
            $query .= 'ADD senderName VARCHAR(128) DEFAULT "" NOT NULL AFTER sender, ';
            $query .= 'CHANGE COLUMN schiedsrichter_historie writeRefereeHistory CHAR(1) DEFAULT "1" NOT NULL AFTER senderName, ';
            $query .= 'ADD sendInfomail CHAR(1) DEFAULT "" NOT NULL, ';
            $query .= 'ADD infomailRecipients  VARCHAR(255) DEFAULT "" NOT NULL, ';
            $query .= 'ADD prependChannelInformation CHAR(1) DEFAULT "" NOT NULL, ';
            $query .= 'CHANGE COLUMN newsletter_info_text channelInformationText VARCHAR(255) DEFAULT "" NOT NULL AFTER prependChannelInformation ';
            $this->connection->executeStatement($query);

            $this->resultMessages[] = 'Table tl_newsletter_channel successfully changed';

            $query = 'UPDATE tl_newsletter_channel AS channel, (SELECT pid, info_to FROM tl_newsletter WHERE id IN (SELECT MAX(id) FROM tl_newsletter WHERE info_to != "" GROUP BY pid)) AS data ';
            $query .= 'SET channel.infomailRecipients = data.info_to ';
            $query .= 'WHERE channel.id = data.pid';
            $this->connection->executeStatement($query);

            $query = 'UPDATE tl_newsletter_channel SET sendInfomail = "1" WHERE infomailRecipients != ""';
            $this->connection->executeStatement($query);

            $query = 'UPDATE tl_newsletter_channel SET prependChannelInformation = "1" WHERE channelInformationText != ""';
            $this->connection->executeStatement($query);

            $query = 'UPDATE tl_newsletter_channel SET sender = "bsa-'.Config::get('bsa_name').'@hfv.de", senderName="'.$GLOBALS['BSA_NAMES'][Config::get('bsa_name')].'"';
            $this->connection->executeStatement($query);

            $this->resultMessages[] = 'Data of table tl_newsletter_channel successfully updated.';
        }

        if ($this->shouldModifyNewsletter()) {
            $query = 'ALTER TABLE tl_newsletter ';
            $query .= 'DROP newsletter_info_text, ';
            $query .= 'DROP info_to, ';
            $query .= 'CHANGE COLUMN reply_to replyToAddress VARCHAR(128) DEFAULT "" NOT NULL AFTER date, ';
            $query .= 'CHANGE COLUMN cc_obmann ccChairman CHAR(1) DEFAULT "" NOT NULL AFTER replyToAddress, ';
            $query .= 'CHANGE COLUMN is_info_to_sent infomailSent CHAR(1) DEFAULT "" NOT NULL AFTER ccChairman ';
            $this->connection->executeStatement($query);

            $this->resultMessages[] = 'Table tl_newsletter successfully changed.';
        }

        if ($this->shouldModifyNewsletterRecipients()) {
            $query = 'ALTER TABLE tl_newsletter_recipients ';
            $query .= 'CHANGE COLUMN schiedsrichter refereeId INT UNSIGNED DEFAULT NULL AFTER token, ';
            $query .= 'DROP COLUMN nachname, ';
            $query .= 'DROP COLUMN vorname, ';
            $query .= 'DROP COLUMN anrede_persoenlich';
            $this->connection->executeStatement($query);

            $this->resultMessages[] = 'Table tl_newsletter_recipients successfully changed.';

            if ($schemaManager->tablesExist(['tl_bsa_schiedsrichter'])) {
                $query = 'SELECT sr.vorname firstname, sr.nachname lastname, sr.email, sr.id FROM tl_bsa_schiedsrichter sr, tl_newsletter_recipients recipient WHERE sr.id = recipient.refereeId';
            } else {
                $query = 'SELECT ref.firstname, ref.lastname, ref.email, ref.id FROM tl_bsa_referee ref, tl_newsletter_recipients recipient WHERE ref.id = recipient.refereeId';
            }
            $arr = $this->connection->executeQuery($query)->fetchAllAssociative();

            foreach ($arr as $row) {
                $row['email'] = Idna::encodeEmail($row['firstname'].' '.$row['lastname'].' ['.$row['email'].']');
                unset($row['firstname'], $row['lastname']);
                $query = 'UPDATE tl_newsletter_recipients SET email=? WHERE refereeId=?';
                $this->connection->executeStatement($query, array_values($row));
            }

            if ($schemaManager->tablesExist(['tl_newsletter_recipients_backup'])) {
                $query = 'INSERT IGNORE INTO tl_newsletter_recipients (id, pid, tstamp, email, active, addedOn, confirmed, ip, token, refereeId, groups) ';
                $query .= 'SELECT id, pid, tstamp, email, active, addedOn, confirmed, ip, token, schiedsrichter, groups ';
                $query .= 'FROM tl_newsletter_recipients_backup WHERE id NOT IN (SELECT id FROM tl_newsletter_recipients)';
                $this->connection->executeStatement($query);
            }

            $this->resultMessages[] = 'Data of table tl_newsletter_recipients successfully updated.';
        }

        if ($this->shouldRenameNewsletterAssignmentTable()) {
            $this->connection->executeStatement('RENAME TABLE tl_bsa_newsletterzuordnung TO tl_bsa_member_group_newsletter_assignment');
            $this->resultMessages[] = 'Table "tl_bsa_newsletterzuordnung" successfully renamed.';
        }

        if ($this->shouldModifyNewsletterAssignmentColumns()) {
            $query = 'ALTER TABLE tl_bsa_member_group_newsletter_assignment ';
            $query .= 'RENAME COLUMN newsletter_channel TO newsletterChannelId';
            $this->connection->executeStatement($query);
            $this->resultMessages[] = 'Columns of table tl_bsa_member_group_newsletter_assignment successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n * ", $this->resultMessages) : null
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

    private function shouldRenameNewsletterAssignmentTable(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist(['tl_bsa_newsletterzuordnung']) && !$schemaManager->tablesExist(['tl_bsa_member_group_newsletter_assignment']);
    }

    private function shouldModifyNewsletterAssignmentColumns(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_bsa_member_group_newsletter_assignment');

        return isset($columns['newsletter_channel']);
    }
}
