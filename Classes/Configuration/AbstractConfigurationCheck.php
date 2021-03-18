<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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

    /**
     * @param ConfigurationInterface $configurationToCheck the configuration to check
     * @param string $typoScriptNamespace the TypoScript namespace of the configuration, e.g., `plugin.tx_oelib"
     */
    public function __construct(ConfigurationInterface $configurationToCheck, string $typoScriptNamespace)
    {
        $this->configuration = $configurationToCheck;
        $this->namespace = $typoScriptNamespace;
    }

    protected function getSuffixedNamespace(): string
    {
        return $this->namespace . '.';
    }

    protected function buildConfigurationPath(string $localPath): string
    {
        return $this->getSuffixedNamespace() . $localPath;
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
     * variable $key (with the current TypoScript setup path prepended).
     *
     * @return void
     */
    protected function addWarningAndRequestCorrection(string $key, string $explanation)
    {
        $message = $explanation . ' Please fix the TypoScript setup variable <strong>' .
            $this->buildConfigurationPath($key) . '</strong> in your TypoScript template setup.';

        $this->addWarning($message);
    }

    /**
     * Checks whether the static template has been included.
     */
    protected function checkStaticIncluded(): bool
    {
        if ($this->configuration->getAsBoolean('isStaticTemplateLoaded')) {
            return true;
        }

        $this->addWarning(
            'The static template is not included.
                 This has the effect that important default values do not get set.
                 To fix this, please include the static template of this extension under
                 <em>Include static (from extensions)</em> in your TypoScript template.'
        );

        return false;
    }

    /**
     * Checks that the HTML template is provided and that the file exists.
     */
    protected function checkTemplateFile(): bool
    {
        $description = 'This value specifies the HTML template which is essential for creating
            any output from this extension.';

        return $this->checkFileExists('templateFile', $description);
    }

    /**
     * Checks that the value is non-empty and that the referenced file exists.
     */
    protected function checkFileExists(string $key, string $description): bool
    {
        if (!$this->checkForNonEmptyString($key, $description)) {
            return false;
        }

        $rawFileName = $this->configuration->getAsString($key);
        $file = GeneralUtility::getFileAbsFileName($rawFileName);
        $isOkay = $file !== '' && \is_file($file);

        if (!$isOkay) {
            $encodedFileName = \htmlspecialchars($rawFileName, ENT_QUOTES | ENT_HTML5);
            $message = 'The specified file <strong>' . $encodedFileName . '</strong> cannot be read. ' .
                $description . ' Please either create the file <strong>' . $encodedFileName .
                '</strong> or select an existing file using the TypoScript setup variable <strong>' .
                $this->buildConfigurationPath($key) . '</strong>.';
            $this->addWarning($message);
        }

        return $isOkay;
    }

    /**
     * Checks whether a configuration value contains a non-empty-string.
     */
    protected function checkForNonEmptyString(string $key, string $explanation): bool
    {
        $value = $this->configuration->getAsString($key);
        if ($value !== '') {
            return true;
        }

        $message = 'The TypoScript setup variable <strong>' . $this->buildConfigurationPath($key) .
            '</strong> is empty, but needs to be non-empty. ' . $explanation;
        $this->addWarningAndRequestCorrection($key, $message);

        return false;
    }

    /**
     * Checks whether a configuration value is non-empty and lies within a set of allowed values.
     *
     * @param string[] $allowedValues allowed values (must not be empty)
     */
    protected function checkIfSingleInSetNotEmpty(string $key, string $explanation, array $allowedValues): bool
    {
        return $this->checkForNonEmptyString($key, $explanation)
            && $this->checkIfSingleInSetOrEmpty($key, $explanation, $allowedValues);
    }

    /**
     * Checks whether a configuration value either is empty or lies within a set of allowed values.
     *
     * @param string[] $allowedValues allowed values (must not be empty)
     */
    protected function checkIfSingleInSetOrEmpty(string $key, string $explanation, array $allowedValues): bool
    {
        $value = $this->configuration->getAsString($key);
        if ($value === '') {
            return true;
        }

        $okay = \in_array($value, $allowedValues, true);
        if (!$okay) {
            $overviewOfValues = '(' . \implode(', ', $allowedValues) . ')';
            $encodedValue = \htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
            $message = 'The TypoScript setup variable <strong>' . $this->buildConfigurationPath($key) .
                '</strong> is set to the value <strong>' . $encodedValue .
                '</strong>, but only the  following values are allowed: <br/><strong>' .
                $overviewOfValues . '</strong><br />' . $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }

    /**
     * Checks whether a configuration value has a non-empty boolean value.
     */
    protected function checkIfBoolean(string $key, string $explanation): bool
    {
        return $this->checkIfSingleInSetNotEmpty($key, $explanation, ['0', '1']);
    }

    /**
     * Checks whether a configuration value has a non-negative integer value (or is empty).
     */
    protected function checkIfInteger(string $key, string $explanation): bool
    {
        $value = $this->configuration->getAsString($key);
        if ($value === '') {
            return true;
        }

        $okay = \ctype_digit($value);
        if (!$okay) {
            $encodedValue = \htmlspecialchars($value, ENT_QUOTES | ENT_HTML5);
            $message = 'The TypoScript setup variable <strong>' . $this->buildConfigurationPath($key) .
                '</strong> is set to the value <strong>' . $encodedValue .
                '</strong>, but only non-negative integers are allowed. ' . $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }

    /**
     * Checks whether a configuration value has an integer value in the specified, inclusive range.
     *
     * @throws \InvalidArgumentException
     */
    protected function checkIfIntegerInRange(string $key, int $minimum, int $maximum, string $explanation): bool
    {
        if ($minimum > $maximum) {
            throw new \InvalidArgumentException('$minimum must be <= $maximum.', 1616069185);
        }
        if (!$this->checkIfInteger($key, $explanation)) {
            return false;
        }

        $value = $this->configuration->getAsInteger($key);

        $okay = $value >= $minimum && $value <= $maximum;
        if ($value < $minimum || $value > $maximum) {
            $encodedValue = \htmlspecialchars((string)$value, ENT_QUOTES | ENT_HTML5);
            $message = 'The TypoScript setup variable <strong>' . $this->buildConfigurationPath($key) . $key .
                '</strong> is set to the value <strong>' . $encodedValue . '</strong>, but only integers from ' .
                $minimum . ' to ' . $maximum . ' (including these values) are allowed. ' . $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }
}
