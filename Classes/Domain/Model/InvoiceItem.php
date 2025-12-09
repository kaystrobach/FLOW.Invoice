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
class InvoiceItem
{
    /**
     * @ORM\ManyToOne(cascade={"all"}, inversedBy="invoiceItems")
     * @var Invoice
     */
    protected $invoice;

    /**
     * @ORM\Column(nullable=true)
     * @var int
     */
    protected $amount;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $unit;

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $tax;

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $singlePrice;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $name;

    /**
     * @var string
     * @Flow\Validate(type="text")
     * @ORM\Column(type="text", length=21844, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $total;

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $discount;

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

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount = null)
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getUnit(): ?string
    {
        return $this->unit;
    }

    /**
     * @param string $unit
     */
    public function setUnit($unit = null)
    {
        $this->unit = $unit;
    }

    /**
     * @return float
     */
    public function getTax(): ?float
    {
        return $this->tax;
    }

    /**
     * @param float $tax
     */
    public function setTax($tax = null)
    {
        $this->tax = $tax;
    }

    /**
     * @return float
     */
    public function getSinglePrice(): ?float
    {
        return $this->singlePrice;
    }

    /**
     * @param float $singlePrice
     */
    public function setSinglePrice($singlePrice = null)
    {
        $this->singlePrice = $singlePrice;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name = null)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description = null)
    {
        $this->description = $description;
    }

    /**
     * @return float
     */
    public function getTotal(): ?float
    {
        return $this->total;
    }

    /**
     * @param float $total
     */
    public function setTotal($total = null)
    {
        $this->total = $total;
    }

    public function calculateTotal()
    {
        $this->total = $this->getAmount() * $this->getSinglePrice();
    }

    /**
     * @return float
     */
    public function getDiscount(): ?float
    {
        return $this->discount;
    }

    /**
     * @param float $discount
     */
    public function setDiscount($discount = null)
    {
        $this->discount = $discount;
    }

    public function makePartialClone(): InvoiceItem
    {
        $clone = new InvoiceItem();
        $clone->setInvoice($this->invoice);
        $clone->setAmount($this->amount);
        $clone->setUnit($this->unit);
        $clone->setTax($this->tax);
        $clone->setSinglePrice($this->singlePrice);
        $clone->setName($this->name);
        $clone->setDescription($this->description);
        $clone->setTotal($this->total);
        $clone->setDiscount($this->discount);
        return $clone;
    }
}
