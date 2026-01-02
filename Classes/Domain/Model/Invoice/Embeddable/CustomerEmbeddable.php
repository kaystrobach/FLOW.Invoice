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
    protected string $deptorNumber = '';

    /**
     * @ORM\Column(type="string", length=1000)
     * @var string
     */
    protected string $additionalEmail = '';

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    protected string $shortName = '';

    public function getDeptorNumber(): string
    {
        return $this->deptorNumber;
    }

    public function setDeptorNumber(string $deptorNumber): void
    {
        $this->deptorNumber = $deptorNumber;
    }

    public function getAdditionalEmail(): string
    {
        return $this->additionalEmail;
    }

    public function setAdditionalEmail(string $additionalEmail): void
    {
        $this->additionalEmail = $additionalEmail;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }
}
