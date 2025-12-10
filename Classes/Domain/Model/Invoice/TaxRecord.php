<?php
namespace KayStrobach\Invoice\Domain\Model\Invoice;

use KayStrobach\Invoice\Domain\Model\Invoice;
use Money\Exception;
use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */
class TaxRecord
{
    /**
     * @ORM\ManyToOne(inversedBy="taxRecords", cascade={"persist"})
     * @var Invoice
     */
    protected $invoice;

    /**
     * @var float
     */
    protected $taxRate;

    /**
     * @ORM\Embedded(columnPrefix="sum_")
     * @var Invoice\Embeddable\MoneyEmbeddable
     */
    protected $sum;

    public function __construct()
    {
        $this->sum = new Invoice\Embeddable\MoneyEmbeddable();
    }

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
    public function getTaxRate(): float
    {
        return $this->taxRate ?? 0.0;
    }

    /**
     * @param float $taxRate
     */
    public function setTaxRate(float $taxRate): void
    {
        $this->taxRate = $taxRate;
    }

    /**
     * @return Invoice\Embeddable\MoneyEmbeddable
     */
    public function getSum(): Invoice\Embeddable\MoneyEmbeddable
    {
        return $this->sum;
    }

    /**
     * @param Invoice\Embeddable\MoneyEmbeddable $sum
     */
    public function setSum(Invoice\Embeddable\MoneyEmbeddable $sum): void
    {
        $this->sum = $sum;
    }


}
