<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class can check any configuration, e.g., TypoScript, Flexforms or extension manager.
 *
 * To use this class, override the 'checkAllConfigurationValues` method to call the available `check*` methods.
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
     * @param string $typoScriptNamespace the TypoScript namespace of the configuration, e.g., `plugin.tx_oelib`
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

    /**
     * Builds a fully-qualified TypoScript namespace from a suffix, e.g., `plugin.tx_oelib.foo` from `foo`.
     *
     * The output is HTML-safe.
     */
    protected function buildEncodedConfigurationPath(string $localPath): string
    {
        return $this->encode($this->getSuffixedNamespace() . $localPath);
    }

    /**
     * Syntactic sugar for HTML-encoding a string.
     */
    protected function encode(string $rawText): string
    {
        return \htmlspecialchars($rawText, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * Builds an HTML-safe, text-only overview of the given values.
     *
     * @param string[] $values
     */
    protected function buildValueOverview(array $values): string
    {
        return $this->encode('(' . \implode(', ', $values) . ')');
    }

    /**
     * Checks the configuration.
     *
     * Any warnings created by the check will be available via `getWarningsAsHtml` and `hasWarnings`.
     *
     * Running this method twice resets the warnings from the first run so that warnings will not be added twice.
     */
    public function check(): void
    {
        $this->resetWarnings();
        $this->checkAllConfigurationValues();
    }

    private function resetWarnings(): void
    {
        $this->warnings = [];
    }

    /**
     * Checks all configuration values.
     *
     * This method does not reset any existing configuration check warnings.
     */
    abstract protected function checkAllConfigurationValues(): void;

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
     */
    protected function addWarning(string $rawWarningText): void
    {
        $this->warnings[]
            = '<div lang="en" class="alert alert-warning mt-3" role="alert">' . $rawWarningText .
            '<i>The configuration check for this extension can be disabled in the extension manager.</i>' .
            '</div>';
    }

    /**
     * Sets the error message, consisting of `$explanation` and a request to change the TypoScript setup
     * variable `$key` (with the current TypoScript setup path prepended).
     *
     * @param string $explanation explanation, may contain HTML, will not be encoded
     */
    protected function addWarningAndRequestCorrection(string $key, string $explanation): void
    {
        $message = $explanation . ' Please fix the TypoScript setup variable <strong>' .
            $this->buildEncodedConfigurationPath($key) . '</strong> in your TypoScript template setup.';

        $this->addWarning($message);
    }

    /**
     * Builds the sentence start "The TypoScript setup variable $variable ", including the trailing space
     * and some HTML markup.
     */
    protected function buildWarningStartWithKey(string $key): string
    {
        return 'The TypoScript setup variable <strong>' . $this->buildEncodedConfigurationPath($key) . '</strong> ';
    }

    /**
     * Builds the sentence start "The TypoScript setup variable $variable is set to the value $value, but only ",
     * including the trailing space and some HTML markup.
     *
     * @param string|int $value
     */
    protected function buildWarningStartWithKeyAndValue(string $key, $value): string
    {
        return $this->buildWarningStartWithKey($key) . 'contains the value &quot;<strong>' .
            $this->encode((string)$value) . '</strong>&quot;, but only ';
    }

    /**
     * Retrieves the column names of a given DB table name.
     *
     * @param string $tableName the name of an existing DB table (must not be empty, must exist)
     *
     * @return string[] column names as values
     */
    private function getDbColumnNames(string $tableName): array
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionForTable($tableName);
        $statement = $connection->query('SHOW FULL COLUMNS FROM `' . $tableName . '`');
        $columns = [];
        foreach ($statement->fetchAll() as $row) {
            $columns[] = $row['Field'];
        }

        return $columns;
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
            $encodedFileName = $this->encode($rawFileName);
            $message = 'The specified file <strong>' . $encodedFileName . '</strong> cannot be read. ' .
                $description . ' Please either create the file <strong>' . $encodedFileName .
                '</strong> or select an existing file using the TypoScript setup variable <strong>' .
                $this->buildEncodedConfigurationPath($key) . '</strong>.';
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

        $message = $this->buildWarningStartWithKey($key) . 'is empty, but needs to be non-empty. ' . $explanation;
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
        return $this->checkIfMultiInSetOrEmpty($key, $explanation, $allowedValues);
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
    protected function checkIfNonNegativeIntegerOrEmpty(string $key, string $explanation): bool
    {
        if (!$this->configuration->hasString($key)) {
            return true;
        }

        $value = $this->configuration->getAsString($key);
        $okay = \ctype_digit($value);
        if (!$okay) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) . 'non-negative integers are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }

    /**
     * Checks whether a configuration value has an non-negative integer value in the specified, inclusive range.
     *
     * @throws \InvalidArgumentException
     */
    protected function checkIfIntegerInRange(string $key, int $minimum, int $maximum, string $explanation): bool
    {
        if ($minimum > $maximum) {
            throw new \InvalidArgumentException('$minimum must be <= $maximum.', 1616069185);
        }
        if (!$this->checkIfNonNegativeIntegerOrEmpty($key, $explanation)) {
            return false;
        }

        $value = $this->configuration->getAsInteger($key);
        $okay = $value >= $minimum && $value <= $maximum;
        if ($value < $minimum || $value > $maximum) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                'integers from ' . $minimum . ' to ' . $maximum . ' (including these values) are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }

    /**
     * Checks whether a configuration value has a non-negative integer.
     */
    protected function checkIfPositiveInteger(string $key, string $explanation): bool
    {
        return $this->checkForNonEmptyString($key, $explanation)
            && $this->checkIfPositiveIntegerOrEmpty($key, $explanation);
    }

    /**
     * Checks whether a configuration value has a non-negative integer value (or is empty).
     */
    protected function checkIfPositiveIntegerOrEmpty(string $key, string $explanation): bool
    {
        if (!$this->configuration->hasString($key)) {
            return true;
        }
        if (!$this->checkIfNonNegativeIntegerOrEmpty($key, $explanation)) {
            return false;
        }

        $value = $this->configuration->getAsInteger($key);
        $okay = $value > 0;
        if (!$okay) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) . 'positive integers are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }

    /**
     * Checks whether a configuration value has a non-negative integer value (or is empty).
     */
    protected function checkIfNonNegativeInteger(string $key, string $explanation): bool
    {
        return $this->checkForNonEmptyString($key, $explanation)
            && $this->checkIfNonNegativeIntegerOrEmpty($key, $explanation);
    }

    /**
     * Checks whether a configuration value is non-empty
     * and its comma-separated values lie within a set of allowed values.
     *
     * @param string[] $allowedValues allowed values (must not be empty)
     */
    protected function checkIfMultiInSetNotEmpty(string $key, string $explanation, array $allowedValues): bool
    {
        return $this->checkForNonEmptyString($key, $explanation)
            && $this->checkIfMultiInSetOrEmpty($key, $explanation, $allowedValues);
    }

    /**
     * Checks whether a configuration value either is empty
     * or its comma-separated values lie within a set of allowed values.
     *
     * @param string[] $allowedValues allowed values (must not be empty)
     */
    protected function checkIfMultiInSetOrEmpty(string $key, string $explanation, array $allowedValues): bool
    {
        if (!$this->configuration->hasString($key)) {
            return true;
        }

        $okay = true;

        /** @var array<int, non-empty-string> $values */
        $values = GeneralUtility::trimExplode(',', $this->configuration->getAsString($key), true);

        foreach ($values as $value) {
            if (!\in_array($value, $allowedValues, true)) {
                $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                    'the following values are allowed: <br/><strong>' . $this->buildValueOverview($allowedValues) .
                    '</strong><br />' .
                    $explanation;
                $this->addWarningAndRequestCorrection($key, $message);
                $okay = false;
            }
        }

        return $okay;
    }

    /**
     * Checks whether a configuration value is non-empty
     * and is one of the column names of a given DB table.
     */
    public function checkIfSingleInTableColumnsNotEmpty(string $key, string $explanation, string $tableName): bool
    {
        return $this->checkIfSingleInSetNotEmpty($key, $explanation, $this->getDbColumnNames($tableName));
    }

    /**
     * Checks whether a configuration value either is empty
     * or is one of the column names of a given DB table.
     */
    protected function checkIfSingleInTableColumnsOrEmpty(string $key, string $explanation, string $tableName): bool
    {
        return $this->checkIfSingleInSetOrEmpty($key, $explanation, $this->getDbColumnNames($tableName));
    }

    /**
     * Checks whether a configuration value is non-empty
     * and its comma-separated values is a column name of a given DB table.
     */
    protected function checkIfMultiInTableColumnsNotEmpty(string $key, string $explanation, string $tableName): bool
    {
        return $this->checkIfMultiInSetNotEmpty($key, $explanation, $this->getDbColumnNames($tableName));
    }

    /**
     * Checks whether a configuration value either is empty
     * or its comma-separated values is a column name of a given DB table.
     */
    protected function checkIfMultiInTableColumnsOrEmpty(string $key, string $explanation, string $tableName): bool
    {
        return $this->checkIfMultiInSetOrEmpty($key, $explanation, $this->getDbColumnNames($tableName));
    }

    /**
     * Checks whether the salutation mode is set correctly.
     */
    protected function checkSalutationMode(): bool
    {
        return $this->checkIfSingleInSetNotEmpty(
            'salutation',
            'This variable controls the salutation mode (formal or informal).
            If it is not set correctly, some output cannot be created at all.',
            ['formal', 'informal']
        );
    }

    /**
     * Checks whether a configuration value matches the given regular expression.
     */
    protected function checkRegExp(string $key, string $explanation, string $expression): bool
    {
        $value = $this->configuration->getAsString($key);
        if (\preg_match($expression, $value)) {
            return true;
        }

        $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
            ' values matching the regular expression <code>' . $this->encode($expression) . '</code> are allowed. ' .
            $explanation;
        $this->addWarningAndRequestCorrection($key, $message);

        return false;
    }

    /**
     * Checks whether a configuration value either is empty or contains a comma-separated list of integers.
     */
    protected function checkIfIntegerListOrEmpty(string $key, string $explanation): bool
    {
        return $this->checkRegExp($key, $explanation, '/^(\\d+( *, *\\d+)*)?$/');
    }

    /**
     * Checks whether a configuration value is non-empty and contains a comma-separated list of integers.
     */
    protected function checkIfIntegerListNotEmpty(string $key, string $explanation): bool
    {
        return $this->checkForNonEmptyString($key, $explanation)
            && $this->checkIfIntegerListOrEmpty($key, $explanation);
    }

    /**
     * Checks that an e-mail address is valid or empty.
     */
    public function checkIsValidEmailOrEmpty(string $key, string $explanation): bool
    {
        if (!$this->configuration->hasString($key)) {
            return true;
        }

        $value = $this->configuration->getAsString($key);
        $okay = GeneralUtility::validEmail($value);
        if (!$okay) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                'valid email addresses are allowed. ' . $explanation;
            $this->addWarningAndRequestCorrection($key, $message);
        }

        return $okay;
    }

    /**
     * Checks that an e-mail address is valid and non-empty.
     */
    public function checkIsValidEmailNotEmpty(string $key, string $explanation): bool
    {
        return $this->checkForNonEmptyString($key, $explanation)
            && $this->checkIsValidEmailOrEmpty($key, $explanation);
    }

    /**
     * Checks that there is a valid email address set in $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'].
     */
    public function checkIsValidDefaultFromEmailAddress(): bool
    {
        /** @var SystemEmailFromBuilder $emailBuilder */
        $emailBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);
        $okay = $emailBuilder->canBuild();

        if (!$okay) {
            $this->addWarning(
                'Please set a valid email address in ' .
                "<code>\$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']</code>. " .
                'This makes sure that the emails sent from extensions have a valid From: address and can be ' .
                'sent without problems.'
            );
        }

        return $okay;
    }
}
