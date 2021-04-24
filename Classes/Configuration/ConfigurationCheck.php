<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Email\SystemEmailFromBuilder;
use OliverKlee\Oelib\Interfaces\ConfigurationCheckable;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Oelib\Templating\TemplateHelper;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class checks the extension configuration (TypoScript setup) and some data for
 * basic sanity. This works for FE plug-ins, BE modules and free-floating data
 * structures.
 *
 * Functions for checking a class (optionally with a flavor) must follow the
 * naming schema "check_classname" or "check_classname_flavor"
 * (if a flavor is used).
 *
 * Example: The check method for objects of the class "tx_seminars_seminarbag"
 * (without any special flavor) must be named "check_tx_seminars_seminarbag".
 * The check method for objects of the class "tx_seminars_pi1" with the flavor
 * "seminar_registration" needs to be named
 * "check_tx_seminars_pi1_seminar_registration".
 *
 * The correct functioning of this class does not rely on any HTML templates or
 * language files so it works even under the worst of circumstances.
 *
 * @deprecated will be removed in oelib 4.0, use the new `AbstractConfigurationCheck` instead
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationCheck
{
    /**
     * the object whose configuration should be checked
     *
     * @var TemplateHelper|ConfigurationCheckable
     */
    protected $objectToCheck = null;

    /**
     * @var string the (cached) class name of $this->objectToCheck
     */
    private $className = '';

    /**
     * @var string the "flavor" of the object in case the class name does
     *             not to sufficiently indicate exactly which configuration
     *             values to check
     */
    private $flavor = '';

    /**
     * @var string the error to return (or an empty string if there is no error)
     */
    private $errorText = '';

    /**
     * @param TemplateHelper $objectToCheck
     *        the object that will be checked for configuration problems
     */
    public function __construct(TemplateHelper $objectToCheck)
    {
        $this->objectToCheck = $objectToCheck;
        $this->className = get_class($this->objectToCheck);
    }

    /**
     * Sets the "flavor" of the object to check. The flavor is used to
     * differentiate between different kinds of objects of the same class,
     * e.g. the list view and the single view (which both are pi1 objects).
     *
     * @param string $flavor a short string identifying the "flavor" of the object to check (may be empty)
     *
     * @return void
     */
    public function setFlavor(string $flavor)
    {
        $this->flavor = $flavor;
    }

    /**
     * Returns the current flavor.
     *
     * @return string the current flavor (or an empty string if no flavor is set)
     */
    public function getFlavor(): string
    {
        return $this->flavor;
    }

    /**
     * Detects the class of the object to check and performs the sanity checks.
     * If everything is okay, an empty string is returned.
     * If there are errors, the first error is returned (not wrapped).
     * The error message always is in English.
     *
     * If there is more than one error message, the first error needs to be
     * fixed before the second error can be seen. This is intended as some
     * errors may cause a row of other errors which disappear when the first
     * error has been fixed.
     *
     * Note: This function expected $this->checkByClassNameAndFlavor() to be defined!
     *
     * @return string an error message (or an empty string)
     */
    public function checkIt(): string
    {
        $this->checkByClassNameAndFlavor();

        return $this->getRawMessage();
    }

    /**
     * Detects the class of the object to check and performs the sanity checks.
     * If everything is okay, an empty string is returned.
     * If there are errors, the first error is returned (wrapped by wrap()).
     * The error message always is in English.
     *
     * If there is more than one error message, the first error needs to be
     * fixed before the second error can be seen. This is intended as some
     * errors may cause a row of other errors which disappear when the first
     * error has been fixed.
     *
     * Note: This function expected $this->checkByClassNameAndFlavor() to be defined!
     *
     * @return string an error message wrapped by wrap() (or an empty string)
     */
    public function checkItAndWrapIt(): string
    {
        $this->checkByClassNameAndFlavor();

        return $this->getWrappedMessage();
    }

    /**
     * Calls the correct configuration checks, depending on the class name of
     * $this->objectToCheck and (if applicable) on $this->flavor.
     *
     * @return void
     */
    protected function checkByClassNameAndFlavor()
    {
        $checkFunctionName = 'check_' . $this->className;
        if (!empty($this->flavor)) {
            $checkFunctionName .= '_' . $this->flavor;
        }

        // Check whether a check for the corresponding class exists.
        if (method_exists($this, $checkFunctionName)) {
            $this->$checkFunctionName();
        } else {
            trigger_error(
                'No configuration check ' . $checkFunctionName . ' created yet.'
            );
        }
    }

    /**
     * Adds a warning.
     *
     * This a an alias for `setErrorMessage` in order to ease copy'n'pasting for the new configuration check class.
     *
     * @param string $rawWarningText the warning text, may contain HTML, will not be encoded
     *
     * @return void
     */
    protected function addWarning(string $rawWarningText)
    {
        $this->setErrorMessage($rawWarningText);
    }

    /**
     * Sets the error message in $this->errorText (unless no other error message
     * has already been set).
     *
     * If $this->errorText is empty, it will be set to $message.
     *
     * $message should explain what the problem is, what its negative effects
     * are and what the user can do to fix the problem.
     *
     * If $this->errorText is non-empty or $message is empty,
     * this function is a no-op.
     *
     * @param string $message error text to set (may be empty)
     *
     * @return void
     */
    public function setErrorMessage(string $message)
    {
        if ($message !== '' && $this->errorText === '') {
            $this->errorText = $message;
        }
    }

    /**
     * Sets the error message, consisting of $explanation and a request to change the TypoScript setup
     * variable $key (with the current TypoScript setup path prepended).
     *
     * This a an alias for `setErrorMessageAndRequestCorrection` in order to ease copy'n'pasting for the new
     * configuration check class.
     *
     * @return void
     */
    protected function addWarningAndRequestCorrection(string $key, bool $canUseFlexforms, string $explanation)
    {
        $this->setErrorMessageAndRequestCorrection($key, $canUseFlexforms, $explanation);
    }

    /**
     * Sets the error message, consisting of $explanation and a request to
     * change the TypoScript setup variable $key (with the current TypoScript setup path
     * prepended). If $canUseFlexforms is TRUE, the possibility to change the
     * variable via flexforms is mentioned as well.
     *
     * @param string $key TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $explanation error text to set (may be empty)
     *
     * @return void
     */
    protected function setErrorMessageAndRequestCorrection(string $key, bool $canUseFlexforms, string $explanation)
    {
        $message = $explanation . ' Please fix the TypoScript setup variable <strong>' .
            $this->buildEncodedConfigurationPath($key) . '</strong> in your TypoScript template setup';
        if ($canUseFlexforms) {
            $message .= ' or via FlexForms';
        }
        $message .= '.';
        $this->addWarning($message);
    }

    /**
     * Returns an empty string if there are no errors.
     * Otherwise, returns $this->errorText.
     *
     * Use this method if you want to process this message furether, e.g.
     * for bubbling it up to other configcheck objects.
     *
     * @return string $this->errorText (or an empty string if there are no errors)
     */
    public function getRawMessage(): string
    {
        return $this->errorText;
    }

    /**
     * Returns an empty string if there are no errors.
     * Otherwise, returns $this->errorText wrapped by $this->wrap().
     *
     * Use this method if you want to display this message pretty
     * directly and it doesn't need to get handled to other configcheck
     * objects.
     *
     * @return string $this->errorText wrapped by $this->wrap (or an empty string if there are no errors)
     */
    public function getWrappedMessage(): string
    {
        $result = '';

        if (!empty($this->errorText)) {
            $result = $this->wrap($this->errorText);
        }

        return $result;
    }

    /**
     * Wraps $message in (in this case) <p></p>, styled nicely alarming,
     * with the lang attribute set to "en".
     * In addition, the message is prepended by "Configuration check warning: "
     * and followed by "When that is done, please empty the FE cache and
     * reload this page."
     *
     * This wrapping method can be overwritten for other wrappings.
     *
     * @param string $message text to be wrapped (may be empty)
     *
     * @return string $message wrapped in <p></p>
     */
    protected function wrap(string $message): string
    {
        return '<p lang="en" style="color: #000; background: #fff; ' .
            'padding: .4em; border: 3px solid #f00; clear: both;">' .
            '<strong>Configuration check warning:</strong><br />' .
            $message .
            '<br />When that is done, please empty the ' .
            '<acronym title="front-end">FE</acronym> cache and reload ' .
            'this page.' .
            '<br /><em>The configuration check for this extension can be ' .
            'disabled in the extension manager.</em>' .
            '</p>';
    }

    /**
     * Builds a fully-qualified TypoScript namespace from a suffix, e.g., `plugin.tx_oelib.foo` from `foo`.
     *
     * The output is HTML-safe.
     */
    protected function buildEncodedConfigurationPath(string $localPath): string
    {
        return $this->encode($this->getTSSetupPath() . $localPath);
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
        return $this->buildWarningStartWithKey($key) . 'is set to the value &quot;<strong>' .
            $this->encode((string)$value) . '</strong>&quot;, but only ';
    }

    /**
     * Syntactic sugar for HTML-encoding a string.
     */
    protected function encode(string $rawText): string
    {
        return \htmlspecialchars($rawText, ENT_QUOTES | ENT_HTML5);
    }

    /**
     * Builds a HTML-safe, text-only overview of the given values.
     *
     * @param string[] $values
     */
    protected function buildValueOverview(array $values): string
    {
        return $this->encode('("' . \implode('", "', $values) . '")');
    }

    /**
     * Checks whether the static template has been included.
     *
     * @return void
     */
    protected function checkStaticIncluded()
    {
        if (!$this->objectToCheck->getConfValueBoolean('isStaticTemplateLoaded')) {
            $this->addWarning(
                'The static template is not included. This has the effect ' .
                'that important default values do not get set. To fix ' .
                'this, please include this extension\'s template under ' .
                '<em>Include static (from extensions)</em> in your TypoScript ' .
                'template.'
            );
        }
    }

    /**
     * Checks whether the HTML template is provided and the file exists.
     *
     * @param bool $canUseFlexforms
     *        whether the template can also be selected via flexforms
     *
     * @return void
     */
    protected function checkTemplateFile(bool $canUseFlexforms = false)
    {
        if (TYPO3_MODE === 'BE') {
            return;
        }

        $this->checkForNonEmptyString(
            'templateFile',
            $canUseFlexforms,
            's_template_special',
            'This value specifies the HTML template which is essential when ' .
            'creating any output from this extension.'
        );

        if (
            $this->getFrontEndController() instanceof TypoScriptFrontendController
            && $this->objectToCheck->hasConfValueString('templateFile', 's_template_special')
        ) {
            $rawFileName = $this->objectToCheck->getConfValueString('templateFile', 's_template_special', true);

            $file = GeneralUtility::getFileAbsFileName($rawFileName);
            if ($file === '' || !\is_file($file)) {
                $encodedFileName = $this->encode($rawFileName);
                $message = 'The specified HTML template file <strong>' . $encodedFileName .
                    '</strong> cannot be read. ' .
                    'The HTML template file is essential when creating any ' .
                    'output from this extension. ' .
                    'Please either create the file <strong>' . $encodedFileName .
                    '</strong> or select an existing file using the TypoScript setup variable <strong>' .
                    $this->buildEncodedConfigurationPath('templateFile') . '</strong>';
                if ($canUseFlexforms) {
                    $message .= ' or via FlexForms';
                }
                $message .= '.';
                $this->addWarning($message);
            }
        }
    }

    /**
     * Checks whether the CSS file (if a name is provided) actually is a file.
     * If no file name is provided, no error will be displayed as this is
     * perfectly allowed.
     *
     * @return void
     */
    protected function checkCssFileFromConstants()
    {
        $key = 'cssFile';
        if ($this->objectToCheck->hasConfValueString($key)) {
            $message = $this->buildWarningStartWithKey($key) .
                'is set, but should not be set. You will have to unset ' .
                'the TypoScript setup variable and set <strong>' . $this->buildEncodedConfigurationPath($key) .
                '</strong> in your TypoScript constants instead.';
            $this->addWarning($message);
        } else {
            $message = '';
        }

        $frontEndController = $this->getFrontEndController();
        $typoScriptSetupPage = &$frontEndController->tmpl->setup['page.'];
        $fileName = (string)$typoScriptSetupPage['includeCSS.'][$this->objectToCheck->prefixId];
        if ($fileName !== '') {
            $file = GeneralUtility::getFileAbsFileName($fileName);
            if ($file === '' || !\is_file($file)) {
                $encodedFileName = $this->encode($fileName);
                $message .= 'The specified CSS file <strong>' . $encodedFileName .
                    '</strong> cannot be read. ' .
                    'If that constant does not point to an existing file, no ' .
                    'special CSS will be used for styling this extension\'s ' .
                    'HTML. Please either create the file <strong>' . $encodedFileName .
                    '</strong> or select an existing file using the TypoScript ' .
                    'constant <strong>' . $this->buildEncodedConfigurationPath($key) .
                    '</strong>' .
                    '. If you do not want to use any special CSS, you ' .
                    'can set that variable to an empty string.';
                $this->addWarning($message);
            }
        }
    }

    /**
     * Checks whether a configuration value contains a non-empty-string.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for
     *        and why it needs to be non-empty, must not be empty
     *
     * @return void
     */
    public function checkForNonEmptyString(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);
        $this->checkForNonEmptyStringValue($value, $key, $canUseFlexforms, $explanation);
    }

    /**
     * Checks whether a provided value is a non-empty string. The
     * value to check must be provided as a parameter and is not fetched
     * automatically; the $key parameter is only used to create the
     * warning message.
     *
     * @param string $value
     *        the value to check
     * @param string $key
     *        TypoScript setup field name to mention in the warning, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for
     *        and why it needs to be non-empty, must not be empty
     *
     * @return void
     */
    protected function checkForNonEmptyStringValue(
        string $value,
        string $key,
        bool $canUseFlexforms,
        string $explanation
    ) {
        if ($value === '') {
            $message = $this->buildWarningStartWithKey($key) . 'is empty, but needs to be non-empty. ' . $explanation;
            $this->addWarningAndRequestCorrection(
                $key,
                $canUseFlexforms,
                $message
            );
        }
    }

    /**
     * Checks whether a configuration value is non-empty and lies within a set
     * of allowed values.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string[] $allowedValues
     *        allowed values (must not be empty)
     *
     * @return void
     */
    protected function checkIfSingleInSetNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        array $allowedValues
    ) {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
        $this->checkIfSingleInSetOrEmpty($key, $canUseFlexforms, $sheet, $explanation, $allowedValues);
    }

    /**
     * Checks whether a configuration value either is empty or lies within a
     * set of allowed values.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string[] $allowedValues
     *        allowed values (must not be empty)
     *
     * @return void
     */
    protected function checkIfSingleInSetOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        array $allowedValues
    ) {
        if ($this->objectToCheck->hasConfValueString($key, $sheet)) {
            $value = $this->objectToCheck->getConfValueString($key, $sheet);
            $this->checkIfSingleInSetOrEmptyValue($value, $key, $canUseFlexforms, $explanation, $allowedValues);
        }
    }

    /**
     * Checks whether a provided value either is empty or lies within a
     * set of allowed values. The value to check must be provided as a parameter
     * and is not fetched automatically; the $key parameter is only used
     * to create the warning message.
     *
     * @param string $value
     *        the value to check
     * @param string $key
     *        TypoScript setup field name to mention in the warning, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string[] $allowedValues
     *        allowed values (must not be empty)
     *
     * @return void
     */
    protected function checkIfSingleInSetOrEmptyValue(
        string $value,
        string $key,
        bool $canUseFlexforms,
        string $explanation,
        array $allowedValues
    ) {
        if (!empty($value) && !\in_array($value, $allowedValues, true)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                'the following values are allowed: <br/><strong>' . $this->buildValueOverview($allowedValues) .
                '</strong><br />' .
                $explanation;
            $this->addWarningAndRequestCorrection(
                $key,
                $canUseFlexforms,
                $message
            );
        }
    }

    /**
     * Checks whether a configuration value has a boolean value.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfBoolean(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $this->checkIfSingleInSetNotEmpty($key, $canUseFlexforms, $sheet, $explanation, ['0', '1']);
    }

    /**
     * Checks whether a configuration value has an integer value (or is empty).
     *
     * In the new configuration check, the corresponding method is named checkIfNonNegativeIntegerOrEmpty.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfInteger(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);

        if (!preg_match('/^\\d*$/', $value)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) . 'integers are allowed. ' . $explanation;
            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
        }
    }

    /**
     * Checks whether a configuration value has an integer value in a specified range (or is empty).
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param int $minValue
     *        the first value of the range which is allowed
     * @param int $maxValue
     *        the last value of the range which is allowed
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfIntegerInRange(
        string $key,
        int $minValue,
        int $maxValue,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        // Checks if our minimum value is bigger then our maximum value and
        // swaps their values if this is the case.
        if ($minValue > $maxValue) {
            $temp = $maxValue;
            $maxValue = $minValue;
            $minValue = $temp;
        }

        $value = $this->objectToCheck->getConfValueInteger($key, $sheet);

        if (($value < $minValue) || ($value > $maxValue)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                'integers from ' . $minValue . ' to ' . $maxValue . ' are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
        }
    }

    /**
     * Checks whether a provided value has an integer value (or is empty). The
     * value to check must be provided as a parameter and is not fetched
     * automatically; the $key parameter is only used to create the
     * warning message.
     *
     * @param string $value
     *        the value to check
     * @param string $key
     *        TypoScript setup field name to mention in the warning, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfPositiveIntegerValue(
        string $value,
        string $key,
        bool $canUseFlexforms,
        string $explanation
    ) {
        $this->checkForNonEmptyStringValue($value, $key, $canUseFlexforms, $explanation);
        if (!preg_match('/^[1-9]\\d*$/', $value)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) . 'positive integers are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
        }
    }

    /**
     * Checks whether a configuration value has a positive (thus non-zero) integer value.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfPositiveInteger(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);
        $this->checkIfPositiveIntegerValue($value, $key, $canUseFlexforms, $explanation);
    }

    /**
     * Checks whether a configuration value has a positive (thus non-zero)
     * integer value or is empty.
     *
     * @param string $key
     *        TypoScript setup field name to extract, may be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfPositiveIntegerOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);
        if (!empty($value) && !preg_match('/^[1-9]\\d*$/', $value)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                'positive integers and empty strings are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
        }
    }

    /**
     * Checks whether a configuration value has a positive integer value or is zero.
     *
     * In the new configuration check, the corresponding method is named checkIfNonNegativeInteger.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfPositiveIntegerOrZero(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);

        $this->checkForNonEmptyStringValue($value, $key, $canUseFlexforms, $explanation);

        if (!preg_match('/^\\d+$/', $value)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) . 'positive integers are allowed. ' .
                $explanation;

            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
        }
    }

    /**
     * Checks whether a configuration value is non-empty and its
     * comma-separated values lie within a set of allowed values.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string[] $allowedValues
     *        allowed values (must not be empty)
     *
     * @return void
     */
    protected function checkIfMultiInSetNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        array $allowedValues
    ) {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
        $this->checkIfMultiInSetOrEmpty($key, $canUseFlexforms, $sheet, $explanation, $allowedValues);
    }

    /**
     * Checks whether a configuration value either is empty or its
     * comma-separated values lie within a set of allowed values.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string[] $allowedValues
     *        allowed values (must not be empty)
     *
     * @return void
     */
    protected function checkIfMultiInSetOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        array $allowedValues
    ) {
        if ($this->objectToCheck->hasConfValueString($key, $sheet)) {
            $allValues = GeneralUtility::trimExplode(',', $this->objectToCheck->getConfValueString($key, $sheet), true);

            foreach ($allValues as $currentValue) {
                if (!in_array($currentValue, $allowedValues, true)) {
                    $message = $this->buildWarningStartWithKeyAndValue($key) .
                        'the following values are allowed: <br/><strong>' . $this->buildValueOverview($allowedValues) .
                        '</strong><br />' .
                        $explanation;
                    $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
                }
            }
        }
    }

    /**
     * Checks whether a configuration value is non-empty and is one of the
     * column names of a given DB table.
     *
     * This method is named `checkIfSingleInTableColumnsNotEmpty` in the new configuration check.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $tableName
     *        a DB table name (must not be empty)
     *
     * @return void
     */
    public function checkIfSingleInTableNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $tableName
    ) {
        $this->checkIfSingleInSetNotEmpty(
            $key,
            $canUseFlexforms,
            $sheet,
            $explanation,
            $this->getDbColumnNames($tableName)
        );
    }

    /**
     * Checks whether a configuration value either is empty
     * or is one of the column names of a given DB table.
     *
     * This method is named `checkIfSingleInTableColumnsOrEmpty` in the new configuration check.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $tableName
     *        a DB table name (must not be empty)
     *
     * @return void
     */
    protected function checkIfSingleInTableOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $tableName
    ) {
        $this->checkIfSingleInSetOrEmpty(
            $key,
            $canUseFlexforms,
            $sheet,
            $explanation,
            $this->getDbColumnNames($tableName)
        );
    }

    /**
     * Checks whether a configuration value is non-empty and its
     * and its comma-separated values is a column name of a given DB table.
     *
     * This method is named `checkIfMultiInTableColumnsNotEmpty` in the new configuration check.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $tableName
     *        a DB table name (must not be empty)
     *
     * @return void
     */
    protected function checkIfMultiInTableNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $tableName
    ) {
        $this->checkIfMultiInSetNotEmpty(
            $key,
            $canUseFlexforms,
            $sheet,
            $explanation,
            $this->getDbColumnNames($tableName)
        );
    }

    /**
     * Checks whether a configuration value either is empty
     * or its comma-separated values is a column name of a given DB table.
     *
     * This method is named `checkIfMultiInTableColumnsOrEmpty` in the new configuration check.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $tableName
     *        a DB table name (must not be empty)
     *
     * @return void
     */
    protected function checkIfMultiInTableOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $tableName
    ) {
        $this->checkIfMultiInSetOrEmpty(
            $key,
            $canUseFlexforms,
            $sheet,
            $explanation,
            $this->getDbColumnNames($tableName)
        );
    }

    /**
     * Checks whether the salutation mode is set correctly.
     *
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     *
     * @return void
     */
    protected function checkSalutationMode($canUseFlexforms = false)
    {
        $this->checkIfSingleInSetNotEmpty(
            'salutation',
            $canUseFlexforms,
            'sDEF',
            'This variable controls the salutation mode (formal or informal). ' .
            'If it is not set correctly, some output cannot be created at all.',
            ['formal', 'informal']
        );
    }

    /**
     * Gets the path for TypoScript setup where $this->objectToCheck's configuration is
     * located. This includes the extension key, (possibly) something like pi1
     * and the trailing dot.
     *
     * @return string the TypoScript setup configuration path including the trailing dot, e.g. "plugin.tx_seminars_pi1."
     */
    protected function getTSSetupPath(): string
    {
        if ($this->objectToCheck instanceof ConfigurationCheckable) {
            return $this->objectToCheck->getTypoScriptNamespace();
        }

        $result = 'plugin.tx_' . $this->objectToCheck->extKey;
        $matches = [];
        if (\preg_match('/_pi\\d+$/', $this->className, $matches)) {
            $result .= $matches[0];
        }

        $result .= '.';

        return $result;
    }

    /**
     * Retrieves the column names of a given DB table name.
     *
     * @param string $tableName
     *        the name of a existing DB table (must not be empty, must exist)
     *
     * @return string[] column names as values
     */
    protected function getDbColumnNames(string $tableName): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
        $statement = $connection->query('SHOW FULL COLUMNS FROM `' . $tableName . '`');
        $columns = [];
        foreach ($statement->fetchAll() as $row) {
            $columns[] = $row['Field'];
        }

        return $columns;
    }

    /**
     * Checks whether a configuration value matches a regular expression.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $regExp
     *        a regular expression (including the delimiting slashes)
     *
     * @return void
     */
    protected function checkRegExp(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $regExp
    ) {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);

        if (!preg_match($regExp, $value)) {
            $message = $this->buildWarningStartWithKeyAndValue($key, $value) .
                ' values matching the regular expression <code>' . $this->encode($regExp) . '</code> are allowed. ' .
                $explanation;
            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message);
        }
    }

    /**
     * Checks whether a configuration value is non-empty and matches a regular expression.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $regExp
     *        a regular expression (including the delimiting slashes)
     *
     * @return void
     *
     * @deprecated will be removed without replacement in oelib 4.0
     */
    protected function checkRegExpNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $regExp
    ) {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
        $this->checkRegExp($key, $canUseFlexforms, $sheet, $explanation, $regExp);
    }

    /**
     * Checks whether a configuration value either is empty or contains a
     * comma-separated list of integers (in this case, PIDs).
     *
     * This method is named `checkIfIntegerListOrEmpty` in the new configuration check.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfPidListOrEmpty(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $this->checkRegExp($key, $canUseFlexforms, $sheet, $explanation, '/^([0-9]+(,( *)[0-9]+)*)?$/');
    }

    /**
     * Checks whether a configuration value is non-empty and contains a
     * comma-separated list of integers (in this case, PIDs).
     *
     * This method is named `checkIfIntegerListNotEmpty` in the new configuration check.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfPidListNotEmpty(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
        $this->checkIfPidListOrEmpty($key, $canUseFlexforms, $sheet, $explanation);
    }

    /**
     * Checks whether a configuration value is non-empty and contains a
     * comma-separated list of front-end PIDs.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     *
     * @deprecated will be removed without replacement in oelib 4.0
     */
    protected function checkIfFePagesNotEmpty(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
    }

    /* ####################################################################################
     * The check methods below have not been converted to the new configuration check yet.
     */

    /**
     * Checks whether a configuration value is non-empty and contains a
     * single front-end PID.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfSingleFePageNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        $this->checkIfPositiveInteger($key, $canUseFlexforms, $sheet, $explanation);
    }

    /**
     * Checks whether a configuration value either is empty or contains a
     * single front-end PID.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfSingleFePageOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        $this->checkIfInteger($key, $canUseFlexforms, $sheet, $explanation);
    }

    /**
     * Checks whether a configuration value either is empty or contains a
     * comma-separated list of front-end PIDs.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     *
     * @deprecated will be removed in oelib 4.0.0; is a no-op in the meantime
     */
    protected function checkIfFePagesOrEmpty(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        if (Typo3Version::isNotHigherThan(8)) {
            GeneralUtility::logDeprecatedFunction();
        } else {
            trigger_error(
                'checkIfFePagesOrEmpty() is deprecated and will be removed in oelib 4.0.0; is a no-op in the meantime',
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * Checks whether a configuration value is non-empty and contains a
     * comma-separated list of system folder PIDs.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfSysFoldersNotEmpty(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
    }

    /**
     * Checks whether a configuration value is non-empty and contains a
     * single system folder PID.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    protected function checkIfSingleSysFolderNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        $this->checkIfPositiveInteger($key, $canUseFlexforms, $sheet, $explanation);
    }

    /**
     * Checks whether a configuration value either is empty or contains a
     * single system folder PID.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     *
     * @deprecated will be removed without replacement in oelib 4.0
     */
    protected function checkIfSingleSysFolderOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation
    ) {
        $this->checkIfInteger($key, $canUseFlexforms, $sheet, $explanation);
    }

    /**
     * Checks whether a configuration value either is empty or contains a
     * comma-separated list of system folder PIDs.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     *
     * @deprecated will be removed in oelib 4.0.0; is a no-op in the meantime
     */
    protected function checkIfSysFoldersOrEmpty(string $key, bool $canUseFlexforms, string $sheet, string $explanation)
    {
        if (Typo3Version::isNotHigherThan(8)) {
            GeneralUtility::logDeprecatedFunction();
        } else {
            trigger_error(
                'checkIfSysFoldersOrEmpty() is deprecated and will be removed in oelib 4.0.0; ' .
                'is a no-op in the meantime',
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * Checks whether a configuration value either is empty or contains a
     * comma-separated list of PIDs that specify pages or a given type.
     *
     * @param string $key
     *        TypoScript setup field name to extract, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string $typeCondition
     *        a comparison operator with a value that will be used in a SQL
     *        query to check for the correct page types, for example "<199" or
     *        "=254", must not be empty
     *
     * @return void
     *
     * @deprecated will be removed in oelib 4.0.0; is a no-op in the meantime
     */
    protected function checkPageTypeOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        string $explanation,
        string $typeCondition
    ) {
        if (Typo3Version::isNotHigherThan(8)) {
            GeneralUtility::logDeprecatedFunction();
        } else {
            trigger_error(
                'checkPageTypeOrEmpty() is deprecated and will be removed in oelib 4.0.0; is a no-op in the meantime',
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * Checks all values within .listView (including .listView itself).
     *
     * @param string[] $allowedSortFields
     *        allowed sort keys for the list view, must not be empty
     *
     * @return void
     *
     * @deprecated will be removed without replacement in oelib 4.0
     */
    protected function checkListView(array $allowedSortFields)
    {
        $key = 'listView.';

        if (isset($this->objectToCheck->conf[$key])) {
            $this->checkListViewIfSingleInSetNotEmpty(
                'orderBy',
                'This setting controls by which field the list view will be sorted. ' .
                'If this value is not set correctly, sorting will not work correctly.',
                $allowedSortFields
            );
            $this->checkListViewIfSingleInSetNotEmpty(
                'descFlag',
                'This setting controls the default sort order (ascending or descending). ' .
                'If this value is not set correctly, the list view might be sorted the wrong way round.',
                ['0', '1']
            );
            $this->checkListViewIfPositiveInteger(
                'results_at_a_time',
                'This setting controls how many events per page will be displayed in the list view. ' .
                'If this value is not set correctly, the wrong number of events will be displayed.'
            );
            $this->checkListViewIfPositiveInteger(
                'maxPages',
                'This setting controls how many result pages will be linked in the list view. ' .
                'If this value is not set correctly, the result browser will not work correctly.'
            );
        } else {
            $this->addWarningAndRequestCorrection(
                $key,
                false,
                $this->buildWarningStartWithKey($key) .
                'is not set. This setting controls the list view. ' .
                'If this part of the setup is missing, sorting and the result browser will not work correctly.'
            );
        }
    }

    /**
     * Checks whether a configuration value in listView. is non-empty and lies
     * within a set of allowed values.
     *
     * @param string $key
     *        TypoScript setup field name to extract (within listView.), must not be empty
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     * @param string[] $allowedValues
     *        allowed values (must not be empty)
     *
     * @return void
     *
     * @deprecated will be removed without replacement in oelib 4.0
     */
    protected function checkListViewIfSingleInSetNotEmpty(string $key, string $explanation, array $allowedValues)
    {
        $fieldSubPath = 'listView.' . $key;
        $value = $this->objectToCheck->getListViewConfValueString($key);

        $this->checkForNonEmptyStringValue($value, $fieldSubPath, false, $explanation);
        $this->checkIfSingleInSetOrEmptyValue($value, $fieldSubPath, false, $explanation, $allowedValues);
    }

    /**
     * Checks whether a configuration value within listView. has a positive
     * (thus non-zero) integer value.
     *
     * @param string $key
     *        TypoScript setup field name to extract (within listView.), must not be empty
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     *
     * @deprecated will be removed without replacement in oelib 4.0
     */
    protected function checkListViewIfPositiveInteger(string $key, string $explanation)
    {
        $fieldSubPath = 'listView.' . $key;
        $value = $this->objectToCheck->getListViewConfValueString($key);

        $this->checkIfPositiveIntegerValue($value, $fieldSubPath, false, $explanation);
    }

    /**
     * Checks that an e-mail address is valid or empty.
     *
     * @param string $key
     *        TypoScript setup field name to mention in the warning, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param bool $unused unused
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    public function checkIsValidEmailOrEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        bool $unused,
        string $explanation
    ) {
        $value = $this->objectToCheck->getConfValueString($key, $sheet);
        if ($value === '') {
            return;
        }

        if (!GeneralUtility::validEmail($value)) {
            $message = 'The e-mail address in <strong>' . $this->buildEncodedConfigurationPath($key) .
                '</strong> is set to <strong>' . $value . '</strong> ' .
                'which is not valid. E-mails might not be received as long as this address is invalid.<br />';
            $this->addWarningAndRequestCorrection($key, $canUseFlexforms, $message . $explanation);
        }
    }

    /**
     * Checks that an e-mail address is valid and non-empty.
     *
     * @param string $key
     *        TypoScript setup field name to mention in the warning, must not be empty
     * @param bool $canUseFlexforms
     *        whether the value can also be set via flexforms (this will be
     *        mentioned in the error message)
     * @param string $sheet
     *        flexforms sheet pointer, eg. "sDEF", will be ignored if
     *        $canUseFlexforms is set to FALSE
     * @param bool $allowInternalAddresses
     *        whether internal addresses ("user@servername") are considered valid
     * @param string $explanation
     *        a sentence explaining what that configuration value is needed for,
     *        must not be empty
     *
     * @return void
     */
    public function checkIsValidEmailNotEmpty(
        string $key,
        bool $canUseFlexforms,
        string $sheet,
        bool $allowInternalAddresses,
        string $explanation
    ) {
        $this->checkForNonEmptyString($key, $canUseFlexforms, $sheet, $explanation);
        $this->checkIsValidEmailOrEmpty($key, $canUseFlexforms, $sheet, $allowInternalAddresses, $explanation);
    }

    /**
     * @return TypoScriptFrontendController|null
     */
    protected function getFrontEndController()
    {
        return $GLOBALS['TypoScriptFE'] ?? null;
    }

    /**
     * Checks that there is a valid email address set in $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'].
     *
     * @return void
     */
    public function checkIsValidDefaultFromEmailAddress()
    {
        $emailBuilder = GeneralUtility::makeInstance(SystemEmailFromBuilder::class);
        if (!$emailBuilder->canBuild()) {
            $this->addWarning(
                'Please set a valid email address in ' .
                "\$GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']. " .
                'This makes sure that the emails sent from extensions have a valid From: address and can be ' .
                'sent without problems.'
            );
        }
    }
}
