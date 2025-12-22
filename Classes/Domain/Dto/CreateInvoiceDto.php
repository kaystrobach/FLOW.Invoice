<?php

namespace KayStrobach\Invoice\Domain\Dto;

use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use KayStrobach\Invoice\Domain\Factory\InvoiceFactory;
use Neos\Flow\Annotations as Flow;

class CreateInvoiceDto
{
    /**
     * @Flow\Inject
     * @var RegistryEntryRepository
     */
    protected RegistryEntryRepository $registryEntryRepository;

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    public string $type = '';

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected string $offerNumber = '';

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected string $orderNumber = '';

    /**
     * @Flow\Validate(type="NotEmpty")
     * @var string
     */
    protected string $customerNumber = '';

    public function getPossibleTypes(): array
    {
        $entryList = explode(',', $this->registryEntryRepository->getValue('KayStrobach_Invoice_General', 'invoiceSeries'));
        $entries = [];
        foreach($entryList as $entry) {
            $namespace = InvoiceFactory::REGISTRY_NAMESPACE_PREFIX . $entry;
            $entries[] = [
                'identifier' => $entry,
                'description' => $this->getValue($namespace, 'description'),
                'name' => $this->getValue($namespace, 'title'),
                'prefix' => $this->getValue($namespace, 'numberPrefix'),
                'postfix' => $this->getValue($namespace, 'numberPostfix'),
                'namespace' => $namespace,
            ];
        }

        return $entries;
    }

    protected function getValue(string $namespace, string $prop): mixed
    {
        $v = $this->registryEntryRepository->getValue($namespace, $prop);
        if ($v !== null) {
            return $v;
        }
        return $this->registryEntryRepository->getValue(InvoiceFactory::REGISTRY_NAMESPACE_DEFAULT, $prop);
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

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function setOrderNumber(string $orderNumber): void
    {
        $this->orderNumber = $orderNumber;
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
        $t->setOrderNumber($array['orderNumber'] ?? '');
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
