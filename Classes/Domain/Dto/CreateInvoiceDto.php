<?php

namespace KayStrobach\Invoice\Domain\Dto;

use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use Neos\Flow\Annotations as Flow;

class CreateInvoiceDto
{
    /**
     * @Flow\Inject
     * @var RegistryEntryRepository
     */
    protected RegistryEntryRepository $registryEntryRepository;

    /**
     * @var string
     */
    public string $type = '';

    /**
     * @var string
     */
    protected string $offerNumber = '';

    /**
     * @var string
     */
    protected string $customerNumber = '';

    public function getPossibleTypes(): array
    {
        $registryValue = $this->registryEntryRepository->getValue('KayStrobach.Invoice.General', 'invoice.series');
        return json_decode($registryValue, true, 512, JSON_THROW_ON_ERROR || JSON_OBJECT_AS_ARRAY) ?? [];
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getOfferNumber(): string
    {
        return $this->offerNumber;
    }

    public function setOfferNumber(string $offerNumber): void
    {
        $this->offerNumber = $offerNumber;
    }

    public function getCustomerNumber(): string
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber(string $customerNumber): void
    {
        $this->customerNumber = $customerNumber;
    }

    public static function fromArray(array $array): self
    {
        $t = new self();
        $t->setType($array['type'] ?? '');
        $t->setOfferNumber($array['offerNumber'] ?? '');
        $t->setCustomerNumber($array['customerNumber'] ?? '');
        return $t;
    }

    public function getOption(string $optionName): mixed
    {
        foreach ($this->getPossibleTypes() as $type)
        {
            if (!is_array($type) || !isset($type['identifier'])) {
                continue;
            }

            if (isset($type[$optionName])) {

                return $type[$optionName];
            }
        }
        return null;
    }
}
