<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\ViewHelpers;

use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This view helper converts strings to uppercase.
 *
 * @deprecated will be remove in oelib 4.0 - use the `format.case` Fluid view helper instead
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class UppercaseViewHelper extends AbstractViewHelper
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
