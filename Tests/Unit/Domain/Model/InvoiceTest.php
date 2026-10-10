<?php
namespace KayStrobach\Invoice\Tests\Unit\Domain\Model;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\Invoice\TaxRecord;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;
use KayStrobach\Invoice\Domain\Model\SettlementDate;
use KayStrobach\Invoice\Service\StornoInvoiceService;

/**
 * Testcase for Invoice
 */
class InvoiceTest extends \Neos\Flow\Tests\UnitTestCase
{
    private static function addItem(Invoice $invoice, int $amount, int $singlePrice, ?float $tax): InvoiceItem
    {
        $item = new InvoiceItem();
        $item->setAmount($amount);
        $item->getSinglePrice()->setValue($singlePrice);
        $item->setTax($tax);
        $item->setInvoice($invoice);
        $invoice->getInvoiceItems()->add($item);
        return $item;
    }

    private static function addSettlementDate(Invoice $invoice, string $dueDate, int $amount, bool $paid = false): SettlementDate
    {
        $settlementDate = new SettlementDate();
        $settlementDate->setDueDate(new \DateTime($dueDate));
        $settlementDate->getAmount()->setValue($amount);
        $settlementDate->setPaid($paid);
        $settlementDate->setInvoice($invoice);
        $invoice->getSettlementDates()->add($settlementDate);
        return $settlementDate;
    }

    /**
     * @return array<string, TaxRecord>
     */
    private static function taxRecordsByRate(Invoice $invoice): array
    {
        $result = [];
        foreach ($invoice->getTaxRecords() as $taxRecord) {
            $result[(string)$taxRecord->getTaxRate()] = $taxRecord;
        }
        return $result;
    }

    /**
     * @test
     */
    public function calculateTotalWithSingleTaxRate(): void
    {
        $invoice = new Invoice();
        self::addItem($invoice, 2, 1000, 19.0);

        self::assertTrue($invoice->calculateTotal());

        self::assertSame(2000, $invoice->getTotalNoTaxes()->getValue());
        self::assertSame(2380, $invoice->getTotal()->getValue());
        $taxRecords = self::taxRecordsByRate($invoice);
        self::assertCount(1, $taxRecords);
        self::assertSame(380, $taxRecords['19']->getSum()->getValue());
        self::assertSame(2000, $taxRecords['19']->getSumNetBase()->getValue());
        self::assertSame($invoice, $taxRecords['19']->getInvoice());
    }

    /**
     * @test
     */
    public function calculateTotalGroupsTaxesByRate(): void
    {
        $invoice = new Invoice();
        self::addItem($invoice, 1, 10000, 19.0);
        self::addItem($invoice, 3, 333, 7.0);
        self::addItem($invoice, 1, 500, 19.0);

        $invoice->calculateTotal();

        $taxRecords = self::taxRecordsByRate($invoice);
        self::assertCount(2, $taxRecords);
        self::assertSame(10500, $taxRecords['19']->getSumNetBase()->getValue());
        self::assertSame(1995, $taxRecords['19']->getSum()->getValue());
        self::assertSame(999, $taxRecords['7']->getSumNetBase()->getValue());
        // 999 * 0.07 = 69.93
        self::assertSame(70, $taxRecords['7']->getSum()->getValue());
        self::assertSame(11499, $invoice->getTotalNoTaxes()->getValue());
        self::assertSame(11499 + 1995 + 70, $invoice->getTotal()->getValue());
    }

    /**
     * Taxes are rounded commercially once per tax rate, not per item
     *
     * @test
     */
    public function taxesAreRoundedPerRate(): void
    {
        $invoice = new Invoice();
        // 1595,36 * 19% = 303,1184
        self::addItem($invoice, 1, 159536, 19.0);
        // 0,03 * 19% = 0,0057 each, 0,57 cent on the sum of both items (instead of 0 + 0 per item)
        self::addItem($invoice, 1, 3, 19.0);

        $invoice->calculateTotal();

        // (159536 + 3) * 0.19 = 30312.41
        self::assertSame(30312, self::taxRecordsByRate($invoice)['19']->getSum()->getValue());
        self::assertSame(159539 + 30312, $invoice->getTotal()->getValue());
    }

    /**
     * @test
     */
    public function calculatingTwiceDoesNotDuplicateTaxRecords(): void
    {
        $invoice = new Invoice();
        self::addItem($invoice, 1, 1000, 19.0);
        self::addItem($invoice, 1, 1000, 7.0);

        $invoice->calculateTotal();
        $invoice->calculateTotal();

        self::assertCount(2, $invoice->getTaxRecords());
        self::assertSame(2000 + 190 + 70, $invoice->getTotal()->getValue());
    }

    /**
     * Pins the current behaviour: the "0.00" guard in updateTaxRecords() never matches,
     * so items with a tax rate of 0 still get a tax record with a sum of 0
     *
     * @test
     */
    public function itemsWithZeroTaxRateGetATaxRecordWithoutTaxes(): void
    {
        $invoice = new Invoice();
        self::addItem($invoice, 1, 1000, 19.0);
        self::addItem($invoice, 1, 500, 0.0);

        $invoice->calculateTotal();

        $taxRecords = self::taxRecordsByRate($invoice);
        self::assertCount(2, $taxRecords);
        self::assertSame(0, $taxRecords['0']->getSum()->getValue());
        self::assertSame(500, $taxRecords['0']->getSumNetBase()->getValue());
        self::assertSame(1500, $invoice->getTotalNoTaxes()->getValue());
        self::assertSame(1690, $invoice->getTotal()->getValue());
    }

    /**
     * @test
     */
    public function calculateTotalDoesNothingOnFinalizedInvoices(): void
    {
        $invoice = new Invoice();
        self::addItem($invoice, 1, 1000, 19.0);
        $invoice->calculateTotal();
        $invoice->setChangeable(false);
        self::addItem($invoice, 1, 5000, 19.0);

        self::assertFalse($invoice->calculateTotal());

        self::assertSame(1190, $invoice->getTotal()->getValue());
        self::assertSame(1000, $invoice->getTotalNoTaxes()->getValue());
    }

    /**
     * @test
     */
    public function invoiceWithoutSettlementDatesIsPaidAndNotDelayed(): void
    {
        $invoice = new Invoice();
        self::assertTrue($invoice->isPaid());
        self::assertFalse($invoice->isPaymentDelayed());
    }

    /**
     * @test
     */
    public function invoiceIsPaidOnlyIfAllSettlementDatesArePaid(): void
    {
        $invoice = new Invoice();
        self::addSettlementDate($invoice, '-1 day', 100, true);
        $open = self::addSettlementDate($invoice, '+1 month', 100);

        self::assertFalse($invoice->isPaid());
        $open->setPaid(true);
        self::assertTrue($invoice->isPaid());
    }

    /**
     * @test
     */
    public function paymentIsDelayedIfAnOpenSettlementDateIsOverdue(): void
    {
        $invoice = new Invoice();
        self::addSettlementDate($invoice, '-1 month', 100, true);
        self::addSettlementDate($invoice, '+1 month', 100);
        self::assertFalse($invoice->isPaymentDelayed());

        self::addSettlementDate($invoice, '-1 day', 100);
        self::assertTrue($invoice->isPaymentDelayed());
    }

    /**
     * @test
     */
    public function markSettlementDatePaidByDateUpdatesMatchingSettlementDatesAndAccountingRecords(): void
    {
        $invoice = new Invoice();
        $matching = self::addSettlementDate($invoice, '2026-03-01 08:00', 100);
        $other = self::addSettlementDate($invoice, '2026-04-01', 100);

        $matchingRecord = new AccountingRecord();
        $matchingRecord->setDueDate(new \DateTime('2026-03-01 17:00'));
        $recordWithoutDueDate = new AccountingRecord();
        $invoice->getAccountingRecords()->add($matchingRecord);
        $invoice->getAccountingRecords()->add($recordWithoutDueDate);

        $invoice->markSettlementDatePaidByDate(new \DateTime('2026-03-01 12:00'), true);

        self::assertTrue($matching->isPaid());
        self::assertFalse($other->isPaid());
        self::assertTrue($matchingRecord->isPaid());
        self::assertFalse($recordWithoutDueDate->isPaid());
    }

    /**
     * @test
     */
    public function settlementDatesSumAddsUpTheAmounts(): void
    {
        $invoice = new Invoice();
        self::addSettlementDate($invoice, '+1 day', 1000);
        self::addSettlementDate($invoice, '+1 month', 1380);

        self::assertSame(2380, $invoice->getSettlementDatesSum());
    }

    /**
     * @test
     */
    public function partialCloneCopiesHeaderDataAndGetsAFreshNumber(): void
    {
        $invoice = new Invoice();
        $invoice->setTitle('Rechnung');
        $invoice->setSubTitle('Projekt X');
        $invoice->setType('Special');
        $invoice->setOfferNumber('A-1');
        $invoice->setPaymentTermText('14 Tage');
        $invoice->setPreText('pre');
        $invoice->setPostText('post');
        $invoice->setDate(new \DateTime('2020-01-01'));
        $invoice->getNumber()->setPrefix('R-2020-');
        $invoice->getNumber()->setPostfix('-X');
        $invoice->getNumber()->setNumber(42);
        $invoice->getNumber()->updateCombinedNumber(true);
        $invoice->getCustomer()->setName('Customer');
        $invoice->getSeller()->setName('Seller');
        self::addItem($invoice, 1, 1000, 19.0);
        self::addSettlementDate($invoice, '+1 day', 1190);

        $clone = $invoice->makePartialClone();

        self::assertSame('Rechnung', $clone->getTitle());
        self::assertSame('Projekt X', $clone->getSubTitle());
        self::assertSame('Special', $clone->getType());
        self::assertSame('A-1', $clone->getOfferNumber());
        self::assertSame('14 Tage', $clone->getPaymentTermText());
        self::assertSame('pre', $clone->getPreText());
        self::assertSame('post', $clone->getPostText());
        self::assertSame('R-2020-', $clone->getNumber()->getPrefix());
        self::assertSame('-X', $clone->getNumber()->getPostfix());
        self::assertNull($clone->getNumber()->getNumber());
        self::assertSame('R-2020--X', $clone->getNumber()->getCombinedNumber());
        self::assertSame('Customer', $clone->getCustomer()->getName());
        self::assertNotSame($invoice->getCustomer(), $clone->getCustomer());
        self::assertSame('Seller', $clone->getSeller()->getName());
        self::assertNotSame($invoice->getSeller(), $clone->getSeller());
        self::assertSame(date('Y'), $clone->getYear());
        self::assertSame(date('Y-m-d'), $clone->getDate()->format('Y-m-d'));
        self::assertCount(0, $clone->getInvoiceItems());
        self::assertCount(0, $clone->getSettlementDates());
        self::assertFalse($clone->isChangeable());
    }

    /**
     * Recalculating the clone must not change the totals of the original invoice
     *
     * @test
     */
    public function partialCloneDoesNotShareTotals(): void
    {
        $invoice = new Invoice();
        self::addItem($invoice, 1, 1000, 19.0);
        $invoice->calculateTotal();

        $clone = $invoice->makePartialClone();
        $clone->getTotal()->setValue(1);
        $clone->getTotalNoTaxes()->setValue(1);

        self::assertSame(1190, $invoice->getTotal()->getValue());
        self::assertSame(1000, $invoice->getTotalNoTaxes()->getValue());
    }

    /**
     * @test
     */
    public function bankTransferDocumentsRequireAFinalizedInvoice(): void
    {
        $invoice = new Invoice();
        $this->expectException(\InvalidArgumentException::class);
        $invoice->makeBankTransferDocument();
    }

    /**
     * @test
     */
    public function cancellationInvoiceIsDelegatedToTheService(): void
    {
        $invoice = new Invoice();
        $cancellation = new Invoice();
        $service = $this->createMock(StornoInvoiceService::class);
        $service->expects(self::once())
            ->method('makeStornoInvoice')
            ->with($invoice, null, null, true)
            ->willReturn($cancellation);
        $this->inject($invoice, 'stornoInvoiceService', $service);

        self::assertSame($cancellation, $invoice->makeCancellationInvoice(null, null, true));
    }

    /**
     * @test
     */
    public function invoiceCanOnlyBeCancelledOnce(): void
    {
        $invoice = new Invoice();
        $invoice->setStornoInvoice(new Invoice());
        $service = $this->createMock(StornoInvoiceService::class);
        $service->expects(self::never())->method('makeStornoInvoice');
        $this->inject($invoice, 'stornoInvoiceService', $service);

        self::assertNull($invoice->makeCancellationInvoice());
    }

    /**
     * @test
     */
    public function cancellationInvoiceCanNotBeCancelled(): void
    {
        $cancellation = new Invoice();
        $cancellation->setOriginalInvoice(new Invoice());
        $service = $this->createMock(StornoInvoiceService::class);
        $service->expects(self::never())->method('makeStornoInvoice');
        $this->inject($cancellation, 'stornoInvoiceService', $service);

        self::assertNull($cancellation->makeCancellationInvoice());
    }
}
