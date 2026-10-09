<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Last assigned number per number series (prefix + postfix).
 *
 * Only read and written by InvoiceNumberRangeService via DBAL with a row lock (SELECT … FOR UPDATE),
 * so concurrent finalizations are serialized per series. The entity exists for the schema only.
 *
 * @Flow\Entity
 * @ORM\Table(
 *     name="kaystrobach_invoice_numberrange",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="invoice_numberrange_series", columns={"prefix", "postfix"})}
 * )
 */
class InvoiceNumberRange
{
    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    protected string $prefix = '';

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @var string
     */
    protected string $postfix = '';

    /**
     * @ORM\Column(type="integer", nullable=false)
     * @var int
     */
    protected int $lastNumber = 0;

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getPostfix(): string
    {
        return $this->postfix;
    }

    public function getLastNumber(): int
    {
        return $this->lastNumber;
    }
}
