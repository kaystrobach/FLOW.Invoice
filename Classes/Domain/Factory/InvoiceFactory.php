<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\Domain\Factory;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Service\InvoiceNumberRangeService;
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

    /**
     * @Flow\Inject
     * @var InvoiceNumberRangeService
     */
    protected $numberRangeService;

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
    }

    public function setTitle(Invoice $invoice) {
        $invoice->setTitle($this->getRegistryValue(self::REGISTRY_NAMESPACE_PREFIX . $invoice->getType(), 'title'));
    }

    public function setInvoiceNumber(Invoice $invoice)
    {
        $logger = $this->logger;
        if (!$invoice->isChangeable()) {
            $this->logger->debug('Invoice is not changeable');
            return;
        }
        if ($invoice->getNumber()->getNumber() !== null) {
            // e.g. unlocked with invoice:reset – keep the number, only lock the invoice again
            $this->logger->debug('Invoice already had a number, keeping it');
            $invoice->setChangeable(false);
            return;
        }

        $this->logger->debug('Starting to look for number');

        $numberRangeService = $this->numberRangeService;
        // outside the transaction on purpose, see InvoiceNumberRangeService::ensureSeries()
        $numberRangeService->ensureSeries(
            $this->entityManager->getConnection(),
            $invoice->getNumber()->getPrefix(),
            $invoice->getNumber()->getPostfix()
        );
        // number and finalization are committed together; the series stays locked until then
        $this->entityManager->wrapInTransaction(
            static function (EntityManager $em) use ($invoice, $logger, $numberRangeService) {
                $number = $numberRangeService->nextNumber(
                    $em->getConnection(),
                    $invoice->getNumber()->getPrefix(),
                    $invoice->getNumber()->getPostfix()
                );
                $invoice->getNumber()->setNumber($number);
                $invoice->getNumber()->updateCombinedNumber(true);
                $logger->info('Invoice number set to ' . $invoice->getNumber()->getCombinedNumber());
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
