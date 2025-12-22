<?php

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 *
 */
class SellerEmbeddable extends AddressEmbeddable
{
    /**
     * @var string
     */
    protected $receiverBic = '';

    /**
     * @var string
     */
    protected $receiverName = '';

    /**
     * @var string
     */
    protected $receiverIban = '';

    public function getReceiverBic(): string
    {
        return $this->receiverBic;
    }

    public function setReceiverBic(string $receiverBic): void
    {
        $this->receiverBic = $receiverBic;
    }

    public function getReceiverName(): string
    {
        return $this->receiverName;
    }

    public function setReceiverName(string $receiverName): void
    {
        $this->receiverName = $receiverName;
    }

    public function getReceiverIban(): string
    {
        return $this->receiverIban;
    }

    public function setReceiverIban(string $receiverIban): void
    {
        $this->receiverIban = $receiverIban;
    }
}
