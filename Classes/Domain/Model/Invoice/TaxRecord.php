<?php
namespace KayStrobach\Invoice\Domain\Model\Invoice;

use KayStrobach\Invoice\Domain\Model\Invoice;
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
     * @var float
     */
    protected $sum;

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
     * @return float
     */
    public function getSum(): float
    {
        return $this->sum ?? 0.0;
    }

    /**
     * @param float $sum
     */
    public function setSum(float $sum): void
    {
        $this->sum = $sum;
    }


}
