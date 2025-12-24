<?php

namespace KayStrobach\Invoice\Service;

use DateTime;
use fucodo\registry\Domain\Repository\RegistryEntryRepository;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdProfiles;
use KayStrobach\Invoice\Domain\Model\Invoice\TaxRecord;
use KayStrobach\Invoice\Domain\Model\InvoiceItem;
use Neos\Flow\Annotations as Flow;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Pdf\View\PdfTemplateView;
use Neos\Flow\ResourceManagement\ResourceManager;

class CreateZugpferdInvoiceService
{
    public const ZUGPFERD_CODE_INVOICE = '380';  // Code '380' für 'invoice'

    public function render(Invoice $invoice)
    {
        $document = ZugferdDocumentBuilder::CreateNew(ZugferdProfiles::PROFILE_EN16931);

        // Add invoice and position information
        $document
            ->setDocumentInformation(
                $invoice->getNumber()->getCombinedNumber(),
                self::ZUGPFERD_CODE_INVOICE,
                $invoice->getDate(),
                $invoice->getTotal()->getCurrency()
            )
            ->setDocumentBusinessProcess('urn:fdc:peppol.eu:2017:poacc:billing:01:1.0')
            ->setDocumentSupplyChainEvent($invoice->getDate())
            ->setDocumentSeller($invoice->getSeller()->getName())
            ->addDocumentSellerTaxRegistration('VA', $invoice->getSeller()->getVatID())
            ->setDocumentSellerAddress(
                $invoice->getSeller()->getStreet(), '', '',
                $invoice->getSeller()->getZipCode(),
                $invoice->getSeller()->getCity(),
                $invoice->getSeller()->getCountryCode()
            )
            ->setDocumentSellerCommunication('EM', $invoice->getSeller()->getEmail())
            ->setDocumentSellerContact(
                $invoice->getSeller()->getName(),
                null,
                null, //$invoice->getSeller()->getPhone(),
                null,
                $invoice->getSeller()->getEmail()
            )
            ->setDocumentBuyer($invoice->getCustomer()->getName())
            ->setDocumentBuyerAddress(
                $invoice->getCustomer()->getStreet(), '', '',
                $invoice->getCustomer()->getZipCode(),
                $invoice->getCustomer()->getCity(),
                $invoice->getCustomer()->getCountryCode()
            )
            ->setDocumentBuyerCommunication('EM', $invoice->getCustomer()->getEmail());

        foreach ($invoice->getTaxRecords() as $taxRecord) {
            if (!$taxRecord instanceof TaxRecord) {
                continue;
            }
            $document
                ->addDocumentTax(
                    'S',
                    'VAT',
                    $taxRecord->getSumNetBase()->getValue() / 100,
                    $taxRecord->getSum()->getValue() / 100 ,
                    $taxRecord->getTaxRate()
                ); // Wenn wir mehrere Steuersätze haben, brauchen wir einen Eintrag pro Steuersatz
        }

        $document
            ->setDocumentSummation(
                $invoice->getTotal()->getValue() / 100, // Gesamtbetrag (Gesamtbetrag der Position + Gebühren - Freibeträge + Steuern)
                $invoice->getTotal()->getValue() / 100, // Final amount due for payment
                $invoice->getTotalNoTaxes()->getValue() / 100, // Total amount for all line items before charges, allowances, and taxes
                0.0, // Summe aller zusätzlichen Kosten (z.B. Versand)
                0.0, // Summe der Rabatte oder Nachlässe
                $invoice->getTotalNoTaxes()->getValue() / 100, // Steuerpflichtiger Gesamtbetrag (Basisbetrag für die Steuerberechnung)
                ($invoice->getTotal()->getValue() - $invoice->getTotalNoTaxes()->getValue()) / 100, // Gesamtsteuerbetrag
                null,
                0.0
            )
            //->addDocumentPaymentTerm("Zahlbar innerhalb 30 Tagen bis 05.12.2024") // Wenn ein Betrag ausstehend ist, müssen wir das hier setzen oder ...
            ->addDocumentPaymentTerm(null, new \DateTime(' + 30 days')) // ...das Fälligkeitsdatum der Zahlung
        ;

        if ($invoice->getAdditionalInformation() !== '') {
            $document->addDocumentNote($invoice->getAdditionalInformation(), null, 'REG');
        }
        if ($invoice->getAdditionalText() !== '') {
            $document->addDocumentNote($invoice->getAdditionalText(), null, 'REG');
        }

        if ($invoice->getPreText() !== '') {
            $document->addDocumentNote($invoice->getPreText(), null, 'REG');
        }
        if ($invoice->getPostText() !== '') {
            $document->addDocumentNote($invoice->getPostText(), null, 'REG');
        }

        if ($invoice->getPaymentTermText() !== '') {
            $document->addDocumentPaymentTerm(
                $invoice->getPaymentTermText()
            );
        }

        foreach($invoice->getSettlementDates() as $settlementDate) {
            $document->addDocumentPaymentTerm(
                '',
                $settlementDate->getDueDate(),
                null, // hier kann später das SEPA Mandat eingefügt werden!!!,
                round($settlementDate->getAmount()->getValue() / 100, 2)
            );
        }


        // Add items
        $i = 0;
        foreach ($invoice->getInvoiceItems() as $item) {
            if (!$item instanceof InvoiceItem) {
                continue;
            }
            $document
                ->addNewPosition($item->getSort())
                ->setDocumentPositionProductDetails($item->getName(), $item->getDescription(), $item->getArticleReference())
                ->setDocumentPositionNetPrice($item->getSinglePrice()->getValue() / 100)
                ->setDocumentPositionQuantity($item->getAmount(), $item->getUnit())
                ->addDocumentPositionTax('S', 'VAT', $item->getTax()) //Code 'S' für 'Standard Rate'
                ->setDocumentPositionLineSummation(($item->getSinglePrice()->getValue() / 100) * $item->getAmount())
            ;
        }

        return $document->getContent();
    }
}
