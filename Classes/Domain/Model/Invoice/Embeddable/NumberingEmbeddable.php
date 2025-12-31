<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @ORM\Embeddable()
 */
class NumberingEmbeddable implements \JsonSerializable
{
    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected string $prefix = '';

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @var int|null
     */
    protected ?int $number = null;

    /**
     * @ORM\Column(type="string", nullable=false)
     * @var string
     */
    protected string $postfix = '';

    /**
     * @var string
     */
    protected string $combinedNumber = '';

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): void
    {
        $this->prefix = $this->replacePlaceHolders($prefix);
        $this->updateCombinedNumber();
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(?int $number): void
    {
        $this->number = $number;
        $this->updateCombinedNumber();
    }

    public function getPostfix(): string
    {
        return $this->postfix;
    }

    public function setPostfix(string $postfix): void
    {
        $this->postfix = $this->replacePlaceHolders($postfix);
        $this->updateCombinedNumber();
    }

    public function getCombinedNumber(): string
    {
        return $this->combinedNumber;
    }

    public function setCombinedNumber(string $combinedNumber): void
    {
        $this->combinedNumber = $combinedNumber;
    }

    protected function replacePlaceHolders(string $string): string
    {
        $now = new \DateTime('now');
        return str_replace(array('%year', '%month'), array($now->format('Y'), $now->format('m')), $string);
    }

    public function updateCombinedNumber(bool $force = false): void
    {
        if (($this->combinedNumber !== $this->prefix) && !$force) {
            return;
        }
        $this->combinedNumber = trim($this->prefix) . $this->number . trim($this->postfix);
    }

    public function __toString(): string
    {
        return $this->combinedNumber;
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
