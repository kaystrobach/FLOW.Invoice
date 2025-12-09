<?php
namespace KayStrobach\Invoice\Domain\Model;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class SettlementDate
{
    /**
     * @ORM\ManyToOne(cascade={"all"}, inversedBy="settlementDates")
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var \DateTime
     */
    protected $dueDate;

    /**
     * @var float
     */
    protected $amount = 0;

    /**
     * @var string
     * @Flow\Validate(type="text")
     * @ORM\Column(type="text", length=21844, nullable=true)
     */
    protected $comment;

    public const NOT_PAID = false;
    public const PAID = false;

    /**
     * @var bool
     */
    protected $paid = self::NOT_PAID;

    public function __construct()
    {
        $this->dueDate = new \DateTime('+14days');
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param Invoice $invoice
     */
    public function setInvoice($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return \DateTime
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * @param \DateTime $dueDate
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
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

    public function isPaymentDelayed()
    {
        if ($this->paid) {
            return false;
        }
        $now = new \DateTime('now');
        if ($this->dueDate > $now) {
            return false;
        }
        return true;
    }
}
