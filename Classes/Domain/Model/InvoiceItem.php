<?php
namespace KayStrobach\Invoice\Domain\Model;

/*
 * This file is part of the KayStrobach.Invoice package.
 */

use KayStrobach\Invoice\Domain\Model\Invoice\Embeddable\MoneyEmbeddable;
use KayStrobach\Invoice\Domain\Model\Invoice\Embeddable\NumberingEmbeddable;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class InvoiceItem
{
    /**
     * @Flow\Validate(type="NotEmpty")
     * @ORM\ManyToOne(cascade={"persist"}, inversedBy="invoiceItems")
     * @var Invoice
     */
    protected $invoice;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @Flow\Validate(type="Integer")
     * @var int
     */
    protected int $sort = 0;

    /**
     * @ORM\Column(nullable=true)
     * @var int
     */
    protected $amount;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $unit;


    /**
     * @Flow\Transient
     * @Flow\InjectConfiguration(path="CodeTables.UnitOfMeasure", package="KayStrobach.Invoice")
     * @var array
     */
    protected $possibleUnitsConfig = [];

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $tax;

    /**
     * @ORM\Embedded(columnPrefix="singleprice_")
     * @var MoneyEmbeddable
     */
    protected MoneyEmbeddable $singlePrice;

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $articleReference = '';

    /**
     * @ORM\Column(nullable=true)
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     * @Flow\Validate(type="text")
     * @ORM\Column(type="text", length=21844, nullable=true)
     */
    protected $description = '';

    /**
     * @ORM\Embedded(columnPrefix="total_")
     * @var MoneyEmbeddable
     */
    protected MoneyEmbeddable $total;

    /**
     * @ORM\Column(nullable=true)
     * @var float
     */
    protected $discount;

    public function __construct()
    {
        $this->total = new MoneyEmbeddable();
        $this->singlePrice = new MoneyEmbeddable();
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

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }

    public function getArticleReference(): string
    {
        return $this->articleReference ?? '';
    }

    public function setArticleReference(string $articleReference): void
    {
        $this->articleReference = $articleReference;
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

    public function getPossibleUnits(): array
    {
        $found = [];

        foreach ($this->possibleUnitsConfig as $key => $unit) {
            if ((empty($unit['enable'])) || !$unit['enable']) {
                continue;
            }
            $found[$key] = $unit['label'] ?? $key;
        }

        if (empty($found[$this->getUnit()])) {
            $found[$this->getUnit()] = $this->getUnit();
        }

        return $found;
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
     * @return MoneyEmbeddable
     */
    public function getSinglePrice(): MoneyEmbeddable
    {
        return $this->singlePrice;
    }

    /**
     * @param MoneyEmbeddable $singlePrice
     */
    public function setSinglePrice(MoneyEmbeddable $singlePrice)
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
     * @return MoneyEmbeddable
     */
    public function getTotal(): MoneyEmbeddable
    {
        return $this->total;
    }

    /**
     * @param MoneyEmbeddable $total
     */
    public function setTotal(MoneyEmbeddable $total)
    {
        $this->total = $total;
    }

    public function calculateTotal()
    {
        // 1595,36 * 19 > 313,1184 --> runden kaufmännisch ab 5 aufrunden --> 303,12
        $this->total->setValue($this->getAmount() * $this->getSinglePrice()->getValue());
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
        $clone->setTotal(clone $this->total);
        return $clone;
    }
}
