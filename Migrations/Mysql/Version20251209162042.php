<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251209162042 extends AbstractMigration
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

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_accountingrecord ADD amount DOUBLE PRECISION NOT NULL, ADD shouldorhave VARCHAR(255) NOT NULL, ADD account VARCHAR(255) NOT NULL, ADD offsetaccount VARCHAR(255) NOT NULL, ADD text VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_comment CHANGE comment comment TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice DROP INDEX UNIQ_CBA55468EF5FB03, ADD INDEX IDX_CBA55468EF5FB03 (stornoinvoice)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice DROP INDEX UNIQ_CBA55464E59BB9C, ADD INDEX IDX_CBA55464E59BB9C (originalresource)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE deptor deptor TEXT DEFAULT NULL, CHANGE additionalinformation additionalinformation TEXT DEFAULT NULL, CHANGE periodofperformancecomment periodofperformancecomment TEXT DEFAULT NULL, CHANGE additionaltext additionaltext TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_settlementdate CHANGE comment comment TEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1060Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1060Platform'."
        );

        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_comment CHANGE comment comment MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_settlementdate CHANGE comment comment MEDIUMTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_accountingrecord DROP amount, DROP shouldorhave, DROP account, DROP offsetaccount, DROP text');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice DROP INDEX IDX_CBA55464E59BB9C, ADD UNIQUE INDEX UNIQ_CBA55464E59BB9C (originalresource)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice DROP INDEX IDX_CBA55468EF5FB03, ADD UNIQUE INDEX UNIQ_CBA55468EF5FB03 (stornoinvoice)');
        $this->addSql('ALTER TABLE kaystrobach_invoice_domain_model_invoice CHANGE deptor deptor MEDIUMTEXT DEFAULT NULL, CHANGE additionalinformation additionalinformation MEDIUMTEXT DEFAULT NULL, CHANGE periodofperformancecomment periodofperformancecomment MEDIUMTEXT DEFAULT NULL, CHANGE additionaltext additionaltext MEDIUMTEXT DEFAULT NULL');
    }
}
