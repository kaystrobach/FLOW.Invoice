<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251212072057 extends AbstractMigration
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

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE deptornumber customer_deptornumber VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_name LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE deptor customer_combinedaddress LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_street VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_housenumber VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_addressaddon VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_roomnumber VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_zipcode VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_city VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_country VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_countrycode VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD customer_vatid VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE email customer_email VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_name LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_combinedaddress LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_street VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_housenumber VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_addressaddon VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_roomnumber VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_zipcode VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_city VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_country VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_countrycode VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice ADD seller_vatid VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->throwIrreversibleMigrationException('no timetravel allowed');
    }
}
