<?php
namespace KayStrobach\Invoice\Tests\Unit\Service;

use KayStrobach\Invoice\Domain\Factory\InvoiceFactory;
use KayStrobach\Invoice\Domain\Model\AccountingRecord;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;
use KayStrobach\Invoice\Domain\Repository\InvoiceRepository;
use KayStrobach\Invoice\Service\CreateCompleteElectronicInvoiceService;
use KayStrobach\Invoice\Service\StornoInvoiceService;
use PHPUnit\Framework\MockObject\MockObject;

class StornoInvoiceServiceTest extends \Neos\Flow\Tests\UnitTestCase
{
    private StornoInvoiceService $service;

    private InvoiceRepository&MockObject $invoiceRepository;

    private InvoiceFactory&MockObject $invoiceFactory;

    private Invoice $original;

    private InvoiceItem $originalItem;

    private AccountingRecord $originalAccountingRecord;

    public function setUp(): void
    {
        $this->invoiceRepository = $this->createMock(InvoiceRepository::class);
        $this->invoiceFactory = $this->createMock(InvoiceFactory::class);
        $this->service = new StornoInvoiceService();
        $this->inject($this->service, 'invoiceRepository', $this->invoiceRepository);
        $this->inject($this->service, 'invoiceFactory', $this->invoiceFactory);

        $this->original = new Invoice();
        $this->original->setTitle('Rechnung');
        $this->original->getNumber()->setPrefix('R-2026-');
        $this->original->getNumber()->setNumber(7);
        $this->original->getNumber()->updateCombinedNumber(true);

        $this->originalItem = new InvoiceItem();
        $this->originalItem->setAmount(2);
        $this->originalItem->getSinglePrice()->setValue(1000);
        $this->originalItem->setTax(19.0);
        $this->originalItem->setInvoice($this->original);
        $this->original->getInvoiceItems()->add($this->originalItem);
        $this->original->calculateTotal();

        $this->originalAccountingRecord = new AccountingRecord();
        $this->originalAccountingRecord->setInvoice($this->original);
        $this->originalAccountingRecord->setText('Erlös');
        $this->originalAccountingRecord->setDueDate(new \DateTime('2026-03-01'));
        $this->originalAccountingRecord->setBelegfeld2('2026-03-01');
        $this->original->getAccountingRecords()->add($this->originalAccountingRecord);

        $this->original->setChangeable(false);
    }

    private function makeDraftStorno(): Invoice
    {
        return $this->service->makeStornoInvoice($this->original, null, null, true);
    }

    /**
     * @test
     */
    public function originalInvoiceIsNotModified(): void
    {
        $this->makeDraftStorno();

        self::assertSame(1000, $this->originalItem->getSinglePrice()->getValue());
        self::assertSame(2000, $this->originalItem->getTotal()->getValue());
        self::assertSame(2380, $this->original->getTotal()->getValue());
        self::assertSame(2000, $this->original->getTotalNoTaxes()->getValue());
        self::assertSame(380, $this->original->getTaxRecords()->first()->getSum()->getValue());
        self::assertCount(1, $this->original->getInvoiceItems());
        self::assertCount(1, $this->original->getAccountingRecords());
        self::assertSame(AccountingRecord::HAVE, $this->originalAccountingRecord->getShouldOrHave());
        self::assertSame('Erlös', $this->originalAccountingRecord->getText());
        self::assertNotNull($this->originalAccountingRecord->getDueDate());
        self::assertSame($this->original, $this->originalAccountingRecord->getInvoice());
        self::assertSame('R-2026-7', $this->original->getNumber()->getCombinedNumber());
    }

    /**
     * @test
     */
    public function stornoHasHeaderDataReferencingTheOriginal(): void
    {
        $storno = $this->makeDraftStorno();

        self::assertNotSame($this->original, $storno);
        self::assertSame('StornoBilling', $storno->getType());
        self::assertSame('Stornorechnung', $storno->getTitle());
        self::assertSame('zu R-2026-7', $storno->getSubTitle());
        self::assertNull($storno->getNumber()->getNumber());
        self::assertSame($storno, $this->original->getStornoInvoice());
    }

    /**
     * @test
     */
    public function stornoTotalsAreNegated(): void
    {
        $storno = $this->makeDraftStorno();

        self::assertSame(-2380, $storno->getTotal()->getValue());
        self::assertSame(-2000, $storno->getTotalNoTaxes()->getValue());
        self::assertSame('EUR', $storno->getTotal()->getCurrency());
    }

    /**
     * @test
     */
    public function stornoItemsAreNegated(): void
    {
        $storno = $this->makeDraftStorno();

        self::assertCount(1, $storno->getInvoiceItems());
        /** @var InvoiceItem $item */
        $item = $storno->getInvoiceItems()->first();
        self::assertNotSame($this->originalItem, $item);
        self::assertSame($storno, $item->getInvoice());
        self::assertSame(2, $item->getAmount());
        self::assertSame(19.0, $item->getTax());
        self::assertSame(-1000, $item->getSinglePrice()->getValue());
        self::assertSame(-2000, $item->getTotal()->getValue());
    }

    /**
     * The net base is needed for the tax breakdown of the electronic invoice
     *
     * @test
     */
    public function stornoTaxRecordsAreNegated(): void
    {
        $storno = $this->makeDraftStorno();

        self::assertCount(1, $storno->getTaxRecords());
        $taxRecord = $storno->getTaxRecords()->first();
        self::assertSame($storno, $taxRecord->getInvoice());
        self::assertSame(19.0, $taxRecord->getTaxRate());
        self::assertSame(-380, $taxRecord->getSum()->getValue());
        self::assertSame(-2000, $taxRecord->getSumNetBase()->getValue());
    }

    /**
     * @test
     */
    public function stornoAccountingRecordsAreReversed(): void
    {
        $storno = $this->makeDraftStorno();

        self::assertCount(1, $storno->getAccountingRecords());
        /** @var AccountingRecord $record */
        $record = $storno->getAccountingRecords()->first();
        self::assertNotSame($this->originalAccountingRecord, $record);
        self::assertSame($storno, $record->getInvoice());
        self::assertSame(AccountingRecord::SHOULD, $record->getShouldOrHave());
        self::assertNull($record->getDueDate());
        self::assertSame('Erlös,Storno R-2026-7', $record->getText());
        self::assertSame($storno->getNumber()->getCombinedNumber(), $record->getBelegfeld2());
    }

    /**
     * @test
     */
    public function draftStornoStaysChangeableAndIsNotFinalized(): void
    {
        $this->invoiceFactory->expects(self::never())->method('setInvoiceNumber');

        $storno = $this->makeDraftStorno();

        self::assertTrue($storno->isChangeable());
    }

    /**
     * @test
     */
    public function stornoIsPersistedTogetherWithTheOriginal(): void
    {
        $this->invoiceRepository->expects(self::once())
            ->method('add')
            ->with(self::isInstanceOf(Invoice::class));
        $this->invoiceRepository->expects(self::once())
            ->method('updateAndPersist')
            ->with(self::identicalTo($this->original));
        $this->invoiceFactory->expects(self::once())
            ->method('setNumberDefaults')
            ->with(self::isInstanceOf(Invoice::class));
        $this->invoiceFactory->expects(self::once())
            ->method('setTitle')
            ->with(self::isInstanceOf(Invoice::class));

        $this->makeDraftStorno();
    }

    /**
     * @test
     */
    public function callbackGetsTheStornoBeforeItIsFinalized(): void
    {
        $received = null;
        $this->service->makeStornoInvoice($this->original, null, function (Invoice $storno) use (&$received) {
            $received = $storno;
        }, true);

        self::assertSame($this->original->getStornoInvoice(), $received);
    }

    /**
     * @test
     */
    public function stornoIsFinalizedByDefault(): void
    {
        $this->invoiceFactory->expects(self::once())
            ->method('setInvoiceNumber')
            ->with(self::isInstanceOf(Invoice::class));
        $electronicInvoiceService = $this->createMock(CreateCompleteElectronicInvoiceService::class);
        $electronicInvoiceService->expects(self::once())->method('addRenderedPdfToInvoice');

        // the storno is created inside the service, so the document service is injected via the callback
        $storno = $this->service->makeStornoInvoice($this->original, null, function (Invoice $storno) use ($electronicInvoiceService) {
            $this->inject($storno, 'completeElectronicInvoiceService', $electronicInvoiceService);
        });

        self::assertFalse($storno->isChangeable());
    }
}
