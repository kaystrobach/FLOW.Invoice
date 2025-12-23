<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * @ORM\Embeddable()
 */
class PeriodEmbeddable
{
    /**
     * @Flow\Validate(type="NotEmpty", validationGroups={"finalizeInvoice"})
     * @ORM\Column(nullable=true)
     * @var DateTimeImmutable
     */
    protected ?DateTimeImmutable $start = null;

    /**
     * @Flow\Validate(type="NotEmpty", validationGroups={"finalizeInvoice"})
     * @ORM\Column(nullable=true)
     * @var ?DateTimeImmutable DateTimeImmutable = null
     */
    protected ?DateTimeImmutable $end = null;

    /**
     * @var string
     * @Flow\Validate(type="text")
     * @ORM\Column(type="text", length=65532, nullable=true)
     */
    protected string $comment = '';

    public function getStart(): ?DateTimeImmutable
    {
        return $this->start;
    }

    public function setStart(?DateTimeImmutable $start): void
    {
        if ($start !== null) {
            $start = $start->setTime(0,0);
        }
        $this->start = $start;
    }

    public function getEnd(): ?DateTimeImmutable
    {
        return $this->end;
    }

    public function setEnd(?DateTimeImmutable $end): void
    {
        if ($end !== null) {
            $end = $end->setTime(23,50);
        }
        $this->end = $end;
    }

    public function getComment(): string
    {
        return $this->comment;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }
}
