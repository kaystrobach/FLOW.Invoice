<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251223145418 extends AbstractMigration
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

        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice SET currency = "EUR", totalnotaxes_currency = "EUR", total_currency = "EUR"');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice_taxrecord SET sum_currency = "EUR"');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoiceitem SET singleprice_currency = "EUR", total_currency = "EUR"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->throwIrreversibleMigrationException();
    }
}
