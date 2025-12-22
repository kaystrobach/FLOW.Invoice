<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NoResultException;
use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use KayStrobach\Invoice\Domain\Model\Invoice;
use Neos\Flow\Annotations as Flow;
use Neos\Utility\ObjectAccess;


class InvoiceFactory
{
    const REGISTRY_NAMESPACE_DEFAULT = 'KayStrobach_Invoice_InvoiceSettings_Default';

    const REGISTRY_NAMESPACE_PREFIX = 'KayStrobach_Invoice_InvoiceSettings_';

    /**
     * @Flow\Inject
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @Flow\Inject
     * @var RegistryEntryRepository
     */
    protected RegistryEntryRepository $registryEntryRepository;

    public function setInvoiceDefaultsFromEnv(Invoice $invoice, string $type = 'Default')
    {
        $namespace = self::REGISTRY_NAMESPACE_PREFIX . $type;

        $this->setNumberDefaults($invoice, $namespace);
        $this->setDefaultTexts($invoice, $namespace);
        $this->setSellerDefaultsFromEnv($invoice, $namespace);
        $this->setInvoiceNumber($invoice);
    }

    public function setInvoiceNumber(Invoice $invoice)
    {
        if ($invoice->isChangeable()) {
            return;
        }
        if($invoice->getNumber()->getNumber() !== null) {
            return;
        }
        $this->entityManager->wrapInTransaction(
            static function (EntityManager $em) use ($invoice) {
                try {
                    $query = $em->createQueryBuilder()
                        ->select('MAX(e.number.number)')
                        ->from(Invoice::class, 'e')
                        ->where('e.number.prefix = ?1 AND e.number.postfix = ?2')
                        ->setParameter(1, $invoice->getNumber()->getPrefix())
                        ->setParameter(2, $invoice->getNumber()->getPostfix())
                        ->getQuery();
                    $maxId = $query
                        ->getSingleScalarResult();
                    $invoice->getNumber()->setNumber(1 + (int)$maxId);
                    // throw new \Exception((string)$invoice->getNumber()->getNumber());
                } catch (NoResultException $exception) {
                    $invoice->getNumber()->setNumber(1);
                }
                $em->persist($invoice);
                $em->flush($invoice);
            }
        );
    }

    protected function setNumberDefaults(Invoice $invoice, string $namespace)
    {
        $prefix = $this->getRegistryValue($namespace, 'numberPrefix') ?? 'R-%year';
        $invoice->getNumber()->setPrefix($prefix);

        $postfix = $this->getRegistryValue($namespace, 'numberPostfix') ?? '';
        $invoice->getNumber()->setPostfix($postfix);
    }

    protected function setDefaultTexts(Invoice $invoice, string $namespace)
    {
        $props = [
            'title',
            'subTitle',
            'additionalInformation',
        ];
        foreach ($props as $prop) {
            $this->setProperty($invoice, $prop, $this->getRegistryValue($namespace, $prop));
        }
    }

    protected function setSellerDefaultsFromEnv(Invoice $invoice, string $namespace)
    {
        $props = [
            'seller.name',
            'seller.personName',
            'seller.street',
            'seller.houseNumber',
            'seller.addressAddon',
            'seller.roomNumber',
            'seller.zipCode',
            'seller.city',
            'seller.country',
            'seller.countryCode',
            'seller.vatID',
            'seller.email',
            'seller.receiverBank',
            'seller.receiverBic',
            'seller.receiverName',
            'seller.receiverIban',
        ];

        foreach ($props as $prop) {
            $this->setProperty($invoice, $prop, $this->getRegistryValue($namespace, $prop));
        }
    }

    protected function getRegistryValue(string $namespace, $prop): mixed
    {
        $v = $this->registryEntryRepository->getValue($namespace, $prop);
        if ($v !== null) {
            return $v;
        }
        return $this->registryEntryRepository->getValue(self::REGISTRY_NAMESPACE_DEFAULT, $prop);
    }

    protected function setProperty(Invoice $invoice, string $property, $value)
    {
        if (!str_contains($property, '.')) {
            ObjectAccess::setProperty($invoice, $property, $value);
            return;
        }
        $lastDot = strrpos($property, '.');
        $objectPath = substr($property, 0, $lastDot);
        $propName = substr($property,  $lastDot+ 1);
        $object = ObjectAccess::getPropertyPath($invoice, $objectPath);
        ObjectAccess::setProperty($object, $propName, $value);
    }

    public function triggerThirdPartyProcessesOnUpdate(Invoice $invoice): void
    {
        $this->emitInvoiceCreated($invoice);
    }

    /**
     * @param Invoice $invoice
     * @return void
     * @Flow\Signal
     */
    protected function emitInvoiceCreated(Invoice $invoice): void {}
}
