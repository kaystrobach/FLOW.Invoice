<?php

namespace KayStrobach\Invoice\ViewHelpers\Format;

class Base64EncodeViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function render()
    {
        return base64_encode($this->renderChildren());
    }
}
