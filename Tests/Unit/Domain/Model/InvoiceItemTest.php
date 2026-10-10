<?php
namespace KayStrobach\Invoice\Tests\Unit\Domain\Model;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;

/**
 * Testcase for Invoice item
 */
class InvoiceItemTest extends \Neos\Flow\Tests\UnitTestCase
{
    private static function item(?int $amount, int $singlePrice, ?float $tax = 19.0): InvoiceItem
    {
        $item = new InvoiceItem();
        $item->setAmount($amount);
        $item->getSinglePrice()->setValue($singlePrice);
        $item->setTax($tax);
        return $item;
    }

    /**
     * @test
     */
    public function totalIsAmountTimesSinglePriceInCents(): void
    {
        $item = self::item(3, 1999);
        $item->calculateTotal();
        self::assertSame(5997, $item->getTotal()->getValue());
    }

    /**
     * @test
     */
    public function totalOfNegativeSinglePriceIsNegative(): void
    {
        $item = self::item(2, -1000);
        $item->calculateTotal();
        self::assertSame(-2000, $item->getTotal()->getValue());
    }

    /**
     * @test
     */
    public function totalWithoutAmountIsZero(): void
    {
        $item = self::item(null, 1000);
        $item->calculateTotal();
        self::assertSame(0, $item->getTotal()->getValue());
    }

    /**
     * @test
     */
    public function partialCloneCopiesAllFields(): void
    {
        $invoice = new Invoice();
        $item = self::item(2, 1000, 7.0);
        $item->setInvoice($invoice);
        $item->setSort(3);
        $item->setArticleReference('ART-1');
        $item->setUnit('C62');
        $item->setName('Name');
        $item->setDescription('Description');
        $item->setDiscount(5.0);
        $item->calculateTotal();

        $clone = $item->makePartialClone();

        self::assertNotSame($item, $clone);
        self::assertSame($invoice, $clone->getInvoice());
        self::assertSame(3, $clone->getSort());
        self::assertSame('ART-1', $clone->getArticleReference());
        self::assertSame(2, $clone->getAmount());
        self::assertSame('C62', $clone->getUnit());
        self::assertSame(7.0, $clone->getTax());
        self::assertSame(1000, $clone->getSinglePrice()->getValue());
        self::assertSame('Name', $clone->getName());
        self::assertSame('Description', $clone->getDescription());
        self::assertSame(2000, $clone->getTotal()->getValue());
        self::assertSame(5.0, $clone->getDiscount());
    }

    /**
     * Changing the clone (e.g. negating it for a cancellation invoice) must not touch the original
     *
     * @test
     */
    public function partialCloneDoesNotShareMoneyObjects(): void
    {
        $item = self::item(2, 1000);
        $item->calculateTotal();

        $clone = $item->makePartialClone();
        $clone->getSinglePrice()->setValue(-1000);
        $clone->getTotal()->setValue(-2000);

        self::assertSame(1000, $item->getSinglePrice()->getValue());
        self::assertSame(2000, $item->getTotal()->getValue());
    }

    /**
     * @test
     */
    public function possibleUnitsContainOnlyEnabledUnitsWithLabelFallback(): void
    {
        $item = new InvoiceItem();
        $item->setUnit('C62');
        $this->inject($item, 'possibleUnitsConfig', [
            'C62' => ['label' => 'Stück', 'enable' => true],
            'DAY' => ['enable' => true],
            'HAR' => ['label' => 'Hektar', 'enable' => false],
            'KGM' => ['label' => 'Kilogramm'],
        ]);

        self::assertSame(['C62' => 'Stück', 'DAY' => 'DAY'], $item->getPossibleUnits());
    }

    /**
     * Old invoices may use units which are disabled by now, they must stay selectable
     *
     * @test
     */
    public function possibleUnitsAlwaysContainTheCurrentUnit(): void
    {
        $item = new InvoiceItem();
        $item->setUnit('HAR');
        $this->inject($item, 'possibleUnitsConfig', [
            'C62' => ['label' => 'Stück', 'enable' => true],
            'HAR' => ['label' => 'Hektar', 'enable' => false],
        ]);

        self::assertSame(['C62' => 'Stück', 'HAR' => 'HAR'], $item->getPossibleUnits());
    }
}
