<?php
namespace KayStrobach\Invoice\Tests\Unit\Domain\Model\Invoice;

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\Invoice\ListState;
use KayStrobach\Invoice\Domain\Model\SettlementDate;

class ListStateTest extends \Neos\Flow\Tests\UnitTestCase
{
    private function invoice(?int $number, array $dueDates = []): Invoice
    {
        $invoice = new Invoice();
        $invoice->getNumber()->setNumber($number);
        foreach ($dueDates as $date => $paid) {
            $settlementDate = new SettlementDate();
            $settlementDate->setDueDate(new \DateTime($date . ' 00:00:00'));
            $settlementDate->setPaid($paid);
            $invoice->getSettlementDates()->add($settlementDate);
        }
        return $invoice;
    }

    private function state(Invoice $invoice): ListState
    {
        return ListState::of($invoice, new \DateTimeImmutable('2026-10-10 14:30:00'));
    }

    /**
     * @test
     */
    public function invoiceWithoutNumberIsDraft(): void
    {
        $state = $this->state($this->invoice(null, ['2026-10-01' => false]));
        self::assertSame(ListState::DRAFT, $state->getKey());
        self::assertSame('neutral', $state->getTone());
        self::assertTrue($state->isDraft());
    }

    /**
     * @test
     */
    public function cancelledInvoiceWinsOverPaymentState(): void
    {
        $invoice = $this->invoice(7, ['2026-09-01' => false]);
        $invoice->setStornoInvoice($this->invoice(8));
        self::assertSame(ListState::CANCELLED, $this->state($invoice)->getKey());
    }

    /**
     * @test
     */
    public function cancellationInvoiceIsMarked(): void
    {
        $invoice = $this->invoice(8);
        $invoice->setOriginalInvoice($this->invoice(7));
        $state = $this->state($invoice);
        self::assertSame(ListState::CANCELLATION, $state->getKey());
        self::assertSame('back', $state->getIcon());
    }

    /**
     * @test
     */
    public function invoiceWithoutDueDatesHasNoTerms(): void
    {
        self::assertSame(ListState::NO_TERMS, $this->state($this->invoice(9))->getKey());
    }

    /**
     * @test
     */
    public function dueTodayIsDueSoonNotOverdue(): void
    {
        $state = $this->state($this->invoice(1, ['2026-10-10' => false]));
        self::assertSame(ListState::DUE_SOON, $state->getKey());
        self::assertSame(0, $state->getDays());
        self::assertSame('Heute fällig', $state->getLabel());
    }

    /**
     * @test
     */
    public function dueWithinSevenDaysIsDueSoon(): void
    {
        $state = $this->state($this->invoice(1, ['2026-10-17' => false]));
        self::assertSame(ListState::DUE_SOON, $state->getKey());
        self::assertSame('Fällig in 7 Tagen', $state->getLabel());
    }

    /**
     * @test
     */
    public function dueLaterIsOpen(): void
    {
        $state = $this->state($this->invoice(1, ['2026-10-22' => false]));
        self::assertSame(ListState::OPEN, $state->getKey());
        self::assertSame('Offen · fällig in 12 Tagen', $state->getLabel());
        self::assertTrue($state->isOutstanding());
    }

    /**
     * @test
     */
    public function oldestUnpaidDueDateDecides(): void
    {
        $state = $this->state($this->invoice(1, [
            '2026-11-08' => false,
            '2026-09-08' => true,
            '2026-10-08' => false,
        ]));
        self::assertSame(ListState::OVERDUE, $state->getKey());
        self::assertSame(2, $state->getDays());
        self::assertSame('2026-10-08', $state->getDueDate()->format('Y-m-d'));
        self::assertSame(2, $state->getInstallment());
        self::assertSame(3, $state->getInstallmentCount());
        self::assertTrue($state->hasInstallments());
        self::assertSame('Überfällig seit 2 Tagen', $state->getLabel());
    }

    /**
     * @test
     */
    public function overdueByOneDayUsesSingular(): void
    {
        self::assertSame('Überfällig seit 1 Tag', $this->state($this->invoice(1, ['2026-10-09' => false]))->getLabel());
    }

    /**
     * @test
     */
    public function allDueDatesPaidIsPaidWithLastDueDate(): void
    {
        $state = $this->state($this->invoice(1, ['2026-08-15' => true, '2026-09-15' => true]));
        self::assertSame(ListState::PAID, $state->getKey());
        self::assertSame('2026-09-15', $state->getDueDate()->format('Y-m-d'));
        self::assertNull($state->getDays());
        self::assertFalse($state->isOutstanding());
    }
}
