<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class can check any configuration, e.g., TypoScript, Flexforms or extension manager.
 *
 * To use this class, override the 'checkAllConfigurationValues` method to call the available `check*` methods.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
abstract class AbstractConfigurationCheck
{
    /**
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * @var string[]
     */
    protected $warnings = [];

    /**
     * @var string
     */
    private $namespace;

    public function __construct(ConfigurationInterface $configurationToCheck, string $typoScriptNamespace)
    {
        $this->configuration = $configurationToCheck;
        $this->namespace = $typoScriptNamespace;
    }

    protected function getSuffixedNamespace(): string
    {
        return $this->namespace . '.';
    }

    /**
     * Checks the configuration.
     *
     * Any warnings created by the check will be available via `getWarningsAsHtml` and `hasWarnings`.
     *
     * Running this method twice resets the warnings from the first run so that warnings will not be added twice.
     *
     * @return void
     */
    public function check()
    {
        $this->resetWarnings();
        $this->checkAllConfigurationValues();
    }

    /**
     * @return void
     */
    private function resetWarnings()
    {
        $this->warnings = [];
    }

    /**
     * Checks all configuration values.
     *
     * This method does not reset any existing configuration check warnings.
     *
     * @return void
     */
    abstract protected function checkAllConfigurationValues();

    public function hasWarnings(): bool
    {
        return \count($this->warnings) > 0;
    }

    /**
     * @return string[]
     */
    public function getWarningsAsHtml(): array
    {
        return $this->warnings;
    }

    /**
     * Adds a warning and renders it as a Twitter Bootstrap warning.
     *
     * @param string $rawWarningText the warning text, may contain HTML, will not be encoded
     *
     * @return void
     */
    protected function addWarning(string $rawWarningText)
    {
        $this->warnings[] = '<div lang="en" class="alert alert-dark" role="alert">' . $rawWarningText . '</div>';
    }

    /**
     * Sets the error message, consisting of $explanation and a request to change the TypoScript setup
     * variable $fieldName (with the current TS setup path prepended).
     *
     * @return void
     */
    protected function addWarningAndRequestCorrection(string $fieldName, string $explanation)
    {
        $message = $explanation . ' Please fix the TypoScript setup variable <strong>' .
            $this->getSuffixedNamespace() . $fieldName . '</strong> in your TypoScript  template setup.';
        $this->addWarning($message);
    }

    /**
     * Checks whether the static template has been included.
     *
     * @return void
     */
    protected function checkStaticIncluded()
    {
        if (!$this->configuration->getAsBoolean('isStaticTemplateLoaded')) {
            $this->addWarning(
                'The static template is not included.
                 This has the effect that important default values do not get set.
                 To fix this, please include the static template of this extension under
                 <em>Include static (from extensions)</em> in your TypoScript template.'
            );
        }
    }

    /**
     * @return TypoScriptFrontendController|null
     */
    protected function getFrontEndController()
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
