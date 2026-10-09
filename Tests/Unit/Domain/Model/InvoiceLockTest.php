<?php
namespace KayStrobach\Invoice\Tests\Unit\Domain\Model;

use KayStrobach\Invoice\Domain\Model\Invoice;

/**
 * Invoices with a number must never become deletable again
 */
class InvoiceLockTest extends \Neos\Flow\Tests\UnitTestCase
{
    /**
     * @test
     */
    public function draftWithoutNumberIsDeletable(): void
    {
        $invoice = new Invoice();
        $invoice->setChangeable(true);
        self::assertTrue($invoice->isDeletable());
    }

    /**
     * @test
     */
    public function invoiceWithNumberIsNotDeletableEvenIfUnlocked(): void
    {
        $invoice = new Invoice();
        $invoice->setChangeable(true);
        $invoice->getNumber()->setNumber(42);
        self::assertFalse($invoice->isDeletable());
    }

    /**
     * @test
     */
    public function finalizedInvoiceIsNotDeletable(): void
    {
        $invoice = new Invoice();
        $invoice->setChangeable(false);
        self::assertFalse($invoice->isDeletable());
    }
}
