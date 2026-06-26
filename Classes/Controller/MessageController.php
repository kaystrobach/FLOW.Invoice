<?php

namespace KayStrobach\Invoice\Controller;

use KayStrobach\Backend\Controller\AbstractPageRendererController;
use KayStrobach\Invoice\Domain\Dto\MessageDto;
use KayStrobach\Invoice\Domain\Model\Invoice;
use KayStrobach\Invoice\Service\SendInvoiceService;
use Neos\Flow\Annotations as Flow;

class MessageController extends AbstractPageRendererController
{
    /**
     * @Flow\Inject
     * @var SendInvoiceService
     */
    protected SendInvoiceService $sendInvoiceService;

    public function prepareMessageAction(Invoice $object)
    {
        $this->view->assign('object', $object);
        $dto = new MessageDto();
        $dto->setInvoice($object);
        $dto->setTo($object->getCustomer()->getEmail());
        $dto->setCc($object->getCustomer()->getAdditionalEmail());

        $this->sendInvoiceService->emitInvoiceMessagePrepare($dto);

        $this->view->assign('dto', $dto);
    }

    public function sendMessageAction(MessageDto $messageDto)
    {
        $this->sendInvoiceService->emitInvoiceShouldBeSendNow($messageDto->getInvoice());
        $this->redirect(
            'edit',
            null,
            null,
            [
                'object' => $messageDto->getInvoice(),
            ]
        );
    }

    public function sendDirectly(Invoice $object)
    {
        $this->sendInvoiceService->emitInvoiceShouldBeSendNow($object);
        $this->redirect(
            'show',
            null,
            null,
            [
                'object' => $object
            ]
        );
    }
}
