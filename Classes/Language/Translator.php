<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Language;

/**
 * This class returns localized labels in the given languages.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Stefano Kowalke <blueduck@gmx.net>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Translator
{
    /**
     * @var string the key of the language to load the translations for
     */
    private $languageKey = '';

    /**
     * @var string the key of the alternative language to load the translations for
     */
    private $alternativeLanguageKey = '';

    /**
     * @var array
     *      the localized labels in a nested associative array:
     *      'languageKey' => array('labelkey' => array(0 => array('source' => 'label', 'target' => 'label')
     */
    private $localizedLabels = [];

    /**
     * The constructor.
     *
     * @param string $languageKey the key of the language to load the translations for, may be empty
     * @param string $alternativeLanguageKey the key of the alternative language to load the translations for, may be
     *     empty
     * @param array<string, array<string, string>> $localizedLabels the localized labels in a nested associative array:
     *        'languageKey' => array('labelkey' => 'label'), may be empty
     */
    public function __construct(string $languageKey, string $alternativeLanguageKey, array $localizedLabels)
    {
        $this->languageKey = $languageKey;
        $this->alternativeLanguageKey = $alternativeLanguageKey;
        $this->localizedLabels = $localizedLabels;
    }

    /**
     * Returns the localized label for the key $key.
     *
     * @param string $key
     *        the key of the label to get the localization for, must not be empty
     * @param bool $useHtmlSpecialChars
     *        whether the localized label should be processes with htmlspecialchars prior to returning it
     *
     * @return string the localized label, might be empty
     */
    public function translate(string $key, bool $useHtmlSpecialChars = false): string
    {
        if ($key === '') {
            throw new \InvalidArgumentException('The parameter $key must not be empty.', 1331489544);
        }

        $translation = $this->translateForNewTypo3($key);

        return $useHtmlSpecialChars ? htmlspecialchars($translation, ENT_QUOTES | ENT_HTML5) : $translation;
    }

    /**
     * Returns the localized label for the key $key.
     *
     * @param string $key the key of the label to get the localization for, must not be empty
     *
     * @return string the localized label, might be empty
     */
    protected function translateForNewTypo3(string $key): string
    {
        if (isset($this->localizedLabels[$this->languageKey][$key][0]['target'])) {
            $translation = $this->localizedLabels[$this->languageKey][$key][0]['target'];
        } elseif (
            ($this->alternativeLanguageKey !== '')
            && isset($this->localizedLabels[$this->alternativeLanguageKey][$key][0]['target'])
        ) {
            $translation = $this->localizedLabels[$this->alternativeLanguageKey][$key][0]['target'];
        } elseif (isset($this->localizedLabels['default'][$key][0]['target'])) {
            $translation = $this->localizedLabels['default'][$key][0]['target'];
        } else {
            $translation = $key;
        }

        return $translation;
    }

    /**
     * Returns the language key in $this->languageKey.
     *
     * Note: This method is meant for testing purposes.
     *
     * @return string the language key in $this->languageKey, may be empty
     */
    public function getLanguageKey(): string
    {
        return $this->languageKey;
    }

    /**
     * Returns the alternative language key in $this->alternativeLanguageKey.
     *
     * Note: This method is meant for testing purposes.
     *
     * @return string the alternative language key in
     *                $this->alternativeLanguageKey, may be empty
     */
    public function getAlternativeLanguageKey(): string
    {
        return $this->alternativeLanguageKey;
    }
}
