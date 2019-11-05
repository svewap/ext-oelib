<?php
declare(strict_types = 1);

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This view helper converts strings to uppercase.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_ViewHelpers_UppercaseViewHelper extends AbstractViewHelper
{
    /**
     * Converts the rendered children to uppercase.
     *
     * @return string the uppercased rendered children, might be empty
     */
    public function render(): string
    {
        $renderedChildren = $this->renderChildren();
        $encoding = mb_detect_encoding($renderedChildren);

        return mb_strtoupper($renderedChildren, $encoding);
    }
}
