<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\ViewHelpers;

use TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception as FluidException;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

/**
 * Formats an object implementing \DateTimeInterface using the HTML5 time element and microdata, already marked up
 * for use by the "timeago" jQuery plugin.
 *
 * = Examples =
 *
 * <code title="Defaults">
 * <community:format.dynamicDate>{dateObject}</community:format.date>
 * </code>
 * <output>
 * <time datetime="1975-04-02T08:53" class="js-time-ago">02.04.1975 08:53</time>';
 * (depending on the provided date)
 * </output>
 *
 * <code title="Custom date format">
 * <community:format.dynamicDate format="Y-m-i">{dateObject}</community:format.date>
 * </code>
 * <output>
 * <time datetime="1975-04-02T08:53" class="js-time-ago">1975-04-02</time>';
 * (depending on the provided date)
 * </output>
 * <code title="Inline notation">
 * {community:format.date(date: dateObject)}
 * </code>
 * <output>
 * <code>
 * <time datetime="1975-04-02T08:53" class="js-time-ago">02.04.1975 08:53</time>';
 * </code>
 * (depending on the value of {dateObject})
 * </output>
 *
 * <code title="Inline notation (2nd variant)">
 * {dateObject -> community:format.dynamicDate()}
 * </code>
 * <output>
 * <code>
 * <time datetime="1975-04-02T08:53" class="js-time-ago">02.04.1975 08:53</time>';
 * </code>
 * (depending on the value of {dateObject})
 * </output>
 *
 * @see https://github.com/rmm5t/jquery-timeago
 */
class DynamicDateViewHelper extends AbstractViewHelper implements CompilableInterface
{
    /**
     * @var string
     */
    private const DEFAULT_DATE_FORMAT = 'd.m.Y H:i';

    /**
     * Renders the DateTime object (which is the child) as a formatted date.
     *
     * @param string $displayFormat format string which is taken to format the visible Date/Time
     *
     * @return string formatted date
     *
     * @throws FluidException
     */
    public function render(string $displayFormat = self::DEFAULT_DATE_FORMAT): string
    {
        return static::renderStatic(
            ['format' => $displayFormat],
            $this->buildRenderChildrenClosure(),
            $this->renderingContext
        );
    }

    /**
     * Renders the DateTime object (which is the child) as a formatted date.
     *
     * @param array<string, mixed> $arguments can include the "format" key (will default to a German date/time format)
     * @param \Closure $renderChildrenClosure
     * @param RenderingContextInterface $renderingContext
     *
     * @return string
     *
     * @throws FluidException
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        $date = $renderChildrenClosure();
        if (!$date instanceof \DateTimeInterface) {
            throw new FluidException('"' . $date . '" is not a DateTimeInterface instance.', 1459514034);
        }

        /** @var \DateTimeInterface $date */
        $format = $arguments['format'] ?: self::DEFAULT_DATE_FORMAT;
        $visibleDate = $date->format($format);
        $metadataDate = $date->format('Y-m-d\\TH:i');

        return '<time datetime="' . $metadataDate . '" class="js-time-ago">' . $visibleDate . '</time>';
    }
}
