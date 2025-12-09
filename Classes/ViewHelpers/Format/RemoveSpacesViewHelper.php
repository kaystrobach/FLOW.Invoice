<?php

namespace KayStrobach\Invoice\ViewHelpers\Format;

class RemoveSpacesViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    public function render()
    {
        return str_replace(' ', '', $this->renderChildren());
    }
}

