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
            || $this->shouldModifyNewsletterRecipients();
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
            $query .= 'CHANGE COLUMN schiedsrichter_historie writeRefereeHistory CHAR(1) DEFAULT "1" NOT NULL AFTER jumpTo';
            $query .= 'ADD sendInfomail CHAR(1) DEFAULT "" NOT NULL ';
            $query .= 'ADD infomailRecipients  VARCHAR(255) DEFAULT "" NOT NULL ';
            $query .= 'ADD prependChannelInformation CHAR(1) DEFAULT "" NOT NULL ';
            $query .= 'CHANGE COLUMN newsletter_info_text channelInformationText VARCHAR(255) DEFAULT "" NOT NULL AFTER prependChannelInformation';
            $this->connection->executeQuery($query);

            $query  = 'UPDATE tl_newsletter_channel AS channel, (SELECT pid, info_to FROM tl_newsletter WHERE id IN (SELECT MAX(id) FROM tl_newsletter WHERE info_to != "" GROUP BY pid)) AS data ';
            $query .= 'SET channel.infomailRecipients = data.info_to ';
            $query .= 'WHERE channel.id = data.pid';
            $this->connection->executeQuery($query);

            $query  = 'UPDATE tl_newsletter_channel SET sendInfomail = "1" WHERE infomailRecipients != ""';
            $this->connection->executeQuery($query);

            $query  = 'UPDATE tl_newsletter_channel SET prependChannelInformation = "1" WHERE channelInformationText != ""';
            $this->connection->executeQuery($query);

            $this->resultMessages[] = 'Table "tl_newsletter_channel" successfully altered and updated.';
        }

        if ($this->shouldModifyNewsletter()) {
            $this->connection->executeQuery('ALTER TABLE tl_bsa_season RENAME COLUMN aktiv TO active');
            $this->resultMessages[] = 'Column "aktiv" successfully renamed.';
        }

        return $this->createResult(
            true,
            $this->resultMessages ? implode("\n", $this->resultMessages) : null
        );
    }


    private function shouldModifyNewsletterChannel(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_newsletter_channel');

        return isset($columns['schiedsrichter_historie']);
    }
    private function shouldModifyNewsletter(): bool
    {
        return false;
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_newsletter');

        return isset($columns['newsletter_info_text']);
    }
    private function shouldModifyNewsletterRecipients(): bool
    {
        return false;
        $schemaManager = $this->connection->createSchemaManager();
        $columns = $schemaManager->listTableColumns('tl_newsletter_recipients');

        return isset($columns['schiedsrichter']);
    }
}
