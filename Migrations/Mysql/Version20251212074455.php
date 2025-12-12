<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212074455 extends AbstractMigration
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

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE totalnotaxes_value totalnotaxes_value INT NOT NULL, CHANGE total_value total_value INT NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_taxrecord CHANGE sum_value sum_value INT NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoiceitem CHANGE singleprice_value singleprice_value INT NOT NULL, CHANGE total_value total_value INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_taxrecord CHANGE sum_value sum_value INT DEFAULT 0');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoiceitem CHANGE singleprice_value singleprice_value INT DEFAULT 0 NOT NULL, CHANGE total_value total_value INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE total_value total_value INT DEFAULT 0, CHANGE totalnotaxes_value totalnotaxes_value INT DEFAULT 0');
    }
}
