<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Stringable;

/**
 * @ORM\Embeddable()
 */
class MoneyEmbeddable implements Stringable
{
    /**
     * @var int
     */
    protected int $value = 0;

    /**
     * @var string
     */
    protected string $currency = 'EUR';

    public function getValue(): int
    {
        return $this->value;
    }

    public function setValue(int $value): void
    {
        $this->value = $value;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): void
    {
        $this->currency = $currency;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s %s',
            number_format($this->getValue() / 100, 2, ',', '.'),
            $this->getCurrency()
        );
    }
}
