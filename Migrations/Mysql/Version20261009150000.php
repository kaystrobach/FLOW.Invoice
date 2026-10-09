<?php

declare(strict_types=1);

namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Number ranges with row lock instead of MAX(number) + 1, unique invoice numbers per series
 */
final class Version20261009150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'KayStrobach.Invoice: number range table (seeded from existing invoices) and unique index on prefix/number/postfix';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $duplicates = $this->connection->fetchAllAssociative(
            'SELECT number_combinednumber AS invoice_number, COUNT(*) AS c
             FROM kaystrobach_invoice_domain_model_invoice
             WHERE number_number IS NOT NULL
             GROUP BY number_prefix, number_number, number_postfix, number_combinednumber
             HAVING c > 1'
        );
        $this->abortIf(
            $duplicates !== [],
            'Duplicate invoice numbers exist, the unique index cannot be created. Resolve them first (./flow invoice:checknumbers): '
            . implode(', ', array_map(static fn (array $row) => $row['invoice_number'] . ' (' . $row['c'] . 'x)', $duplicates))
        );

        $this->addSql('CREATE TABLE kaystrobach_invoice_numberrange (persistence_object_identifier VARCHAR(40) NOT NULL, prefix VARCHAR(255) NOT NULL, postfix VARCHAR(255) NOT NULL, lastnumber INT NOT NULL, UNIQUE INDEX invoice_numberrange_series (prefix, postfix), PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('INSERT INTO kaystrobach_invoice_numberrange (persistence_object_identifier, prefix, postfix, lastnumber)
            SELECT UUID(), number_prefix, number_postfix, MAX(number_number)
            FROM kaystrobach_invoice_domain_model_invoice
            WHERE number_number IS NOT NULL
            GROUP BY number_prefix, number_postfix');
        $this->addSql('CREATE UNIQUE INDEX invoice_number_unique ON kaystrobach_invoice_domain_model_invoice (number_prefix, number_number, number_postfix)');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof \Doctrine\DBAL\Platforms\MariaDb1027Platform,
            "Migration can only be executed safely on '\Doctrine\DBAL\Platforms\MariaDb1027Platform'."
        );

        $this->addSql('DROP INDEX invoice_number_unique ON kaystrobach_invoice_domain_model_invoice');
        $this->addSql('DROP TABLE kaystrobach_invoice_numberrange');
    }
}
