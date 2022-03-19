<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Templating;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Language\SalutationSwitcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This utility class provides some commonly-used functions for handling
 * templates (in addition to all functionality provided by the base classes).
 */
class TemplateHelper extends SalutationSwitcher
{
    /**
     * @var non-empty-string the regular expression used to find subparts
     */
    private const LABEL_PATTERN = '/###(LABEL_([A-Z0-9_]+))###/';

    /**
     * @var string the prefix used for CSS classes
     */
    public $prefixId = '';

    /**
     * @var string the path of this file relative to the extension directory
     */
    public $scriptRelPath = '';

    /**
     * @var string the extension key
     */
    public $extKey = '';

    /**
     * @var bool whether `init()` already has been called (in order to avoid duplicate calls)
     */
    protected $isInitialized = false;

    /**
     * @var string the file name of the template set via TypoScript or FlexForms
     */
    private $templateFileName = '';

    /**
     * @var Template this object's (only) template
     */
    private $template = null;

    /**
     * Initializes the FE plugin stuff and reads the configuration.
     *
     * It is harmless if this function gets called multiple times as it
     * recognizes this and ignores all calls but the first one.
     *
     * This is merely a convenience function.
     *
     * If the parameter is omitted, the configuration for `plugin.tx_[extkey]` is
     * used instead, e.g., `plugin.tx_seminars`.
     *
     * @param array<string, mixed>|mixed $configuration TypoScript configuration for the plugin (usually an array)
     */
    public function init($configuration = null): void
    {
        if ($this->isInitialized) {
            return;
        }

        if (\is_array($configuration)) {
            $this->conf = $configuration;
        }
        $this->ensureContentObject();

        if ($this->extKey !== '' && !empty($this->conf)) {
            $this->pi_setPiVarDefaults();
        }

        $this->isInitialized = true;
    }

    /**
     * @return bool
     */
    protected function isConfigurationCheckEnabled(): bool
    {
        if ($this->extKey === '') {
            return false;
        }

        return ConfigurationProxy::getInstance($this->extKey)->getAsBoolean('enableConfigCheck');
    }

    /**
     * Ensures that $this->cObj points to a valid content object.
     *
     * If this object already has a valid cObj, this function does nothing.
     *
     * If there is a front end and this object does not have a cObj yet, the cObj from the front end is used.
     *
     * If this object has no cObj and there is no front end, this function will do nothing.
     */
    protected function ensureContentObject(): void
    {
        if ($this->cObj instanceof ContentObjectRenderer) {
            return;
        }

        $frontEnd = $this->getFrontEndController();
        if ($frontEnd instanceof TypoScriptFrontendController && $frontEnd->cObj instanceof ContentObjectRenderer) {
            $this->cObj = $frontEnd->cObj;
        }
    }

    /**
     * Checks that this object is properly initialized.
     */
    public function isInitialized(): bool
    {
        return $this->isInitialized;
    }

    /**
     * Gets a value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TypoScript setup,
     * an empty string is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $isFileName whether this is a filename, which has to be combined with a path
     * @param bool $ignoreFlexform whether to ignore the flexform values and just get the settings from TypoScript,
     *        may be empty
     *
     * @return string the value of the corresponding flexforms or TypoScript setup entry (may be empty)
     */
    private function getConfValue(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $isFileName = false,
        bool $ignoreFlexform = false
    ): string {
        $configurationValueFromTypoScript = (string)($this->conf[$fieldName] ?? '');
        $contentObject = $this->cObj;
        if (!$contentObject instanceof ContentObjectRenderer) {
            return $configurationValueFromTypoScript;
        }

        $flexFormsData = $contentObject->data['pi_flexform'] ?? null;
        if (!$ignoreFlexform && \is_array($flexFormsData)) {
            $flexFormsValue = $this->pi_getFFvalue($contentObject->data['pi_flexform'], $fieldName, $sheet);
        } else {
            $flexFormsValue = null;
        }

        if ($isFileName && $flexFormsValue !== null && $flexFormsValue !== '') {
            $flexFormsValue = $this->addPathToFileName($flexFormsValue);
        }

        return $flexFormsValue ?: $configurationValueFromTypoScript;
    }

    /**
     * Adds a path in front of the file name.
     * This is used for files that are selected in the Flexform of the front end
     * plugin.
     *
     * If no path is provided, the default (uploads/[extension_name]/) is used
     * as path.
     *
     * An example (default, with no path provided):
     * If the file is named 'template.tmpl', the output will be
     * 'uploads/[extension_name]/template.tmpl'.
     * The '[extension_name]' will be replaced by the name of the calling
     * extension.
     *
     * @param string $fileName the file name
     * @param string $path the path to the file (without filename), must contain a slash at the end,
     *        may contain a slash at the beginning (if not relative)
     *
     * @return non-empty-string the complete path including file name
     *
     * @deprecated will be removed in oelib 5.0
     */
    private function addPathToFileName(string $fileName, string $path = ''): string
    {
        if (empty($path)) {
            $path = 'uploads/tx_' . $this->extKey . '/';
        }

        return $path . $fileName;
    }

    /**
     * Gets a trimmed string value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TS
     * setup, an empty string is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $isFileName whether this is a filename, which has to be combined with a path
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return string the trimmed value of the corresponding flexforms or
     *                TypoScript setup entry (may be empty)
     */
    public function getConfValueString(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $isFileName = false,
        bool $ignoreFlexform = false
    ): string {
        return trim(
            $this->getConfValue(
                $fieldName,
                $sheet,
                $isFileName,
                $ignoreFlexform
            )
        );
    }

    /**
     * Checks whether a string value from flexforms or TypoScript setup is set.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is checked. If there is no field with that name in TS
     * setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return bool whether there is a non-empty value in the
     *                 corresponding flexforms or TypoScript setup entry
     */
    public function hasConfValueString(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $ignoreFlexform = false
    ): bool {
        return $this->getConfValueString($fieldName, $sheet, false, $ignoreFlexform) !== '';
    }

    /**
     * Gets an integer value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TS
     * setup, zero is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return int the int value of the corresponding flexforms or
     *                 TypoScript setup entry
     */
    public function getConfValueInteger(string $fieldName, string $sheet = 'sDEF'): int
    {
        return (int)$this->getConfValue($fieldName, $sheet);
    }

    /**
     * Checks whether an integer value from flexforms or TypoScript setup is set and
     * non-zero. The priority lies on flexforms; if nothing is found there, the
     * value from TypoScript setup is checked. If there is no field with that name in
     * TypoScript setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return bool whether there is a non-zero value in the
     *                 corresponding flexforms or TypoScript setup entry
     */
    public function hasConfValueInteger(string $fieldName, string $sheet = 'sDEF'): bool
    {
        return (bool)$this->getConfValueInteger($fieldName, $sheet);
    }

    /**
     * Gets a boolean value from flexforms or TypoScript setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TypoScript setup is returned. If there is no field with that name in TS
     * setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return bool the boolean value of the corresponding flexforms or
     *                 TypoScript setup entry
     */
    public function getConfValueBoolean(string $fieldName, string $sheet = 'sDEF'): bool
    {
        return (bool)$this->getConfValue($fieldName, $sheet);
    }

    /**
     * Sets a configuration value.
     *
     * This function is intended to be used for testing purposes only.
     *
     * @param non-empty-string $key key of the configuration property to set
     * @param mixed $value value of the configuration property, may be empty or zero
     */
    public function setConfigurationValue(string $key, $value): void
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty', 1331489491);
        }

        $this->conf[$key] = $value;
    }

    /**
     * Gets the configuration.
     *
     * @return array<string, mixed> configuration array, might be empty
     */
    public function getConfiguration(): array
    {
        return $this->conf;
    }

    /**
     * Retrieves the plugin template file set in `$this->conf['templateFile']`
     * (or also via flexforms if TYPO3 mode is FE) and writes it to `$this->templateCode`.
     * The subparts will be written to $this->templateCache.
     *
     * @param bool $ignoreFlexform whether the settings in the Flexform should be ignored
     */
    public function getTemplateCode(bool $ignoreFlexform = false): void
    {
        // Trying to fetch the template code via `$this->cObj` in BE mode leads to
        // a non-catchable error in the `ContentObjectRenderer` class because the `cObj`
        // configuration array is not initialized properly.
        // As flexforms can be used in FE mode only, `$ignoreFlexform` is set true if we are in the BE mode.
        // By this, `$this->cObj->fileResource` can be sheltered from being called.
        if (TYPO3_MODE === 'BE') {
            $ignoreFlexform = true;
        }

        $templateFileName = $this->getConfValueString(
            'templateFile',
            's_template_special',
            true,
            $ignoreFlexform
        );

        if (!$ignoreFlexform) {
            $templateFileName = GeneralUtility::getFileAbsFileName($templateFileName);
        }

        $this->templateFileName = $templateFileName;
    }

    /**
     * Returns the template object from the template registry for the file name
     * in $this->templateFileName.
     *
     * @return Template the template object for the template file name in `$this->templateFileName`
     */
    protected function getTemplate(): Template
    {
        if (!$this->template instanceof Template) {
            $this->template = TemplateRegistry::get($this->templateFileName);
        }

        return $this->template;
    }

    /**
     * Stores the given HTML template and retrieves all subparts, writing them
     * to $this->templateCache.
     *
     * The subpart names are automatically retrieved from $templateCode and
     * are used as array keys. For this, the ### are removed, but the names stay
     * uppercase.
     *
     * Example: The subpart ###MY_SUBPART### will be stored with the array key
     * 'MY_SUBPART'.
     *
     * @param string $templateCode the content of the HTML template
     */
    public function processTemplate(string $templateCode): void
    {
        $this->getTemplate()->processTemplate($templateCode);
    }

    /**
     * Sets a marker's content.
     *
     * Example: If the prefix is "field" and the marker name is "one", the
     * marker "###FIELD_ONE###" will be written.
     *
     * If the prefix is empty and the marker name is "one", the marker
     * "###ONE###" will be written.
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content the marker's content, may be empty
     * @param string $prefix prefix to the marker name (may be empty, case-insensitive, will get uppercased)
     */
    public function setMarker(string $markerName, $content, string $prefix = ''): void
    {
        $this->getTemplate()->setMarker($markerName, $content, $prefix);
    }

    /**
     * Gets a marker's content.
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     *
     * @return string the marker's content or an empty string if the marker has not been set before
     */
    public function getMarker(string $markerName): string
    {
        return $this->getTemplate()->getMarker($markerName);
    }

    /**
     * Sets a subpart's content.
     *
     * Example: If the prefix is "field" and the subpart name is "one", the
     * subpart "###FIELD_ONE###" will be written.
     *
     * If the prefix is empty and the subpart name is "one", the subpart
     * "###ONE###" will be written.
     *
     * @param non-empty-string $subpartName name without the ### signs, case-insensitive, will get uppercased
     * @param mixed $content the subpart's content, may be empty
     * @param string $prefix prefix to the subpart name (may be empty, case-insensitive, will get uppercased)
     *
     * @throws NotFoundException
     */
    public function setSubpart(string $subpartName, $content, string $prefix = ''): void
    {
        $this->getTemplate()->setSubpart($subpartName, $content, $prefix);
    }

    /**
     * Sets a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotEmpty
     */
    public function setMarkerIfNotZero(string $markerName, $content, string $markerPrefix = ''): bool
    {
        return $this->getTemplate()->setMarkerIfNotZero($markerName, $content, $markerPrefix);
    }

    /**
     * Sets a marker based on whether the (string) content is non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotZero
     */
    public function setMarkerIfNotEmpty(string $markerName, $content, string $markerPrefix = ''): bool
    {
        return $this->getTemplate()->setMarkerIfNotEmpty($markerName, $content, $markerPrefix);
    }

    /**
     * Checks whether a subpart is visible.
     *
     * Note: If the subpart to check does not exist, this function will return false.
     *
     * @param string $subpartName name of the subpart to check (without the ###)
     *
     * @return bool TRUE if the subpart is visible, FALSE otherwise
     */
    public function isSubpartVisible(string $subpartName): bool
    {
        return $this->getTemplate()->isSubpartVisible($subpartName);
    }

    /**
     * Takes a comma-separated list of subpart names and sets them to hidden. In
     * the process, the names are changed from 'aname' to '###BLA_ANAME###' and
     * used as keys.
     *
     * Example: If the prefix is "field" and the list is "one,two", the subparts
     * "###FIELD_ONE###" and "###FIELD_TWO###" will be hidden.
     *
     * If the prefix is empty and the list is "one,two", the subparts
     * "###ONE###" and "###TWO###" will be hidden.
     *
     * @param string $subparts comma-separated list of the subparts to hide
     *        (case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function hideSubparts(string $subparts, string $prefix = ''): void
    {
        $this->getTemplate()->hideSubparts($subparts, $prefix);
    }

    /**
     * Takes an array of subpart names and sets them to hidden. In the process,
     * the names are changed from 'aname' to '###BLA_ANAME###' and used as keys.
     *
     * Example: If the prefix is "field" and the array has two elements "one"
     * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
     * hidden.
     *
     * If the prefix is empty and the array has two elements "one" and "two",
     * the subparts "###ONE###" and "###TWO###" will be hidden.
     *
     * @param array<string|int, non-empty-string> $subparts subpart names to hide
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function hideSubpartsArray(array $subparts, string $prefix = ''): void
    {
        $this->getTemplate()->hideSubpartsArray($subparts, $prefix);
    }

    /**
     * Takes a comma-separated list of subpart names and unhides them if they
     * have been hidden beforehand.
     *
     * Note: All subpartNames that are provided with the second parameter will
     * not be unhidden. This is to avoid unhiding subparts that are hidden by
     * the configuration.
     *
     * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
     *
     * Example: If the prefix is "field" and the list is "one,two", the subparts
     * "###FIELD_ONE###" and "###FIELD_TWO###" will be unhidden.
     *
     * If the prefix is empty and the list is "one,two", the subparts
     * "###ONE###" and "###TWO###" will be unhidden.
     *
     * @param string $subparts comma-separated list of subpart names to unhide (case-insensitive, will get uppercased),
     *        must not be empty
     * @param string $permanentlyHiddenSubparts comma-separated list of subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function unhideSubparts(
        string $subparts,
        string $permanentlyHiddenSubparts = '',
        string $prefix = ''
    ): void {
        $this->getTemplate()->unhideSubparts(
            $subparts,
            $permanentlyHiddenSubparts,
            $prefix
        );
    }

    /**
     * Takes an array of subpart names and unhides them if they have been hidden
     * beforehand.
     *
     * Note: All subpartNames that are provided with the second parameter will
     * not be unhidden. This is to avoid unhiding subparts that are hidden by
     * the configuration.
     *
     * In the process, the names are changed from 'aname' to '###BLA_ANAME###'.
     *
     * Example: If the prefix is "field" and the array has two elements "one"
     * and "two", the subparts "###FIELD_ONE###" and "###FIELD_TWO###" will be
     * unhidden.
     *
     * If the prefix is empty and the array has two elements "one" and "two",
     * the subparts "###ONE###" and "###TWO###" will be unhidden.
     *
     * @param array<string|int, non-empty-string> $subparts $subparts subpart names to unhide
     *       (may be empty, case-insensitive, will get uppercased)
     * @param string[] $permanentlyHiddenSubparts subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     */
    public function unhideSubpartsArray(
        array $subparts,
        array $permanentlyHiddenSubparts = [],
        string $prefix = ''
    ): void {
        $this->getTemplate()->unhideSubpartsArray($subparts, $permanentlyHiddenSubparts, $prefix);
    }

    /**
     * Sets or hides a marker based on $condition.
     * If $condition is TRUE, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     * If $condition is FALSE, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs,
     *        case-insensitive, will get uppercased
     * @param bool $condition if this is TRUE, the marker will be filled, otherwise the wrapped marker will be hidden
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *       (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarker(
        string $markerName,
        bool $condition,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->getTemplate()->setOrDeleteMarker(
            $markerName,
            $condition,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Sets or hides a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content,
     * $markerPrefix).
     * If (int)$condition is zero, this function removes the wrapping
     * subpart, working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setOrDeleteMarker
     * @see setOrDeleteMarkerIfNotEmpty
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarkerIfNotZero(
        string $markerName,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->getTemplate()->setOrDeleteMarkerIfNotZero(
            $markerName,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Sets or hides a marker based on whether the (string) content is
     * non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content,
     * $markerPrefix).
     * If $condition is empty, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param non-empty-string $markerName the marker's name without the ### signs, case-insensitive,
     *        will get uppercased
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting
     *        (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding
     *        (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if the subpart has been hidden
     *
     * @see setOrDeleteMarker
     * @see setOrDeleteMarkerIfNotZero
     * @see setMarkerContent
     * @see hideSubparts
     */
    public function setOrDeleteMarkerIfNotEmpty(
        string $markerName,
        $content,
        string $markerPrefix = '',
        string $wrapperPrefix = ''
    ): bool {
        return $this->getTemplate()->setOrDeleteMarkerIfNotEmpty(
            $markerName,
            $content,
            $markerPrefix,
            $wrapperPrefix
        );
    }

    /**
     * Retrieves a named subpart, recursively filling in its inner subparts
     * and markers. Inner subparts that are marked to be hidden will be
     * substituted with empty strings.
     *
     * This function either works on the subpart with the name $key or the
     * complete HTML template if $key is an empty string.
     *
     * @param string $key
     *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
     *        or an empty string to use the complete HTML template
     *
     * @return string the subpart content or an empty string if the
     *                subpart is hidden or the subpart name is missing
     */
    public function getSubpart(string $key = ''): string
    {
        return $this->getTemplate()->getSubpart($key);
    }

    /**
     * Retrieves a named subpart, recursively filling in its inner subparts
     * and markers. Inner subparts that are marked to be hidden will be
     * substituted with empty strings.
     *
     * This function either works on the subpart with the name $key or the
     * complete HTML template if $key is an empty string.
     *
     * All label markers in the rendered subpart are automatically replaced with their corresponding localized labels,
     * removing the need use the very expensive setLabels method.
     *
     * @param string $subpartKey
     *        key of an existing subpart, for example 'LIST_ITEM' (without the ###),
     *        or an empty string to use the complete HTML template
     *
     * @return string the subpart content or an empty string if the subpart is hidden or the subpart name is missing
     */
    public function getSubpartWithLabels(string $subpartKey = ''): string
    {
        $renderedSubpart = $this->getSubpart($subpartKey);

        $translator = $this;
        return (string)\preg_replace_callback(
            self::LABEL_PATTERN,
            static function (array $matches) use ($translator): string {
                /** @var non-empty-string $key */
                $key = \strtolower($matches[1]);
                return $translator->translate($key);
            },
            $renderedSubpart
        );
    }

    /**
     * Writes all localized labels for the current template into their corresponding template markers.
     *
     * For this, the label markers in the template must be prefixed with
     * "LABEL_" (e.g., "###LABEL_FOO###"), and the corresponding localization
     * entry must have the same key, but lowercased and without the ###
     * (e.g., "label_foo").
     */
    public function setLabels(): void
    {
        $template = $this->getTemplate();
        foreach ($template->getLabelMarkerNames() as $label) {
            $template->setMarker($label, $this->translate($label));
        }
    }

    /**
     * Resets the list of subparts to hide.
     */
    public function resetSubpartsHiding(): void
    {
        $this->getTemplate()->resetSubpartsHiding();
    }

    /**
     * Intvals all piVars that are supposed to be integers. These are the keys
     * showUid, pointer and mode and the keys provided in $additionalPiVars.
     *
     * If some piVars are not set or no piVars array is defined yet, this
     * function will set the not yet existing piVars to zero.
     *
     * @param array<array-key, string> $additionalPiVars keys for $this->piVars that will be ensured to exist
     *        as integers in `$this->piVars` as well
     */
    protected function ensureIntegerPiVars(array $additionalPiVars = []): void
    {
        if (!\is_array($this->piVars)) {
            $this->piVars = [];
        }

        foreach (\array_merge(['showUid', 'pointer', 'mode'], $additionalPiVars) as $key) {
            if (isset($this->piVars[$key])) {
                $this->piVars[$key] = (int)$this->piVars[$key];
            } else {
                $this->piVars[$key] = 0;
            }
        }
    }

    /**
     * Ensures that all values in the given array are cast to integers and removes empty or invalid values.
     *
     * @param array<array-key, string> $keys the keys of the piVars to check, may be empty
     *
     * @deprecated will be remove in oelib 5.0
     */
    protected function ensureIntegerArrayValues(array $keys): void
    {
        if (empty($keys)) {
            return;
        }

        foreach ($keys as $key) {
            if (!isset($this->piVars[$key]) || !is_array($this->piVars[$key])) {
                continue;
            }

            foreach ($this->piVars[$key] as $innerKey => $value) {
                $integerValue = (int)$value;

                if ($integerValue === 0) {
                    unset($this->piVars[$key][$innerKey]);
                } else {
                    $this->piVars[$key][$innerKey] = $integerValue;
                }
            }
        }
    }

    /**
     * Extracts a value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return string the contents of that field within listView., may be empty
     */
    private function getListViewConfigurationValue(string $fieldName): string
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($fieldName === '') {
            throw new \InvalidArgumentException('$fieldName must not be empty.', 1331489528);
        }

        return isset($this->conf['listView.'][$fieldName]) ? (string)$this->conf['listView.'][$fieldName] : '';
    }

    /**
     * Returns a string value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return string the trimmed contents of that field within listView.
     *                or an empty string if the value was not set
     */
    public function getListViewConfValueString(string $fieldName): string
    {
        return trim($this->getListViewConfigurationValue($fieldName));
    }

    /**
     * Returns an integer value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return int the integer value of that field within listView, or zero if the value was not set
     */
    public function getListViewConfValueInteger(string $fieldName): int
    {
        return (int)$this->getListViewConfigurationValue($fieldName);
    }

    /**
     * Returns a boolean value within listView.
     *
     * @param non-empty-string $fieldName TypoScript setup field name to extract (within listView.)
     *
     * @return bool the boolean value of that field within listView., FALSE if no value was set
     */
    public function getListViewConfValueBoolean(string $fieldName): bool
    {
        return (bool)$this->getListViewConfigurationValue($fieldName);
    }
}
