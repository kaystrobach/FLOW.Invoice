<?php

declare(strict_types=1);

namespace KayStrobach\Invoice\View;

use KayStrobach\Invoice\Service\CreateZugpferdInvoiceService;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\View\ViewInterface;
use Neos\Flow\Annotations as Flow;

class InvoiceZugpferdView implements ViewInterface
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
     * @var CreateZugpferdInvoiceService
     */
    protected CreateZugpferdInvoiceService $createZugpferdPdfService;

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

    public static function createWithOptions(array $options)
    {
        return new self($options);
    }

    public function render()
    {
        $invoice = $this->variables['object'];
        if (!$invoice instanceof \KayStrobach\Invoice\Domain\Model\Invoice) {
            throw new \InvalidArgumentException('object needs to be an instance of Invoice');
        }
        $this->controllerContext->getResponse()->setContentType('application/xml');
        $this->controllerContext->getResponse()->setHttpHeader('Content-Disposition', sprintf('attachment; filename="%s%s.xml"', 'Invoice', $invoice->getNumber()));
        return $this->createZugpferdPdfService->render($invoice);
    }
}
