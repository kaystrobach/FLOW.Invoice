<?php

namespace KayStrobach\Invoice\Service;

use fucodo\registry\Domain\Model\RegistryEntry;
use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use KayStrobach\Invoice\Domain\Model\Invoice\Embeddable\SellerEmbeddable;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\ObjectAccess;

class SellerEmbeddableInitService
{
    protected $properties = [
        'name',
        'personName',
        'street',
        'houseNumber',
        'addressAddon',
        'roomNumber',
        'zipCode',
        'city',
        'country',
        'countryCode',
        'vatID',
        'email',
        'combinedAddress'
    ];

    /**
     * @Flow\Inject
     * @var RegistryEntryRepository
     */
    protected RegistryEntryRepository $registryEntryRepository;

    public function init(SellerEmbeddable $sellerEmbeddable)
    {
        foreach ($this->properties as $property) {
            $key = 'seller-' . $property;
            $value = $this->registryEntryRepository->getValue('KayStrobach.Invoice', $key);

            if ($value !== null) {
                ObjectAccess::setProperty($sellerEmbeddable, $property, $value);
            }
        }
    }
}
