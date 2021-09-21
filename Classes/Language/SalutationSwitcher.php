<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Language;

use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Plugin\AbstractPlugin;

/**
 * This class provides functions for localization.
 */
abstract class SalutationSwitcher extends AbstractPlugin
{
    /**
     * A list of language keys for which the localizations have been loaded
     * (or NULL if the list has not been compiled yet).
     *
     * @var array<int, string>|null
     */
    private $availableLanguages = null;

    /**
     * An ordered list of language label suffixes that should be tried to get
     * localizations in the preferred order of formality (or NULL if the list
     * has not been compiled yet).
     *
     * @var array<int, string>|null
     */
    private $suffixesToTry = null;

    /**
     * @var array<string, string>
     */
    protected $translationCache = [];

    /**
     * Makes this object serializable.
     *
     * @return array<int, string>
     */
    public function __sleep(): array
    {
        $reflectionClass = new \ReflectionClass($this);
        $properties = $reflectionClass->getProperties();

        $propertyNames = [];
        foreach ($properties as $property) {
            $propertyName = $property->getName();
            if ($propertyName === 'frontendController') {
                continue;
            }
            $propertyNames[] = $property->isPrivate() ? (get_class($this) . ':' . $propertyName) : $propertyName;
        }

        return $propertyNames;
    }

    /**
     * Restores data that got lost during the serialization.
     */
    public function __wakeup(): void
    {
        $this->frontendController = $this->getFrontEndController();
    }

    /**
     * Retrieves the localized string for the local language key $key.
     *
     * This function checks whether the FE or BE localization functions are
     * available and then uses the appropriate method.
     *
     * In $this->conf['salutation'], a suffix to the key may be set (which may
     * be either 'formal' or 'informal'). If a corresponding key exists, the
     * formal/informal localized string is used instead.
     * If the formal/informal key doesn't exist, this function just uses the
     * regular string.
     *
     * Example: key = 'greeting', suffix = 'informal'. If the key
     * 'greeting_informal' exists, that string is used.
     * If it doesn't exist, this functions tries to use the string with the key
     * 'greeting'.
     *
     * @param string $key the local language key for which to return the value, must not be empty
     *
     * @return string the requested local language key, might be empty
     */
    public function translate(string $key): string
    {
        if ($key === '') {
            throw new \InvalidArgumentException('$key must not be empty.', 1331489025);
        }
        if ($this->extKey === '') {
            return $key;
        }
        if (isset($this->translationCache[$key])) {
            return $this->translationCache[$key];
        }

        $this->pi_loadLL();
        if (\is_array($this->LOCAL_LANG) && $this->getFrontEndController() !== null) {
            $result = $this->translateInFrontEnd($key);
        } elseif ($this->getLanguageService() !== null) {
            $result = $this->translateInBackEnd($key);
        } else {
            $result = $key;
        }

        $this->translationCache[$key] = $result;

        return $result;
    }

    /**
     * Retrieves the localized string for the local language key $key, using the
     * BE localization methods.
     *
     * @param string $key the local language key for which to return the value, must not be empty
     *
     * @return string the requested local language key, might be empty
     */
    private function translateInBackEnd(string $key): string
    {
        return $this->getLanguageService()->getLL($key);
    }

    /**
     * Retrieves the localized string for the local language key $key, using the
     * FE localization methods.
     *
     * In $this->conf['salutation'], a suffix to the key may be set (which may
     * be either 'formal' or 'informal'). If a corresponding key exists, the
     * formal/informal localized string is used instead.
     * If the formal/informal key doesn't exist, this function just uses the
     * regular string.
     *
     * Example: key = 'greeting', suffix = 'informal'. If the key
     * 'greeting_informal' exists, that string is used.
     * If it doesn't exist, this functions tries to use the string with the key
     * 'greeting'.
     *
     * @param string $key the local language key for which to return the value, must not be empty
     *
     * @return string the requested local language key, might be empty
     */
    private function translateInFrontEnd(string $key): string
    {
        $hasFoundATranslation = false;
        $result = '';

        $availableLanguages = $this->getAvailableLanguages();
        foreach ($this->getSuffixesToTry() as $suffix) {
            $completeKey = $key . $suffix;
            foreach ($availableLanguages as $language) {
                if (isset($this->LOCAL_LANG[$language][$completeKey])) {
                    $result = $this->pi_getLL($completeKey);
                    $hasFoundATranslation = true;
                    break 2;
                }
            }
        }

        if (!$hasFoundATranslation) {
            $result = $key;
        }

        return $result;
    }

    /**
     * Compiles a list of language keys for which localizations have been loaded.
     *
     * @return array<int, string> a list of language keys (may be empty)
     */
    private function getAvailableLanguages(): array
    {
        if ($this->availableLanguages === null) {
            $this->availableLanguages = [];

            if (!empty($this->LLkey)) {
                $this->availableLanguages[] = $this->LLkey;
            }
            // The key for English is "default", not "en".
            $this->availableLanguages = \str_replace('en', 'default', $this->availableLanguages);
            // Remove duplicates in case the default language is the same as the fall-back language.
            $this->availableLanguages = \array_unique($this->availableLanguages);

            // Now check that we only keep languages for which we have translations.
            foreach ($this->availableLanguages as $index => $code) {
                if (!isset($this->LOCAL_LANG[$code])) {
                    unset($this->availableLanguages[$index]);
                }
            }
        }

        return $this->availableLanguages;
    }

    /**
     * Gets an ordered list of language label suffixes that should be tried to
     * get localizations in the preferred order of formality.
     *
     * @return array<int, string> ordered list of suffixes from "", "_formal" and "_informal", will not be empty
     */
    private function getSuffixesToTry(): array
    {
        if ($this->suffixesToTry === null) {
            $this->suffixesToTry = [];

            if (isset($this->conf['salutation'])) {
                if ($this->conf['salutation'] === 'informal') {
                    $this->suffixesToTry[] = '_informal';
                }
                $this->suffixesToTry[] = '_formal';
            }
            $this->suffixesToTry[] = '';
        }

        return $this->suffixesToTry;
    }

    protected function getFrontEndController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }

    /**
     * Returns $GLOBALS['LANG'].
     */
    protected function getLanguageService(): ?LanguageService
    {
        return $GLOBALS['LANG'] ?? null;
    }
}
