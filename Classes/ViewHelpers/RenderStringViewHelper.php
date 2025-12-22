<?php

namespace KayStrobach\Invoice\ViewHelpers;

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

class RenderStringViewHelper extends AbstractViewHelper
{
    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the result of renderChildren() calls within this ViewHelper
     * @see isChildrenEscapingEnabled()
     *
     * Note: If this is NULL the value of $this->escapingInterceptorEnabled is considered for backwards compatibility
     *
     * @var boolean
     * @api
     */
    protected $escapeChildren = false;

    /**
     * Specifies whether the escaping interceptors should be disabled or enabled for the render-result of this ViewHelper
     * @see isOutputEscapingEnabled()
     *
     * @var boolean
     * @api
     */
    protected $escapeOutput = false;


    /**
     * @throws \Neos\FluidAdaptor\Core\ViewHelper\Exception
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'value', false, null);
    }

    public function render()
    {
        try {
            $value = $this->arguments['value'];
            if ($value === null) {
                $value = $this->renderChildren();
            }

            $templateParser = $this->renderingContext->getTemplateParser();
            return $templateParser->parse($value)->render($this->renderingContext);
        } catch (\Exception $exception) {
            return $exception->getMessage()
                . '<strong>Exception Template: '
                . htmlspecialchars($this->arguments['value'], ENT_QUOTES | ENT_HTML5)
                . '</strong>';
        }
    }
}
