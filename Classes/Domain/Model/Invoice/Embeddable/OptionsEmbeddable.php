<?php

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @ORM\Embeddable()
 */
class OptionsEmbeddable
{
    /**
     * @var bool
     */
    protected bool $showSinglePrices = true;

    public function isShowSinglePrices(): bool
    {
        return $this->showSinglePrices;
    }

    public function setShowSinglePrices(bool $showSinglePrices): void
    {
        $this->showSinglePrices = $showSinglePrices;
    }
}
