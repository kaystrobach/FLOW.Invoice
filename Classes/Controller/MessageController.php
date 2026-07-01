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

    public function sendMessageAction(MessageDto $dto)
    {
        $this->sendInvoiceService->emitInvoiceMessageShouldBeSendNow($dto);

        $this->addFlashMessage('Nachricht wurde zum versenden vorgemerkt');

        $this->redirect(
            'edit',
            'Standard',
            null,
            [
                'object' => $dto->getInvoice(),
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
