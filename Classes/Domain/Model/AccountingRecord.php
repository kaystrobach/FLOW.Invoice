<?php

namespace KayStrobach\Invoice\Domain\Model;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class AccountingRecord
{
    /** @var string Haben */
    public const HAVE = 'H';
    /** @var string Soll */
    public const SHOULD = 'S';

    /**
     * @ORM\ManyToOne(cascade={"all"}, inversedBy="accountingRecords")
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var float
     */
    protected $amount;

    /**
     * Soll oder Haben
     * @var string
     */
    protected $shouldOrHave = 'H';

    /**
     * Konto
     * @var string
     */
    protected $account;

    /**
     * Gegenkonto
     * @var string
     */
    protected $offsetAccount;

    /**
     * @ORM\Column(nullable=true)
     * @var \DateTime
     */
    protected $dueDate;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var bool
     */
    protected $paid = false;

    /**
     * contains the reference to the original invoice as human readable string
     *
     * @var string
     */
    protected $belegfeld1;

    /**
     * Contains either the reference to the storno invoice or a date as human readable string
     * @var string
     */
    protected $belegfeld2;

    /**
     * @return Invoice
     */
    public function getInvoice(): Invoice
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice(Invoice $invoice): void
    {
        $this->invoice = $invoice;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getShouldOrHave(): string
    {
        return $this->shouldOrHave;
    }

    /**
     * @param string $shouldOrHave
     */
    public function setShouldOrHave(string $shouldOrHave): void
    {
        $this->shouldOrHave = $shouldOrHave;
    }

    /**
     * @return string
     */
    public function getAccount(): string
    {
        return $this->account;
    }

    /**
     * @param string $account
     */
    public function setAccount(string $account): void
    {
        $this->account = $account;
    }

    /**
     * @return string
     */
    public function getOffsetAccount(): string
    {
        return $this->offsetAccount;
    }

    /**
     * @param string $offsetAccount
     */
    public function setOffsetAccount(string $offsetAccount): void
    {
        $this->offsetAccount = $offsetAccount;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate(\DateTime $dueDate = null): void
    {
        $this->dueDate = $dueDate;
        if ($dueDate  !== null) {
            $this->setBelegfeld2($dueDate->format('dmy'));
        }
    }

    public function setDueDateDaysInFuture(int $days)
    {
        $dueDate = new \DateTime('now');
        $dueDate->add(new \DateInterval('P' . $days . 'D'));
        $dueDate->setTime(0, 0, 0);
        $this->setDueDate($dueDate);
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text): void
    {
        $this->text = $text;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paid;
    }

    /**
     * @param bool $paid
     */
    public function setPaid(bool $paid): void
    {
        $this->paid = $paid;
    }

    /**
     * @return string
     */
    public function getBelegfeld1(): string
    {
        return $this->belegfeld1;
    }

    /**
     * @param string $belegfeld1
     */
    public function setBelegfeld1(string $belegfeld1): void
    {
        $this->belegfeld1 = $belegfeld1;
    }

    /**
     * @return string
     */
    public function getBelegfeld2(): string
    {
        return $this->belegfeld2;
    }

    public function getBelegfeld2AsDateTime(): ?\DateTime
    {
        if ($this->getBelegfeld2() === '') {
            return null;
        }
        $dt = \DateTime::createFromFormat('dmy', $this->getBelegfeld2());
        if ($dt === false) {
            return null;
        }
        return $dt;
    }

    /**
     * @param string $belegfeld2
     */
    public function setBelegfeld2(string $belegfeld2): void
    {
        $this->belegfeld2 = $belegfeld2;
    }

    public static function fromInvoice(Invoice $invoice): AccountingRecord
    {
        $accountingRecord = new AccountingRecord();
        $accountingRecord->setInvoice($invoice);
        $accountingRecord->setAmount($invoice->getTotal());
        $accountingRecord->setShouldOrHave(AccountingRecord::HAVE);
        $accountingRecord->setBelegfeld1($invoice->getNumberComplete());
        return $accountingRecord;
    }

    public static function fromStornoInvoice(Invoice $invoice): AccountingRecord
    {
        if (!$invoice->getOriginalInvoice() instanceof Invoice) {
            throw new \InvalidArgumentException('originalInvoice needs to be set');
        }
        $accountingRecord = new AccountingRecord();
        $accountingRecord->setInvoice($invoice);
        $accountingRecord->setAmount($invoice->getTotal());
        $accountingRecord->setShouldOrHave(AccountingRecord::SHOULD);
        $accountingRecord->belegfeld1 = $invoice->getOriginalInvoice()->getNumberComplete();
        $accountingRecord->belegfeld2 = $invoice->getNumberComplete();
        return $accountingRecord;
    }
}
