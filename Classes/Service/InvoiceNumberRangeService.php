<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Service;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SqlitePlatform;
use Neos\Flow\Annotations as Flow;

/**
 * Assigns the next invoice number of a series (prefix + postfix) without gaps and without duplicates.
 *
 * The series row in kaystrobach_invoice_numberrange is locked with SELECT … FOR UPDATE, so a second
 * finalization of the same series waits until the first transaction is committed. MAX(number) of the
 * invoices is taken into account as well, so the series stays correct even if invoices were numbered
 * by other means (old code, imports).
 *
 * Must be called inside a transaction; the lock is held until commit/rollback. On rollback the
 * number is not used and the next finalization gets the same number again – no gap.
 *
 * @Flow\Scope("singleton")
 */
class InvoiceNumberRangeService
{
    public const RANGE_TABLE = 'kaystrobach_invoice_numberrange';
    public const INVOICE_TABLE = 'kaystrobach_invoice_domain_model_invoice';

    /**
     * Create the series row if it does not exist yet – call this BEFORE opening the transaction.
     *
     * An INSERT IGNORE inside the transaction would take a shared lock on the existing index entry;
     * two finalizations would then both wait for the exclusive lock of FOR UPDATE (deadlock).
     * Outside a transaction the statement commits immediately and concurrent calls are harmless.
     */
    public function ensureSeries(Connection $connection, string $prefix, string $postfix): void
    {
        if ($connection->isTransactionActive()) {
            return; // nextNumber() creates it as fallback
        }
        $this->insertSeries($connection, $prefix, $postfix);
    }

    public function nextNumber(Connection $connection, string $prefix, string $postfix): int
    {
        if (!$connection->isTransactionActive()) {
            throw new \LogicException('Invoice numbers must be assigned inside a transaction.', 1760000101);
        }

        // lock the series – everything below runs for one finalization at a time
        $last = $this->lockSeries($connection, $prefix, $postfix);
        if ($last === null) {
            // fallback if ensureSeries() was not called before the transaction
            $this->insertSeries($connection, $prefix, $postfix);
            $last = (int)$this->lockSeries($connection, $prefix, $postfix);
        }
        $maxExisting = (int)$connection->fetchOne(
            'SELECT MAX(number_number) FROM ' . self::INVOICE_TABLE . ' WHERE number_prefix = ? AND number_postfix = ?',
            [$prefix, $postfix]
        );

        $next = self::next($last, $maxExisting);
        $connection->update(self::RANGE_TABLE, ['lastnumber' => $next], ['prefix' => $prefix, 'postfix' => $postfix]);
        return $next;
    }

    private function lockSeries(Connection $connection, string $prefix, string $postfix): ?int
    {
        $last = $connection->fetchOne(
            'SELECT lastnumber FROM ' . self::RANGE_TABLE . ' WHERE prefix = ? AND postfix = ?'
            . (self::isSqlite($connection) ? '' : ' FOR UPDATE'),
            [$prefix, $postfix]
        );
        return $last === false ? null : (int)$last;
    }

    private function insertSeries(Connection $connection, string $prefix, string $postfix): void
    {
        $connection->executeStatement(
            (self::isSqlite($connection) ? 'INSERT OR IGNORE' : 'INSERT IGNORE')
            . ' INTO ' . self::RANGE_TABLE . ' (persistence_object_identifier, prefix, postfix, lastnumber) VALUES (?, ?, ?, 0)',
            [self::uuid(), $prefix, $postfix]
        );
    }

    private static function isSqlite(Connection $connection): bool
    {
        return $connection->getDatabasePlatform() instanceof SqlitePlatform;
    }

    public static function next(int $lastNumber, int $maxExistingNumber): int
    {
        return max($lastNumber, $maxExistingNumber) + 1;
    }

    private static function uuid(): string
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($bytes), 4));
    }
}
