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
use Psr\Log\LoggerInterface;


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

    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $logger;

    public function setInvoiceDefaultsFromEnv(Invoice $invoice, ?string $type = null)
    {
        if ($type === null) {
            $type = $invoice->getType();
        }

        $namespace = self::REGISTRY_NAMESPACE_PREFIX . $type;

        $invoice->setType($type);
        $this->setNumberDefaults($invoice, $namespace);

        $this->setDefaultTexts($invoice, $namespace);
        $this->setSellerDefaultsFromEnv($invoice, $namespace);
        $this->setInvoiceNumber($invoice);
    }

    public function setTitle(Invoice $invoice) {
        $invoice->setTitle($this->getRegistryValue(self::REGISTRY_NAMESPACE_PREFIX . $invoice->getType(), 'title'));
    }

    public function setInvoiceNumber(Invoice $invoice)
    {
        if (!$invoice->isChangeable()) {
            return;
        }
        if ($invoice->getNumber()->getNumber() !== null) {
            return;
        }

        $logger = $this->logger;

        $this->entityManager->wrapInTransaction(
            static function (EntityManager $em) use ($invoice, $logger) {
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
                    $invoice->getNumber()->updateCombinedNumber(true);
                    $logger->info('Invoice number set to ' . $invoice->getNumber()->getCombinedNumber());
                    // throw new \Exception((string)$invoice->getNumber()->getNumber());
                } catch (NoResultException $exception) {
                    $invoice->getNumber()->setNumber(1);
                    $invoice->getNumber()->updateCombinedNumber(true);
                    $logger->info('Invoice number set to ' . $invoice->getNumber()->getCombinedNumber());
                }
                $invoice->setChangeable(false);
                $em->persist($invoice);
                $em->flush($invoice);
            }
        );
    }

    public function setNumberDefaults(Invoice $invoice, ?string $namespace = null)
    {
        if ($namespace === null) {
            $namespace = self::REGISTRY_NAMESPACE_PREFIX . $invoice->getType();
        }

        $prefix = $this->getRegistryValue($namespace, 'numberPrefix') ?? 'R-%year';
        $invoice->getNumber()->setPrefix($prefix);

        $postfix = $this->getRegistryValue($namespace, 'numberPostfix') ?? '';
        $invoice->getNumber()->setPostfix($postfix);
        $invoice->getNumber()->updateCombinedNumber(true);
    }

    protected function setDefaultTexts(Invoice $invoice, string $namespace)
    {
        $props = [
            'title',
            'subTitle',
            'additionalInformation',
            'paymentTermText',
            'additionalText',
            'preText',
            'postText'
        ];
        foreach ($props as $prop) {
            if (ObjectAccess::getPropertyPath($invoice, $prop) === '') {
                $this->setProperty($invoice, $prop, $this->getRegistryValue($namespace, $prop));
            }
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
            if (ObjectAccess::getPropertyPath($invoice, $prop) === '') {
                $this->setProperty($invoice, $prop, $this->getRegistryValue($namespace, $prop));
            }
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
