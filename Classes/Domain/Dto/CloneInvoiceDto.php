<?php

namespace KayStrobach\Invoice\Domain\Dto;

use Neos\Flow\Annotations as Flow;

use DateTimeImmutable;
use KayStrobach\Invoice\Domain\Model\Invoice;

class CloneInvoiceDto
{
    protected Invoice $invoice;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var DateTimeImmutable
     */
    protected ?DateTimeImmutable $periodOfPerformanceStart = null;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var ?DateTimeImmutable DateTimeImmutable = null
     */
    protected ?DateTimeImmutable $periodOfPerformanceEnd = null;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    public function getPeriodOfPerformanceStart(): ?DateTimeImmutable
    {
        return $this->periodOfPerformanceStart;
    }

    public function setPeriodOfPerformanceStart(?DateTimeImmutable $periodOfPerformanceStart): void
    {
        $this->periodOfPerformanceStart = $periodOfPerformanceStart;
    }

    public function getPeriodOfPerformanceEnd(): ?DateTimeImmutable
    {
        return $this->periodOfPerformanceEnd;
    }

    public function setPeriodOfPerformanceEnd(?DateTimeImmutable $periodOfPerformanceEnd): void
    {
        $this->periodOfPerformanceEnd = $periodOfPerformanceEnd;
    }
}
