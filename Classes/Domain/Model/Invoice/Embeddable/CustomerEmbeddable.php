<?php

namespace KayStrobach\Invoice\Domain\Model\Invoice\Embeddable;

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable()
 *
 */
class CustomerEmbeddable extends AddressEmbeddable
{
    /**
     * @Flow\Validate(type="notEmpty", validationGroups={"deptor"})
     * @ORM\Column()
     * @var string
     */
    protected $deptorNumber = '';

    public function getDeptorNumber(): string
    {
        return $this->deptorNumber;
    }

    public function setDeptorNumber(string $deptorNumber): void
    {
        $this->deptorNumber = $deptorNumber;
    }
}
