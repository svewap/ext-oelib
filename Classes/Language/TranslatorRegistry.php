<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Language;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use TYPO3\CMS\Core\Localization\LocalizationFactory;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class provides a registry for translators.
 *
 * @deprecated will be removed in oelib 4.0
 */
class TranslatorRegistry
{
    /**
     * @var TranslatorRegistry the Singleton instance
     */
    private static $instance = null;

    /**
     * extension name => Translator entries
     *
     * @var array<string, Translator>
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
        $this->setLanguageKeyFromConfiguration(ConfigurationRegistry::get('config'));
        $this->setLanguageKeyFromConfiguration(ConfigurationRegistry::get('page.config'));
    }

    /**
     * Reads the language key from a configuration and sets it as current language.
     * Also sets the alternate language if one is configured.
     *
     * The language key is read from the "language" key and the alternate language is read
     * from the language_alt key.
     *
     * @param TypoScriptConfiguration $configuration the configuration to read
     *
     * @return void
     */
    private function setLanguageKeyFromConfiguration(TypoScriptConfiguration $configuration)
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
        $this->languageKey = BackEndLoginManager::getInstance()->getLoggedInUser()->getLanguage();
    }

    /**
     * Returns the instance of this class.
     *
     * @return TranslatorRegistry the current Singleton instance
     */
    public static function getInstance(): TranslatorRegistry
    {
        if (self::$instance === null) {
            self::$instance = new TranslatorRegistry();
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
     * @return Translator the Translator for the specified extension
     *
     * @see getByExtensionName()
     */
    public static function get(string $extensionName): Translator
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
     * @return Translator the Translator for the specified extension name
     */
    private function getByExtensionName(string $extensionName): Translator
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
            // Overrides the localized labels with labels from TypoScript only in the front end.

            if (
                isset($localizedLabels[$this->languageKey])
                && \is_array($localizedLabels[$this->languageKey])
                && $this->getFrontEndController() !== null
            ) {
                foreach ($this->getLocalizedLabelsFromTypoScript($extensionName) as $labelKey => $labelFromTypoScript) {
                    $localizedLabels[$this->languageKey][$labelKey][0]['target'] = $labelFromTypoScript;
                }
            }

            /** @var Translator $translator */
            $translator = GeneralUtility::makeInstance(
                Translator::class,
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
    private function getLocalizedLabelsFromFile(string $extensionKey): array
    {
        if ($extensionKey === '') {
            throw new \InvalidArgumentException('$extensionKey must not be empty.', 1331489618);
        }

        /** @var LocalizationFactory $languageFactory */
        $languageFactory = GeneralUtility::makeInstance(LocalizationFactory::class);
        $languageFile = 'EXT:' . $extensionKey . '/' . self::LANGUAGE_FILE_PATH;
        $localizedLabels = $languageFactory->getParsedData($languageFile, $this->languageKey);

        if ($this->alternativeLanguageKey !== '') {
            $alternativeLabels = $languageFactory->getParsedData($languageFile, $this->languageKey);
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
     * @return array<string, string> the localized labels from the extension's TypoScript setup,
     *         will be empty if there are none
     */
    private function getLocalizedLabelsFromTypoScript(string $extensionName): array
    {
        if ($extensionName === '') {
            throw new \InvalidArgumentException('The parameter $extensionName must not be empty.', 1331489630);
        }

        $result = [];
        $namespace = 'plugin.tx_' . $extensionName . '._LOCAL_LANG.' . $this->languageKey;

        $configuration = ConfigurationRegistry::get($namespace);
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
    public function setLanguageKey(string $languageKey)
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
    public function getLanguageKey(): string
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
        return $GLOBALS['TSFE'] ?? null;
    }

    /**
     * Returns $GLOBALS['LANG'].
     *
     * @return LanguageService|null
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
