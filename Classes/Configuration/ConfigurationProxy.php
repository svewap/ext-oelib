<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This singleton class provides access to an extension's global configuration
 * and allows faking global configuration values for testing purposes.
 */
class ConfigurationProxy extends AbstractReadOnlyObjectWithPublicAccessors implements ConfigurationInterface
{
    /**
     * @var array<string, ConfigurationInterface> the singleton configuration proxy objects
     */
    private static $instances = [];

    /**
     * @var array<string, mixed> configuration data for each extension which currently uses the configuration proxy
     */
    private $configuration = [];

    /**
     * @var string key of the extension for which the EM configuration is stored
     */
    private $extensionKey;

    /**
     * @var bool whether the configuration is already loaded
     */
    private $isConfigurationLoaded = false;

    /**
     * Don't call this constructor; use getInstance instead.
     *
     * @param string $extensionKey extension key without the 'tx' prefix, used to retrieve the EM
     *        configuration and as identifier for an extension's instance of
     *        this class, must not be empty
     */
    private function __construct(string $extensionKey)
    {
        $this->extensionKey = $extensionKey;
    }

    /**
     * Retrieves the configuration for the given extension key.
     *
     * @param string $extensionKey extension key without the 'tx' prefix, used to retrieve the EM
     *        configuration and as identifier for an extension's instance of
     *        this class, must not be empty
     *
     * @return ConfigurationInterface the configuration for the given extension key
     *
     * @throws \InvalidArgumentException
     */
    public static function getInstance(string $extensionKey): ConfigurationInterface
    {
        if ($extensionKey === '') {
            throw new \InvalidArgumentException('The extension key was not set.', 1331318826);
        }

        if (!isset(self::$instances[$extensionKey])) {
            self::setInstance($extensionKey, new ConfigurationProxy($extensionKey));
        }

        return self::$instances[$extensionKey];
    }

    /**
     * Sets/replaces a configuration for the given extension key.
     *
     * This method is mainly intended to be used in testing.
     *
     * @throws \InvalidArgumentException
     */
    public static function setInstance(string $extensionKey, ConfigurationInterface $configuration): void
    {
        if ($extensionKey === '') {
            throw new \InvalidArgumentException('The extension key must not be empty.', 1612091700);
        }

        self::$instances[$extensionKey] = $configuration;
    }

    /**
     * Purges the current instances so that getInstance will create new instances.
     */
    public static function purgeInstances(): void
    {
        self::$instances = [];
    }

    /**
     * Loads the EM configuration for the extension key passed via
     * `getInstance()` if the configuration is not yet loaded.
     */
    private function loadConfigurationLazily(): void
    {
        if (!$this->isConfigurationLoaded) {
            $this->retrieveConfiguration();
        }
    }

    /**
     * Retrieves the EM configuration for the extension key passed via `getInstance()`.
     *
     * This function is accessible for testing purposes. As lazy implementation
     * is used, this function might be useful to ensure static test conditions.
     */
    public function retrieveConfiguration(): void
    {
        if (
            isset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$this->extensionKey])
            && \is_array($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS'][$this->extensionKey])
        ) {
            /** @var ExtensionConfiguration $extensionConfiguration */
            $extensionConfiguration = GeneralUtility::makeInstance(ExtensionConfiguration::class);
            $this->configuration = $extensionConfiguration->get($this->extensionKey);
        } else {
            $this->configuration = [];
        }
        $this->isConfigurationLoaded = true;
    }

    /**
     * Checks whether a certain key exists in an extension's configuration.
     *
     * @param string $key key to check, must not be empty
     *
     * @return bool whether $key occurs in the configuration array of the extension named $this->extensionKey
     */
    private function hasConfigurationValue(string $key): bool
    {
        $this->loadConfigurationLazily();

        return isset($this->configuration[$key]);
    }

    /**
     * Returns a string configuration value.
     *
     * @param string $key key of the value to get, must not be empty
     *
     * @return mixed configuration value string, might be empty
     */
    protected function get(string $key)
    {
        $this->loadConfigurationLazily();

        if ($this->hasConfigurationValue($key)) {
            $result = $this->configuration[$key];
        } else {
            $result = '';
        }

        return $result;
    }

    /**
     * Sets a new configuration value.
     *
     * The configuration setters are intended to be used for testing purposes only.
     *
     * @param string $key key of the value to set, must not be empty
     * @param mixed $value the value to set
     */
    protected function set(string $key, $value): void
    {
        $this->loadConfigurationLazily();

        $this->configuration[$key] = $value;
    }

    /**
     * Returns an extension's complete configuration.
     *
     * @return array<string, mixed> an extension's configuration, empty if the configuration was not retrieved before
     */
    public function getCompleteConfiguration(): array
    {
        $this->loadConfigurationLazily();

        return $this->configuration;
    }
}
