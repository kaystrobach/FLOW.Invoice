<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @ORM\Embeddable()
 */
class OrderEmbeddable
{
    /**
     *
     */
    protected string $orderNumber = '';

    /**
     * @Flow\Validate(type="NotEmpty", validationGroups={"finalizeInvoice"})
     */
    protected string $customerReference = '';

    /**
     * @ORM\Column(nullable=true)
     * @var DateTimeImmutable
     */
    protected ?DateTimeImmutable $commissioningDate = null;

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
    }

    public function getCustomerReference(): string
    {
        return $this->customerReference;
    }

    public function setCustomerReference(string $customerReference): void
    {
        $this->customerReference = $customerReference;
    }

    public function getCommissioningDate(): ?DateTimeImmutable
    {
        return $this->commissioningDate;
    }

    public function setCommissioningDate(?DateTimeImmutable $commissioningDate): void
    {
        $this->commissioningDate = $commissioningDate;
    }
}
