<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251210125358 extends AbstractMigration
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

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD totalnotaxes_value INT NULL DEFAULT 0, ADD totalnotaxes_currency VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice SET totalnotaxes_value=ROUND(totalnotaxes*100)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice DROP totalnotaxes');

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD total_value INT NULL DEFAULT 0, ADD total_currency VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice SET total_value=ROUND(total*100)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice DROP total');

        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice SET total_value=0 WHERE total_value IS NULL');
        $this->addSql('UPDATE kaystrobach_invoice_domain_model_invoice SET totalnotaxes_value=0 WHERE totalnotaxes_value IS NULL');

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
