<?php
declare(strict_types = 1);

use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * This utility class provides some commonly-used functions for handling
 * templates (in addition to all functionality provided by the base classes).
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_TemplateHelper extends \Tx_Oelib_SalutationSwitcher
{
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
     * @var bool whether init() already has been called (in order to
     *              avoid double calls)
     */
    protected $isInitialized = false;

    /**
     * @var \Tx_Oelib_ConfigCheck
     */
    protected $configurationCheck = null;

    /**
     * @var string the file name of the template set via TypoScript or FlexForms
     */
    private $templateFileName = '';

    /**
     * @var \Tx_Oelib_Template this object's (only) template
     */
    private $template = null;

    /**
     * TS Setup for plugin.tx_extensionkey, using the current page UID as key
     *
     * @var array[]
     */
    private static $cachedConfigurations = [];

    /**
     * Initializes the FE plugin stuff and reads the configuration.
     *
     * It is harmless if this function gets called multiple times as it
     * recognizes this and ignores all calls but the first one.
     *
     * This is merely a convenience function.
     *
     * If the parameter is omitted, the configuration for plugin.tx_[extkey] is
     * used instead, e.g. plugin.tx_seminars.
     *
     * @param array|null $configuration TypoScript configuration for the plugin, set to null to load the configuration
     *     from a BE page
     *
     * @return void
     */
    public function init($configuration = null)
    {
        if ($this->isInitialized) {
            return;
        }

        // Calls the base class's constructor manually as this isn't done automatically.
        parent::__construct();

        $this->initializeConfiguration($configuration);
        $this->ensureContentObject();

        if ($this->extKey !== '') {
            $this->pi_setPiVarDefaults();
            $this->pi_loadLL();
            $this->initializeConfigurationCheck();
        }

        $this->isInitialized = true;
    }

    /**
     * @param array|null $configuration
     *
     * @return void
     */
    protected function initializeConfiguration(array $configuration = null)
    {
        if ($configuration !== null) {
            $this->conf = $configuration;
            return;
        }

        $frontEnd = $this->getFrontEndController();
        if ($frontEnd !== null && !isset($frontEnd->config['config'])) {
            $frontEnd->config['config'] = [];
        }

        $pageId = $this->getCurrentBePageId();
        if (isset(self::$cachedConfigurations[$pageId])) {
            $this->conf = self::$cachedConfigurations[$pageId];
        } else {
            // We need to create our own template setup if we are in the
            // BE and we aren't currently creating a DirectMail page.
            if (TYPO3_MODE === 'BE' && $frontEnd === null) {
                $this->conf = $this->retrievePageConfig($pageId);
            } else {
                // On the front end, we can use the provided template setup.
                $this->conf = ($this->extKey !== '')
                    ? $frontEnd->tmpl->setup['plugin.']['tx_' . $this->extKey . '.'] : [];
            }

            self::$cachedConfigurations[$pageId] = $this->conf;
        }
    }

    /**
     * @return void
     */
    protected function initializeConfigurationCheck()
    {
        if (!$this->isConfigurationCheckEnabled()) {
            return;
        }

        $className = $this->getConfigurationCheckClassName();
        if ($className !== '') {
            $this->configurationCheck = GeneralUtility::makeInstance($className, $this);
        }
    }

    /**
     * @return bool
     */
    protected function isConfigurationCheckEnabled(): bool
    {
        if ($this->extKey === '') {
            return false;
        }

        return \Tx_Oelib_ConfigurationProxy::getInstance($this->extKey)->getAsBoolean('enableConfigCheck');
    }

    /**
     * This class is intended to be overwritten in subclasses.
     *
     * @return string might be empty
     */
    protected function getConfigurationCheckClassName()
    {
        return $this->getDefaultConfigurationCheckClassName();
    }

    /**
     * @return string might be empty
     */
    protected function getDefaultConfigurationCheckClassName(): string
    {
        $camelCaseClassName = 'Tx_' . ucfirst($this->extKey) . '_ConfigCheck';
        $lowercaseClassName = \strtolower($camelCaseClassName);
        if (\class_exists($camelCaseClassName)) {
            $className = $camelCaseClassName;
        } elseif (\class_exists($lowercaseClassName)) {
            $className = $lowercaseClassName;
        } else {
            $className = '';
        }

        return $className;
    }

    /**
     * Ensures that $this->cObj points to a valid content object.
     *
     * If this object already has a valid cObj, this function does nothing.
     *
     * If there is a front end and this object does not have a cObj yet, the cObj from the front end is used.
     *
     * If this object has no cObj and there is no front end, this function will do nothing.
     *
     * @return void
     */
    protected function ensureContentObject()
    {
        if ($this->cObj !== null) {
            return;
        }

        $frontEnd = $this->getFrontEndController();
        // TSFE->cObj will be an empty string if not initialized, not NULL.
        if (is_object($frontEnd->cObj)) {
            $this->cObj = $frontEnd->cObj;
        }
    }

    /**
     * Checks that this object is properly initialized.
     *
     * @return bool TRUE if this object is properly initialized, FALSE otherwise
     */
    public function isInitialized()
    {
        return $this->isInitialized;
    }

    /**
     * Retrieves the configuration (TS setup) of the page with the PID provided
     * as the parameter $pageId.
     *
     * Only the configuration for the current extension key will be retrieved.
     * For example, if the extension key is "foo", the TS setup for plugin.
     * tx_foo will be retrieved.
     *
     * @param int $pageId UID of the page for which the configuration should be retrieved, must be > 0
     *
     * @return array configuration array of the requested page for the
     *               current extension key
     */
    protected function retrievePageConfig(int $pageId)
    {
        /** @var TemplateService $template */
        $template = GeneralUtility::makeInstance(TemplateService::class);
        // Disables the logging of time-performance information.
        $template->tt_track = 0;
        $template->init();

        /** @var PageRepository $page */
        $page = GeneralUtility::makeInstance(PageRepository::class);

        // Gets the root line.
        // Finds the selected page in the BE exactly as in BaseScriptClass::init().
        $rootLine = $page->getRootLine($pageId);

        // Generates the constants/config and hierarchy info for the template.
        $template->runThroughTemplates($rootLine);
        $template->generateConfig();

        return $template->setup['plugin.']['tx_' . $this->extKey . '.'] ?? [];
    }

    /**
     * Gets a value from flexforms or TS setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TS setup is returned. If there is no field with that name in TS setup,
     * an empty string is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $isFileName whether this is a filename, which has to be combined with a path
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return string the value of the corresponding flexforms or TS setup
     *                entry (may be empty)
     */
    private function getConfValue(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $isFileName = false,
        bool $ignoreFlexform = false
    ): string {
        $flexformsValue = '';
        if (!$ignoreFlexform) {
            $flexformsValue = $this->pi_getFFvalue(
                $this->cObj->data['pi_flexform'],
                $fieldName,
                $sheet
            );
        }

        if ($isFileName && $flexformsValue !== null && $flexformsValue !== '') {
            $flexformsValue = $this->addPathToFileName($flexformsValue);
        }
        $confValue = (string)($this->conf[$fieldName] ?? '');

        return $flexformsValue ?: $confValue;
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
     * @param string $path
     *        the path to the file (without filename), must contain a slash at the end,
     *        may contain a slash at the beginning (if not relative)
     *
     * @return string the complete path including file name
     */
    private function addPathToFileName(string $fileName, string $path = ''): string
    {
        if (empty($path)) {
            $path = 'uploads/tx_' . $this->extKey . '/';
        }

        return $path . $fileName;
    }

    /**
     * Gets a trimmed string value from flexforms or TS setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TS setup is returned. If there is no field with that name in TS
     * setup, an empty string is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $isFileName whether this is a filename, which has to be combined with a path
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return string the trimmed value of the corresponding flexforms or
     *                TS setup entry (may be empty)
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
     * Checks whether a string value from flexforms or TS setup is set.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TS setup is checked. If there is no field with that name in TS
     * setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     * @param bool $ignoreFlexform
     *        whether to ignore the flexform values and just get the settings from TypoScript, may be empty
     *
     * @return bool whether there is a non-empty value in the
     *                 corresponding flexforms or TS setup entry
     */
    public function hasConfValueString(
        string $fieldName,
        string $sheet = 'sDEF',
        bool $ignoreFlexform = false
    ): bool {
        return $this->getConfValueString(
            $fieldName,
            $sheet,
            false,
            $ignoreFlexform
        ) !== '';
    }

    /**
     * Gets an integer value from flexforms or TS setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TS setup is returned. If there is no field with that name in TS
     * setup, zero is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return int the int value of the corresponding flexforms or
     *                 TS setup entry
     */
    public function getConfValueInteger(string $fieldName, string $sheet = 'sDEF'): int
    {
        return (int)$this->getConfValue($fieldName, $sheet);
    }

    /**
     * Checks whether an integer value from flexforms or TS setup is set and
     * non-zero. The priority lies on flexforms; if nothing is found there, the
     * value from TS setup is checked. If there is no field with that name in
     * TS setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return bool whether there is a non-zero value in the
     *                 corresponding flexforms or TS setup entry
     */
    public function hasConfValueInteger(string $fieldName, string $sheet = 'sDEF'): bool
    {
        return (bool)$this->getConfValueInteger($fieldName, $sheet);
    }

    /**
     * Gets a boolean value from flexforms or TS setup.
     * The priority lies on flexforms; if nothing is found there, the value
     * from TS setup is returned. If there is no field with that name in TS
     * setup, FALSE is returned.
     *
     * @param string $fieldName field name to extract
     * @param string $sheet sheet pointer, eg. "sDEF"
     *
     * @return bool the boolean value of the corresponding flexforms or
     *                 TS setup entry
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
     * @param string $key key of the configuration property to set, must not be empty
     * @param mixed $value value of the configuration property, may be empty or zero
     *
     * @return void
     */
    public function setConfigurationValue(string $key, $value)
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty', 1331489491);
        }

        $this->ensureConfigurationArray();
        $this->conf[$key] = $value;
    }

    /**
     * Sets a cached configuration value that will be used when a new instance
     * is created.
     *
     * This function is intended to be used for testing purposes only.
     *
     * @param string $key
     *        key of the configuration property to set, must not be empty
     * @param mixed $value
     *        value of the configuration property, may be empty or zero
     *
     * @return void
     */
    public static function setCachedConfigurationValue(string $key, $value)
    {
        $pageUid = \Tx_Oelib_PageFinder::getInstance()->getPageUid();

        if (!isset(self::$cachedConfigurations[$pageUid])) {
            self::$cachedConfigurations[$pageUid] = [];
        }

        self::$cachedConfigurations[$pageUid][$key] = $value;
    }

    /**
     * Purges all cached configuration values.
     *
     * This function is intended to be used for testing purposes only.
     *
     * @return void
     */
    public static function purgeCachedConfigurations()
    {
        self::$cachedConfigurations = [];
    }

    /**
     * Gets the configuration.
     *
     * @return array configuration array, might be empty
     */
    public function getConfiguration(): array
    {
        $this->ensureConfigurationArray();
        return $this->conf;
    }

    /**
     * Ensures that $this->conf is set and that it is an array.
     *
     * @return void
     */
    private function ensureConfigurationArray()
    {
        if (!is_array($this->conf)) {
            $this->conf = [];
        }
    }

    /**
     * Retrieves the plugin template file set in $this->conf['templateFile'] (or
     * also via flexforms if TYPO3 mode is FE) and writes it to
     * $this->templateCode. The subparts will be written to $this->templateCache.
     *
     * @param bool $ignoreFlexform whether the settings in the Flexform should be ignored
     *
     * @return void
     */
    public function getTemplateCode(bool $ignoreFlexform = false)
    {
        // Trying to fetch the template code via $this->cObj in BE mode leads to
        // a non-catchable error in the tslib_content class because the cObj
        // configuration array is not initialized properly.
        // As flexforms can be used in FE mode only, $ignoreFlexform is set TRUE
        // if we are in the BE mode. By this, $this->cObj->fileResource can be
        // sheltered from being called.
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
            $templateFileName = $this->getFrontEndController()->tmpl->getFileName(
                $templateFileName
            );
        }

        $this->templateFileName = $templateFileName;
    }

    /**
     * Returns the template object from the template registry for the file name
     * in $this->templateFileName.
     *
     * @return \Tx_Oelib_Template the template object for the template file name
     *                           in $this->templateFileName
     */
    protected function getTemplate(): \Tx_Oelib_Template
    {
        if ($this->template === null) {
            $this->template = \Tx_Oelib_TemplateRegistry::get(
                $this->templateFileName
            );
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
     *
     * @return void
     */
    public function processTemplate(string $templateCode)
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
     * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must
     *     not be empty
     * @param mixed $content the marker's content, may be empty
     * @param string $prefix prefix to the marker name (may be empty, case-insensitive, will get uppercased)
     *
     * @return void
     */
    public function setMarker(string $markerName, $content, string $prefix = '')
    {
        $this->getTemplate()->setMarker($markerName, $content, $prefix);
    }

    /**
     * Gets a marker's content.
     *
     * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must
     *     not be empty
     *
     * @return string the marker's content or an empty string if the
     *                marker has not been set before
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
     * @param string $subpartName
     *        the subpart's name without the ### signs, case-insensitive, will get uppercased, must not be empty
     * @param mixed $content the subpart's content, may be empty
     * @param string $prefix prefix to the subpart name (may be empty, case-insensitive, will get uppercased)
     *
     * @return void
     */
    public function setSubpart(string $subpartName, $content, string $prefix = '')
    {
        try {
            $this->getTemplate()->setSubpart($subpartName, $content, $prefix);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            $this->setErrorMessage(
                'The subpart <strong>' . $subpartName .
                '</strong> is missing in the HTML template file <strong>' .
                $this->getConfValueString(
                    'templateFile',
                    's_template_special',
                    true
                ) .
                '</strong>. If you are using a modified HTML template, please ' .
                'fix it. If you are using the original HTML template file, ' .
                'please file a bug report in the ' .
                '<a href="https://github.com/oliverklee/ext-oelib/issues">issue tracker</a>.'
            );
        }
    }

    /**
     * Sets a marker based on whether the int content is non-zero.
     *
     * If (int)$content is non-zero, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must
     *     not be empty
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotEmpty
     */
    public function setMarkerIfNotZero(string $markerName, $content, string $markerPrefix = ''): bool
    {
        return $this->getTemplate()->setMarkerIfNotZero(
            $markerName,
            $content,
            $markerPrefix
        );
    }

    /**
     * Sets a marker based on whether the (string) content is non-empty.
     * If $content is non-empty, this function sets the marker's content,
     * working exactly like setMarker($markerName, $content, $markerPrefix).
     *
     * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must
     *     not be empty
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get
     *     uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE otherwise
     *
     * @see setMarkerIfNotZero
     */
    public function setMarkerIfNotEmpty(string $markerName, $content, string $markerPrefix = ''): bool
    {
        return $this->getTemplate()->setMarkerIfNotEmpty(
            $markerName,
            $content,
            $markerPrefix
        );
    }

    /**
     * Checks whether a subpart is visible.
     *
     * Note: If the subpart to check does not exist, this function will return
     * FALSE.
     *
     * @param string $subpartName name of the subpart to check (without the ###), must not be empty
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
     * @param string $subparts comma-separated list of at least 1 subpart name to hide (case-insensitive, will get
     *     uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     *
     * @return void
     */
    public function hideSubparts(string $subparts, string $prefix = '')
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
     * @param string[] $subparts subpart names to hide (may be empty, case-insensitive, will get uppercased)
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     *
     * @return void
     */
    public function hideSubpartsArray(array $subparts, string $prefix = '')
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
     * @param string $subparts
     *        comma-separated list of at least 1 subpart name to unhide (case-insensitive, will get uppercased), must
     *     not be empty
     * @param string $permanentlyHiddenSubparts
     *        comma-separated list of subpart names that shouldn't get unhidden
     * @param string $prefix
     *        prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     *
     * @return void
     */
    public function unhideSubparts(
        string $subparts,
        string $permanentlyHiddenSubparts = '',
        string $prefix = ''
    ) {
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
     * @param string[] $subparts subpart names to unhide (may be empty, case-insensitive, will get uppercased)
     * @param string[] $permanentlyHiddenSubparts subpart names that shouldn't get unhidden
     * @param string $prefix prefix to the subpart names (may be empty, case-insensitive, will get uppercased)
     *
     * @return void
     */
    public function unhideSubpartsArray(
        array $subparts,
        array $permanentlyHiddenSubparts = [],
        string $prefix = ''
    ) {
        $this->getTemplate()->unhideSubpartsArray(
            $subparts,
            $permanentlyHiddenSubparts,
            $prefix
        );
    }

    /**
     * Sets or hides a marker based on $condition.
     * If $condition is TRUE, this function sets the marker's content, working
     * exactly like setMarker($markerName, $content, $markerPrefix).
     * If $condition is FALSE, this function removes the wrapping subpart,
     * working exactly like hideSubparts($markerName, $wrapperPrefix).
     *
     * @param string $markerName
     *        the marker's name without the ### signs, case-insensitive, will get uppercased, must not be empty
     * @param bool $condition
     *        if this is TRUE, the marker will be filled, otherwise the wrapped marker will be hidden
     * @param mixed $content
     *        content with which the marker will be filled, may be empty
     * @param string $markerPrefix
     *        prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix
     *        prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if
     *                 the subpart has been hidden
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
     * @param string $markerName
     *        the marker's name without the ### signs, case-insensitive, will get uppercased, must not be* empty
     * @param mixed $content
     *        content with which the marker will be filled, may be empty
     * @param string $markerPrefix
     *        prefix to the marker name for setting (may be empty, case-insensitive, will get uppercased)
     * @param string $wrapperPrefix
     *        prefix to the subpart name for hiding (may be empty, case-insensitive, will get uppercased)
     *
     * @return bool TRUE if the marker content has been set, FALSE if
     *                 the subpart has been hidden
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
     * @param string $markerName the marker's name without the ### signs, case-insensitive, will get uppercased, must
     *     not be empty
     * @param mixed $content content with which the marker will be filled, may be empty
     * @param string $markerPrefix prefix to the marker name for setting (may be empty, case-insensitive, will get
     *     uppercased)
     * @param string $wrapperPrefix prefix to the subpart name for hiding (may be empty, case-insensitive, will get
     *     uppercased)
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
    public function getSubpart(string $key = '')
    {
        try {
            return $this->getTemplate()->getSubpart($key);
        } catch (\Tx_Oelib_Exception_NotFound $exception) {
            $this->setErrorMessage(
                'The subpart <strong>' . $key .
                '</strong> is missing in the HTML template file <strong>' .
                $this->getConfValueString(
                    'templateFile',
                    's_template_special',
                    true
                ) .
                '</strong>. If you are using a modified HTML template, please ' .
                'fix it. If you are using the original HTML template file, ' .
                'please file a bug report in the ' .
                '<a href="https://github.com/oliverklee/ext-oelib/issues">issue tracker</a>.'
            );

            return '';
        }
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
        return preg_replace_callback(
            \Tx_Oelib_Template::LABEL_PATTERN,
            static function (array $matches) use ($translator) {
                return $translator->translate(strtolower($matches[1]));
            },
            $renderedSubpart
        );
    }

    /**
     * Writes all localized labels for the current template into their
     * corresponding template markers.
     *
     * For this, the label markers in the template must be prefixed with
     * "LABEL_" (e.g. "###LABEL_FOO###"), and the corresponding localization
     * entry must have the same key, but lowercased and without the ###
     * (e.g. "label_foo").
     *
     * @return void
     */
    public function setLabels()
    {
        $template = $this->getTemplate();
        foreach ($template->getLabelMarkerNames() as $label) {
            $template->setMarker($label, $this->translate($label));
        }
    }

    /**
     * Includes a link to the JavaScript file configured as "jsFile" and adds it
     * to the automatic page header with $this->prefixId.'_js' as the array key.
     *
     * If no file is specified, no link is created.
     *
     * This function may only be called if $this->$prefixId has been set.
     *
     * @deprecated will be removed in oelib 4.0
     *
     * @return void
     */
    public function addJavaScriptToPageHeader()
    {
        if ($this->hasConfValueString('jsFile', 's_template_special')) {
            $this->getFrontEndController()->additionalHeaderData[$this->prefixId . '_js']
                = '<script type="text/javascript" src="'
                . $this->getConfValueString(
                    'jsFile',
                    's_template_special',
                    true
                ) . '"></script>';
        }
    }

    /**
     * Resets the list of subparts to hide.
     *
     * @return void
     */
    public function resetSubpartsHiding()
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
     * @param string[] $additionalPiVars
     *        keys for $this->piVars that will be ensured to exist as ints in $this->piVars as well, may be empty
     *
     * @return void
     */
    protected function ensureIntegerPiVars(array $additionalPiVars = [])
    {
        if (!is_array($this->piVars)) {
            $this->piVars = [];
        }

        foreach (
            array_merge(
                ['showUid', 'pointer', 'mode'],
                $additionalPiVars
            ) as $key) {
            if (isset($this->piVars[$key])) {
                $this->piVars[$key] = (int)$this->piVars[$key];
            } else {
                $this->piVars[$key] = 0;
            }
        }
    }

    /**
     * Ensures that all values in the given array are cast to ints and removes empty
     * or invalid values.
     *
     * @param string[] $keys the keys of the piVars to check, may be empty
     *
     * @return void
     */
    protected function ensureIntegerArrayValues(array $keys)
    {
        if (empty($keys)) {
            return;
        }

        foreach ($keys as $key) {
            if (!isset($this->piVars[$key])
                || !is_array($this->piVars[$key])
            ) {
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
     * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
     *
     * @return string the contents of that field within listView., may be empty
     */
    private function getListViewConfigurationValue(string $fieldName): string
    {
        if (empty($fieldName)) {
            throw new \InvalidArgumentException('$fieldName must not be empty.', 1331489528);
        }

        return isset($this->conf['listView.'][$fieldName]) ? (string)$this->conf['listView.'][$fieldName] : '';
    }

    /**
     * Returns a string value within listView.
     *
     * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
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
     * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
     *
     * @return int the integer value of that field within listView. or
     *                 zero if the value was not set
     */
    public function getListViewConfValueInteger(string $fieldName): int
    {
        return (int)$this->getListViewConfigurationValue($fieldName);
    }

    /**
     * Returns a boolean value within listView.
     *
     * @param string $fieldName TS setup field name to extract (within listView.), must not be empty
     *
     * @return bool the boolean value of that field within listView.,
     *                 FALSE if no value was set
     */
    public function getListViewConfValueBoolean(string $fieldName): bool
    {
        return (bool)$this->getListViewConfigurationValue($fieldName);
    }

    /**
     * Sets the "flavor" of the object to check.
     *
     * @deprecated Will be removed in oelib 4.0. Use custom config check classes instead.
     *
     * @param string $flavor a short string identifying the "flavor" of the object to check (may be empty)
     *
     * @return void
     */
    public function setFlavor(string $flavor)
    {
        if ($this->configurationCheck) {
            $this->configurationCheck->setFlavor($flavor);
        }
    }

    /**
     * Returns the current flavor of the object to check.
     *
     * @return string the current flavor of the object to check (or an empty
     *                string if no flavor is set)
     */
    public function getFlavor(): string
    {
        $result = '';

        if ($this->configurationCheck) {
            $result = $this->configurationCheck->getFlavor();
        }

        return $result;
    }

    /**
     * Sets the error text of $this->configurationCheck.
     *
     * If this->configurationCheck is NULL, this function is a no-op.
     *
     * @param string $message error text to set (may be empty)
     *
     * @return void
     */
    protected function setErrorMessage(string $message)
    {
        if ($this->configurationCheck) {
            $this->configurationCheck->setErrorMessage($message);
        }
    }

    /**
     * Checks this object's configuration and returns a formatted error message
     * (if any). If there are several objects of this class, still only one
     * error message is created (in order to prevent duplicate messages).
     *
     * @param bool $useRawMessage whether to use the raw message instead of the wrapped message
     * @param string $temporaryFlavor flavor to use temporarily for this call (leave empty to not change the flavor)
     *
     * @return string a formatted error message (if there are errors) or an
     *                empty string
     */
    public function checkConfiguration(bool $useRawMessage = false, string $temporaryFlavor = ''): string
    {
        static $hasDisplayedMessage = false;
        $result = '';

        if ($this->configurationCheck !== null) {
            if (!empty($temporaryFlavor)) {
                $oldFlavor = $this->getFlavor();
                $this->setFlavor($temporaryFlavor);
            } else {
                $oldFlavor = '';
            }

            $message = $useRawMessage
                ? $this->configurationCheck->checkIt()
                : $this->configurationCheck->checkItAndWrapIt();

            if (!empty($temporaryFlavor)) {
                $this->setFlavor($oldFlavor);
            }

            // If we have a message, only returns it if it is the first message
            // for objects of this class.
            if (!empty($message) && !$hasDisplayedMessage) {
                $result = $message;
                $hasDisplayedMessage = true;
            }
        }

        return $result;
    }

    /**
     * Returns an empty string if there are no configuration errors.
     * Otherwise, returns the wrapped error text.
     *
     * Use this method if you want to display this message pretty
     * directly and it doesn't need to get handled to other configcheck
     * objects.
     *
     * @return string the wrapped error text (or an empty string if there are no
     *                errors)
     */
    public function getWrappedConfigCheckMessage(): string
    {
        $result = '';

        if ($this->configurationCheck) {
            $result = $this->configurationCheck->getWrappedMessage();
        }

        return $result;
    }

    /**
     * Gets the UID of the currently selected back-end page.
     *
     * @return int the current back-end page UID (or 0 if there is an error)
     */
    public function getCurrentBePageId()
    {
        return \Tx_Oelib_PageFinder::getInstance()->getPageUid();
    }
}
