<?php
namespace KayStrobach\Invoice\Tests\Unit\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use KayStrobach\Invoice\Service\InvoiceNumberRangeService;

/**
 * Number assignment against an in-memory SQLite database (locking itself is MariaDB-specific and
 * was verified separately with concurrent processes, see Documentation/InvoiceNumbers.md)
 */
class InvoiceNumberRangeServiceTest extends \Neos\Flow\Tests\UnitTestCase
{
    private Connection $connection;

    private InvoiceNumberRangeService $service;

    public function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE kaystrobach_invoice_numberrange (persistence_object_identifier VARCHAR(40) NOT NULL PRIMARY KEY, prefix VARCHAR(255) NOT NULL, postfix VARCHAR(255) NOT NULL, lastnumber INT NOT NULL, UNIQUE (prefix, postfix))');
        $this->connection->executeStatement('CREATE TABLE kaystrobach_invoice_domain_model_invoice (persistence_object_identifier VARCHAR(40) NOT NULL PRIMARY KEY, number_prefix VARCHAR(255) NOT NULL, number_number INT DEFAULT NULL, number_postfix VARCHAR(255) NOT NULL, UNIQUE (number_prefix, number_number, number_postfix))');
        $this->service = new InvoiceNumberRangeService();
    }

    private function finalize(string $prefix, string $postfix = '', bool $commit = true): int
    {
        $this->service->ensureSeries($this->connection, $prefix, $postfix);
        $this->connection->beginTransaction();
        $number = $this->service->nextNumber($this->connection, $prefix, $postfix);
        $this->connection->insert('kaystrobach_invoice_domain_model_invoice', [
            'persistence_object_identifier' => uniqid('', true),
            'number_prefix' => $prefix,
            'number_number' => $number,
            'number_postfix' => $postfix,
        ]);
        $commit ? $this->connection->commit() : $this->connection->rollBack();
        return $number;
    }

    /**
     * @test
     */
    public function numbersArePerSeriesAndConsecutive(): void
    {
        self::assertSame(1, $this->finalize('R-2026-'));
        self::assertSame(2, $this->finalize('R-2026-'));
        self::assertSame(1, $this->finalize('S-2026-'));
        self::assertSame(1, $this->finalize('R-2026-', '-X'));
        self::assertSame(3, $this->finalize('R-2026-'));
    }

    /**
     * @test
     */
    public function rollbackDoesNotLeaveAGap(): void
    {
        self::assertSame(1, $this->finalize('R-2026-'));
        self::assertSame(2, $this->finalize('R-2026-', '', false));
        self::assertSame(2, $this->finalize('R-2026-'));
    }

    /**
     * @test
     */
    public function existingInvoicesAreTakenIntoAccount(): void
    {
        $this->connection->insert('kaystrobach_invoice_domain_model_invoice', [
            'persistence_object_identifier' => 'legacy',
            'number_prefix' => 'R-2026-',
            'number_number' => 41,
            'number_postfix' => '',
        ]);
        self::assertSame(42, $this->finalize('R-2026-'));
    }

    /**
     * @test
     */
    public function worksWithoutEnsureSeriesAsFallback(): void
    {
        $this->connection->beginTransaction();
        self::assertSame(1, $this->service->nextNumber($this->connection, 'R-2026-', ''));
        $this->connection->commit();
    }

    /**
     * @test
     */
    public function requiresATransaction(): void
    {
        $this->expectException(\LogicException::class);
        $this->service->nextNumber($this->connection, 'R-2026-', '');
    }

    /**
     * @test
     */
    public function nextNumberIsMaxOfRangeAndExistingInvoices(): void
    {
        self::assertSame(1, InvoiceNumberRangeService::next(0, 0));
        self::assertSame(11, InvoiceNumberRangeService::next(10, 7));
        self::assertSame(11, InvoiceNumberRangeService::next(7, 10));
    }
}
