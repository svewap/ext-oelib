<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Language\Fixtures;

use OliverKlee\Oelib\Language\SalutationSwitcher;

/**
 * This is mere a class used for testing. Do not use it for any other purpose.
 *
 * @deprecated will be removed in oelib 6.0
 */
final class TestingSalutationSwitcher extends SalutationSwitcher
{
    /**
     * @var string
     */
    public $scriptRelPath = 'Tests/Functional/Language/Fixtures/locallang.xlf';

    /**
     * @var string
     */
    public $extKey = 'oelib';

    /**
     * The constructor.
     *
     * @param array<string, mixed> $configuration TypoScript setup configuration, may be empty
     */
    public function __construct(array $configuration)
    {
        parent::__construct();

        $this->conf = $configuration;

        $this->pi_setPiVarDefaults();
        $this->pi_loadLL();
    }

    /**
     * Sets the current language for this plugin and loads the language files.
     *
     * @param string $language two-letter lowercase language like "en" or "de" or "default" (which is an alias for "en")
     */
    public function setLanguage(string $language): void
    {
        if ($this->getLanguage() !== $language) {
            // Make sure the language file are reloaded.
            $this->LOCAL_LANG_loaded = false;
            $this->LLkey = $language;
            $this->pi_loadLL();
        }
    }

    /**
     * Gets the current language.
     *
     * @return string the two-letter key of the current language like "en",
     *                "de" or "default" (which is the only non-two-letter
     *                code and an alias for "en"), will return an empty
     *                string if no language key has been set yet
     */
    public function getLanguage(): string
    {
        return $this->LLkey;
    }

    /**
     * Sets the salutation mode.
     *
     * @param string $salutation the salutation mode to use ("formal" or "informal")
     */
    public function setSalutationMode(string $salutation): void
    {
        $this->conf['salutation'] = $salutation;
    }

    /**
     * Gets the salutation mode.
     *
     * @return string the current salutation mode to use: "formal", "informal" or an empty string
     */
    public function getSalutationMode(): string
    {
        return $this->conf['salutation'];
    }
}
