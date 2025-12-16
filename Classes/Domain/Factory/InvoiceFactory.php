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
    const REGISTRY_NAMESPACE = 'KayStrobach.Invoice.NormalInvoiceSettings';

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

    public function setInvoiceDefaultsFromEnv(Invoice $invoice)
    {
        $this->setNumberDefaults($invoice);
        $this->setDefaultTexts($invoice);
        $this->setSellerDefaultsFromEnv($invoice);
        $this->setInvoiceNumber($invoice);
    }

    protected function setInvoiceNumber(Invoice $invoice)
    {
        if ($invoice->isChangeable()) {
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
                } catch (NoResultException $exception) {
                    $invoice->getNumber()->setNumber(1);
                }
                $em->persist($invoice);
                $em->flush($invoice);
            }
        );
    }

    protected function setNumberDefaults(Invoice $invoice)
    {
        if ($invoice->getNumber()->getPrefix() === '') {
            $invoice->getNumber()->setPrefix('R-' . date('Y') . '-');
        }
    }

    protected function setDefaultTexts(Invoice $invoice)
    {
        $props = [
            'title',
            'subTitle',
            'additionalInformation',
        ];
        foreach ($props as $prop) {
            $v = $this->registryEntryRepository->getValue(self::REGISTRY_NAMESPACE, $prop);
            $this->setProperty($invoice, $prop, $v);
        }
    }

    protected function setSellerDefaultsFromEnv(Invoice $invoice)
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
            'seller.email'
        ];

        foreach ($props as $prop) {
            $v = $this->registryEntryRepository->getValue(self::REGISTRY_NAMESPACE, $prop);
            $this->setProperty($invoice, $prop, $v);
        }
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
}
