<?php
/**
 * Created by kay.
 */

namespace KayStrobach\Invoice\ViewHelpers\Format;

class WrapEachCharViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{
    protected $escapeOutput = false;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('class', 'string', 'The css class to apply', false, 'char');
    }

    public function render()
    {
        $string = str_split($this->renderChildren());
        $output = '';
        foreach ($string as $s) {
            $output .= $this->wrap($s, $this->arguments['class']);
        }
        return $output;
    }

    protected function wrap(string $string, string $class = '')
    {
        return '<span class="' . htmlspecialchars($class, ENT_QUOTES | ENT_HTML5) . '">' . htmlspecialchars($string, ENT_QUOTES | ENT_HTML5) . '</span>';
    }
}
