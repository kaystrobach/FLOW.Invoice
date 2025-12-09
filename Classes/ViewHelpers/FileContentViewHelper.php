<?php

namespace KayStrobach\Invoice\ViewHelpers;


class FileContentViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('file', 'string', 'Value to match in this case', true);
    }

    public function render()
    {
        return file_get_contents($this->arguments['file']);
    }
}
