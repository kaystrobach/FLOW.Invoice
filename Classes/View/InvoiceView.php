<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\View;

use KayStrobach\Invoice\Service\CreateCompleteElectronicInvoiceService;
use KayStrobach\Invoice\Service\CreateInvoicePdfService;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Annotations as Flow;

class InvoiceView implements ViewInterface
{
    protected array $options = [];

    /**
     * View variables and their values
     * @var array
     * @see assign()
     */
    protected $variables = [];

    /**
     * @var ControllerContext
     */
    protected $controllerContext;

    /**
     * @Flow\Inject
     * @var CreateCompleteElectronicInvoiceService
     */
    protected CreateCompleteElectronicInvoiceService $completeElectronicInvoiceService;

    public function __construct(array $options)
    {
        $this->options = $options;
    }

    public function setControllerContext(ControllerContext $controllerContext)
    {
        $this->controllerContext = $controllerContext;
    }

    public function assign(mixed $key, mixed $value)
    {
        $this->variables[$key] = $value;
    }

    public function assignMultiple(array $values)
    {
        $this->variables = array_replace_recursive($this->variables, $values);
    }

    public function canRender(ControllerContext $controllerContext): bool
    {
        return true;
    }

    public function render()
    {
        $this->controllerContext->getResponse()->setContentType('application/pdf');
        return $this->completeElectronicInvoiceService->render(
            $this->variables['object'],
            false,
            'Entwurf - ' . (new \DateTime('now'))->format('d.m.Y')
        );
    }

    public static function createWithOptions(array $options)
    {
        return new self($options);
    }
}
