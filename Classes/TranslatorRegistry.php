<?php

use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class provides a registry for translators.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Benjamin Schulte <benj@minschulte.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_TranslatorRegistry
{
    /**
     * @var \Tx_Oelib_TranslatorRegistry the Singleton instance
     */
    private static $instance = null;

    /**
     * extension name => Translator entries
     *
     * @var \Tx_Oelib_Translator[]
     */
    private $translators = [];

    /**
     * @var string the key of the language to load the translations for
     */
    private $languageKey = 'default';

    /**
     * @var string the key of the alternative language to load the translations for
     */
    private $alternativeLanguageKey = '';

    /**
     * @var string the path to the locallang.xlf file, relative to an extension's root directory
     */
    const LANGUAGE_FILE_PATH = 'Resources/Private/Language/locallang.xlf';

    /**
     * The constructor.
     */
    private function __construct()
    {
        if ($this->getFrontEndController() !== null) {
            $this->initializeFrontEnd();
        } elseif ($this->getLanguageService() !== null) {
            $this->initializeBackEnd();
        } else {
            throw new \BadMethodCallException('There was neither a front end nor a back end detected.', 1331489564);
        }
    }

    /**
     * Initializes the TranslatorRegistry for the front end.
     *
     * @return void
     */
    private function initializeFrontEnd()
    {
        $this->setLanguageKeyFromConfiguration(\Tx_Oelib_ConfigurationRegistry::get('config'));
        $this->setLanguageKeyFromConfiguration(\Tx_Oelib_ConfigurationRegistry::get('page.config'));
    }

    /**
     * Reads the language key from a configuration and sets it as current language.
     * Also sets the alternate language if one is configured.
     *
     * The language key is read from the "language" key and the alternate language is read
     * from the language_alt key.
     *
     * @param \Tx_Oelib_Configuration $configuration the configuration to read
     *
     * @return void
     */
    private function setLanguageKeyFromConfiguration(\Tx_Oelib_Configuration $configuration)
    {
        if (!$configuration->hasString('language')) {
            return;
        }

        $this->languageKey = $configuration->getAsString('language');
        if ($configuration->hasString('language_alt')) {
            $this->alternativeLanguageKey = $configuration->getAsString('language_alt');
        }
    }

    /**
     * Initializes the TranslatorRegistry for the back end.
     *
     * @return void
     */
    private function initializeBackEnd()
    {
        $backEndUser =
            \Tx_Oelib_BackEndLoginManager::getInstance()->getLoggedInUser(\Tx_Oelib_Mapper_BackEndUser::class);
        $this->languageKey = $backEndUser->getLanguage();
    }

    /**
     * Returns the instance of this class.
     *
     * @return \Tx_Oelib_TranslatorRegistry the current Singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new \Tx_Oelib_TranslatorRegistry();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     *
     * @return void
     */
    public static function purgeInstance()
    {
        self::$instance = null;
    }

    /**
     * Gets a Translator by its extension name.
     *
     * This is a wrapper for self::getInstance()->getByExtensionName().
     *
     * @param string $extensionName
     *        the extension name to get the Translator for, must not be empty, the corresponding extension must be
     *     loaded
     *
     * @return \Tx_Oelib_Translator the Translator for the specified extension
     *
     * @see getByExtensionName()
     */
    public static function get($extensionName)
    {
        return self::getInstance()->getByExtensionName($extensionName);
    }

    /**
     * Gets a Translator by its extension name.
     *
     * @param string $extensionName
     *        the extension name to get the Translator for, must not be empty, the corresponding extension must be
     *     loaded
     *
     * @return \Tx_Oelib_Translator the Translator for the specified extension
     *                             name
     */
    private function getByExtensionName($extensionName)
    {
        if ($extensionName === '') {
            throw new \InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489578);
        }

        if (!ExtensionManagementUtility::isLoaded($extensionName)) {
            throw new \BadMethodCallException(
                'The extension with the name "' . $extensionName . '" is not loaded.',
                1331489598
            );
        }

        if (!isset($this->translators[$extensionName])) {
            $localizedLabels = $this->getLocalizedLabelsFromFile($extensionName);
            // Overrides the localized labels with labels from TypoScript only
            // in the front end.

            if (($this->getFrontEndController() !== null)
                && isset($localizedLabels[$this->languageKey])
                && is_array($localizedLabels[$this->languageKey])
            ) {
                $labelsFromTyposcript = $this->getLocalizedLabelsFromTypoScript($extensionName);

                foreach ($labelsFromTyposcript as $labelKey => $labelFromTyposcript) {
                    $localizedLabels[$this->languageKey][$labelKey][0]['target'] = $labelFromTyposcript;
                }
            }

            /** @var \Tx_Oelib_Translator $translator */
            $translator = GeneralUtility::makeInstance(
                \Tx_Oelib_Translator::class,
                $this->languageKey,
                $this->alternativeLanguageKey,
                $localizedLabels
            );
            $this->translators[$extensionName] = $translator;
        }

        return $this->translators[$extensionName];
    }

    /**
     * Returns the localized labels from an extension's language file.
     *
     * @param string $extensionKey
     *        key of the extension to get the localized labels from,
     *        must not be empty, and the corresponding extension must be loaded
     *
     * @return string[] the localized labels from an extension's language file, will be empty if there are none
     */
    private function getLocalizedLabelsFromFile($extensionKey)
    {
        if ($extensionKey === '') {
            throw new \InvalidArgumentException('$extensionKey must not be empty.', 1331489618);
        }

        /** @var LocalizationFactory $languageFactory */
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);
        $languageFile = 'EXT:' . $extensionKey . '/' . self::LANGUAGE_FILE_PATH;
        $localizedLabels = $languageFactory->getParsedData($languageFile, $this->languageKey, 'utf-8', 0);

        if ($this->alternativeLanguageKey !== '') {
            $alternativeLabels = $languageFactory->getParsedData($languageFile, $this->languageKey, 'utf-8', 0);
            $localizedLabels = array_merge(
                $alternativeLabels,
                is_array($localizedLabels) ? $localizedLabels : []
            );
        }

        return $localizedLabels;
    }

    /**
     * Returns the localized labels from an extension's TypoScript setup.
     *
     * Returns only the labels set for the language stored in $this->languageKey
     *
     * @param string $extensionName
     *        the extension name to get the localized labels from TypoScript setup for,
     *        must not be empty, the corresponding extension must be loaded
     *
     * @return string[] the localized labels from the extension's TypoScript setup, will be empty if there are none
     */
    private function getLocalizedLabelsFromTypoScript($extensionName)
    {
        if ($extensionName === '') {
            throw new \InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489630);
        }

        $result = [];
        $namespace = 'plugin.tx_' . $extensionName . '._LOCAL_LANG.' . $this->languageKey;

        $configuration = \Tx_Oelib_ConfigurationRegistry::get($namespace);
        foreach ($configuration->getArrayKeys() as $key) {
            $result[$key] = $configuration->getAsString($key);
        }

        return $result;
    }

    /**
     * Sets the language for the translator.
     *
     * @param string $languageKey the language key to set for the translator,
     *        must not be empty
     *
     * @return void
     */
    public function setLanguageKey($languageKey)
    {
        if ($languageKey === '') {
            throw new \InvalidArgumentException('The given language key must not be empty.', 1331489643);
        }

        $this->languageKey = $languageKey;
    }

    /**
     * Returns the language key set for the translator.
     *
     * @return string the language key of the translator, will not be
     *         empty
     */
    public function getLanguageKey()
    {
        return $this->languageKey;
    }

    /**
     * Returns $GLOBALS['TSFE'].
     *
     * @return TypoScriptFrontendController|null
     */
    protected function getFrontEndController()
    {
        return isset($GLOBALS['TSFE']) ? $GLOBALS['TSFE'] : null;
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService|null
     */
    protected function getLanguageService()
    {
        return isset($GLOBALS['LANG']) ? $GLOBALS['LANG'] : null;
    }
}
