<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210105527 extends AbstractMigration
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

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoiceitem ADD singleprice_value INT NOT NULL DEFAULT 0, ADD singleprice_currency VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoiceitem SET singleprice_value=ROUND(singleprice*100)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoiceitem DROP singleprice');

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoiceitem ADD total_value INT NOT NULL DEFAULT 0, ADD total_currency VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoiceitem SET total_value=ROUND(total*100)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoiceitem DROP total');
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
