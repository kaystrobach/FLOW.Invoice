<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212061408 extends AbstractMigration
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

        $this->addSql('CREATE TABLE kaystrobach_invoice_domain_model_invoice_tags_join (invoice_invoice VARCHAR(40) NOT NULL, tags_tag VARCHAR(40) NOT NULL, INDEX IDX_41AD065F62BC2197 (invoice_invoice), INDEX IDX_41AD065FA4DCD3CA (tags_tag), PRIMARY KEY(invoice_invoice, tags_tag)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_tags_join ADD CONSTRAINT FK_41AD065F62BC2197 FOREIGN KEY (invoice_invoice) REFERENCES kaystrobach_invoice_domain_model_invoice (persistence_object_identifier)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_tags_join ADD CONSTRAINT FK_41AD065FA4DCD3CA FOREIGN KEY (tags_tag) REFERENCES kaystrobach_tags_domain_model_tag (persistence_object_identifier)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_tags_join DROP FOREIGN KEY FK_41AD065F62BC2197');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice_tags_join DROP FOREIGN KEY FK_41AD065FA4DCD3CA');
        $this->addSql('DROP TABLE kaystrobach_invoice_domain_model_invoice_tags_join');
    }
}
