<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210133241 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_taxrecord ADD sum_value INT NULL DEFAULT 0, ADD sum_currency VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice_taxrecord SET sum_value=ROUND(sum*100)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_taxrecord DROP sum');

        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice_taxrecord SET sum_value=0 WHERE sum_value IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->throwIrreversibleMigrationException('We are manipulated the content of the invoice table, this is not reverseable');
    }
}
