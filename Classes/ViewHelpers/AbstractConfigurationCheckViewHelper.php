<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\ViewHelpers;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\ExtbaseConfiguration;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * This ViewHelper provides a configuration check for an Extbase-based extension.
 *
 * Usage:
 * 1. Create a corresponding configuration check class.
 * 2. Extend this view helper in your extension.
 * 4. Override the method getExtensionKey() to return your extension key (without the `tx_` prefix).
 * 3. Override the method getConfigurationCheckClassName() to return the name of your configuration check class.
 * 4. If your TypoScript configuration namespace is different from `plugin.tx_<your extension key>.settings`
 *    override the method getConfigurationNamespace() to return your namespace.
 *
 * @template C of AbstractConfigurationCheck
 */
abstract class AbstractConfigurationCheckViewHelper extends AbstractViewHelper
{
    protected $escapeOutput = false;

    /**
     * @return non-empty-string
     */
    abstract protected static function getExtensionKey(): string;

    /**
     * @return class-string<C>
     */
    abstract protected static function getConfigurationCheckClassName(): string;

    /**
     * @return non-empty-string
     */
    protected static function getConfigurationNamespace(): string
    {
        return 'plugin.tx_' . static::getExtensionKey() . '.settings';
    }

    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {
        if (!AbstractConfigurationCheck::shouldCheck(static::getExtensionKey())) {
            return '';
        }

        $settings = $renderingContext->getVariableProvider()->get('settings');
        if (!\is_array($settings)) {
            throw new \UnexpectedValueException('No settings in the variable container found.', 1651153736);
        }
        $configuration = new ExtbaseConfiguration($settings);
        $configurationCheckClassName = static::getConfigurationCheckClassName();
        $configurationCheck = new $configurationCheckClassName($configuration, static::getConfigurationNamespace());
        $configurationCheck->check();

        return \implode("\n", $configurationCheck->getWarningsAsHtml());
    }
}
