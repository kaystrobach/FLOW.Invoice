<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Model\Invoice;

use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Domain\Model\SettlementDate;

/**
 * Status of an invoice as shown in the list: one key plus everything the row needs to render it
 * (badge tone, icon, label, the relevant due date and the number of days).
 *
 * Tone and icon keys are shared with other lists (orders, leads), see Documentation/List.md.
 */
final class ListState
{
    public const DRAFT = 'draft';
    public const OPEN = 'open';
    public const DUE_SOON = 'due_soon';
    public const OVERDUE = 'overdue';
    public const PAID = 'paid';
    public const NO_TERMS = 'no_terms';
    public const CANCELLED = 'cancelled';
    public const CANCELLATION = 'cancellation';

    private const TONES = [
        self::DRAFT => 'neutral',
        self::OPEN => 'info',
        self::DUE_SOON => 'warn',
        self::OVERDUE => 'alert',
        self::PAID => 'ok',
        self::NO_TERMS => 'muted',
        self::CANCELLED => 'muted',
        self::CANCELLATION => 'violet',
    ];

    private const ICONS = [
        self::DRAFT => 'edit',
        self::OPEN => 'circle',
        self::DUE_SOON => 'clock',
        self::OVERDUE => 'alert',
        self::PAID => 'check',
        self::NO_TERMS => 'circle',
        self::CANCELLED => 'slash',
        self::CANCELLATION => 'back',
    ];

    private function __construct(
        private readonly string $key,
        private readonly ?\DateTimeInterface $dueDate = null,
        private readonly ?int $days = null,
        private readonly int $installment = 0,
        private readonly int $installmentCount = 0
    ) {
    }

    public static function of(Invoice $invoice, \DateTimeInterface $today, int $dueSoonDays = 7): self
    {
        if ($invoice->getStornoInvoice() !== null) {
            return new self(self::CANCELLED);
        }
        if ($invoice->getOriginalInvoice() !== null) {
            return new self(self::CANCELLATION);
        }
        if ($invoice->getNumber()->getNumber() === null) {
            return new self(self::DRAFT);
        }

        $dates = [];
        foreach ($invoice->getSettlementDates() as $settlementDate) {
            if ($settlementDate instanceof SettlementDate && $settlementDate->getDueDate() instanceof \DateTimeInterface) {
                $dates[] = $settlementDate;
            }
        }
        if ($dates === []) {
            return new self(self::NO_TERMS);
        }
        usort($dates, static fn (SettlementDate $a, SettlementDate $b) => $a->getDueDate() <=> $b->getDueDate());
        $count = count($dates);

        foreach ($dates as $index => $settlementDate) {
            if ($settlementDate->isPaid()) {
                continue;
            }
            $days = self::daysBetween($today, $settlementDate->getDueDate());
            $key = match (true) {
                $days < 0 => self::OVERDUE,
                $days <= $dueSoonDays => self::DUE_SOON,
                default => self::OPEN,
            };
            return new self($key, $settlementDate->getDueDate(), abs($days), $index + 1, $count);
        }

        return new self(self::PAID, $dates[$count - 1]->getDueDate(), null, $count, $count);
    }

    /**
     * Whole calendar days from $from to $to, negative if $to lies before $from. Time of day is ignored.
     */
    private static function daysBetween(\DateTimeInterface $from, \DateTimeInterface $to): int
    {
        $start = new \DateTimeImmutable($from->format('Y-m-d'));
        $end = new \DateTimeImmutable($to->format('Y-m-d'));
        return (int)$start->diff($end)->format('%r%a');
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getTone(): string
    {
        return self::TONES[$this->key];
    }

    public function getIcon(): string
    {
        return self::ICONS[$this->key];
    }

    public function getLabel(): string
    {
        $days = $this->days ?? 0;
        return match ($this->key) {
            self::DRAFT => 'Entwurf',
            self::OPEN => sprintf('Offen · fällig in %d Tagen', $days),
            self::DUE_SOON => match ($days) {
                0 => 'Heute fällig',
                1 => 'Morgen fällig',
                default => sprintf('Fällig in %d Tagen', $days),
            },
            self::OVERDUE => $days === 1 ? 'Überfällig seit 1 Tag' : sprintf('Überfällig seit %d Tagen', $days),
            self::PAID => 'Bezahlt',
            self::NO_TERMS => 'Ohne Fälligkeit',
            self::CANCELLED => 'Storniert',
            self::CANCELLATION => 'Stornorechnung',
        };
    }

    /**
     * Next unpaid due date (oldest one if overdue), or the last due date once everything is paid
     */
    public function getDueDate(): ?\DateTimeInterface
    {
        return $this->dueDate;
    }

    /**
     * Days until the due date (open, due soon) or since the due date (overdue)
     */
    public function getDays(): ?int
    {
        return $this->days;
    }

    /**
     * Position of the relevant due date among all due dates, 1-based
     */
    public function getInstallment(): int
    {
        return $this->installment;
    }

    public function getInstallmentCount(): int
    {
        return $this->installmentCount;
    }

    public function hasInstallments(): bool
    {
        return $this->installmentCount > 1;
    }

    public function isDraft(): bool
    {
        return $this->key === self::DRAFT;
    }

    public function isCancelled(): bool
    {
        return $this->key === self::CANCELLED;
    }

    public function isPaid(): bool
    {
        return $this->key === self::PAID;
    }

    /**
     * Open, due soon or overdue
     */
    public function isOutstanding(): bool
    {
        return in_array($this->key, [self::OPEN, self::DUE_SOON, self::OVERDUE], true);
    }
}
