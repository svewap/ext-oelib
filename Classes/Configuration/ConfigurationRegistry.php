<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class represents a registration that allows the storage and retrieval of configuration objects.
 */
class ConfigurationRegistry
{
    /**
     * @var ConfigurationRegistry|null the Singleton instance
     */
    private static $instance = null;

    /**
     * @var array<string, ConfigurationInterface> already created configurations (by namespace)
     */
    private $configurations = [];

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Destructs a configuration for a given namespace and drops the reference to it.
     *
     * @param string $namespace the namespace of the configuration to drop, must not be empty,
     *        must have been set in this registry
     */
    private function dropConfiguration(string $namespace): void
    {
        unset($this->configurations[$namespace]);
    }

    /**
     * Returns an instance of this class.
     *
     * @return ConfigurationRegistry the current Singleton instance
     */
    public static function getInstance(): ConfigurationRegistry
    {
        if (!self::$instance instanceof self) {
            self::$instance = new ConfigurationRegistry();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Retrieves a Configuration by namespace.
     *
     * @param string $namespace the name of a configuration namespace, e.g., "plugin.tx_oelib", must not be empty
     *
     * @return ConfigurationInterface the configuration for the given namespace
     *
     * @see getByNamespace
     */
    public static function get(string $namespace): ConfigurationInterface
    {
        return self::getInstance()->getByNamespace($namespace);
    }

    /**
     * Retrieves a Configuration by namespace.
     *
     * @param string $namespace the name of a configuration namespace, e.g., "plugin.tx_oelib", must not be empty
     *
     * @return ConfigurationInterface the configuration for the given namespace
     */
    private function getByNamespace(string $namespace): ConfigurationInterface
    {
        $this->checkForNonEmptyNamespace($namespace);

        if (!isset($this->configurations[$namespace])) {
            $this->configurations[$namespace] = $this->retrieveConfigurationFromTypoScriptSetup($namespace);
        }

        return $this->configurations[$namespace];
    }

    /**
     * Sets a configuration for a certain namespace.
     *
     * @param string $namespace the namespace of the configuration to set, must not be empty
     */
    public function set(string $namespace, ConfigurationInterface $configuration): void
    {
        $this->checkForNonEmptyNamespace($namespace);

        if (isset($this->configurations[$namespace])) {
            $this->dropConfiguration($namespace);
        }

        $this->configurations[$namespace] = $configuration;
    }

    /**
     * Checks that $namespace is non-empty.
     *
     * @throws \InvalidArgumentException if $namespace is empty
     */
    private function checkForNonEmptyNamespace(string $namespace): void
    {
        if ($namespace === '') {
            throw new \InvalidArgumentException('$namespace must not be empty.', 1331318549);
        }
    }

    /**
     * Retrieves the configuration from TypoScript setup of the current page for a given namespace.
     *
     * @param string $namespace the namespace of the configuration to retrieve, must not be empty
     *
     * @return TypoScriptConfiguration the TypoScript configuration for that namespace, might be empty
     */
    private function retrieveConfigurationFromTypoScriptSetup(string $namespace): TypoScriptConfiguration
    {
        $data = $this->getCompleteTypoScriptSetup();

        foreach (\explode('.', $namespace) as $namespacePart) {
            if (!\array_key_exists($namespacePart . '.', $data)) {
                $data = [];
                break;
            }

            $data = $data[$namespacePart . '.'];
        }

        $configuration = GeneralUtility::makeInstance(TypoScriptConfiguration::class);
        $configuration->setData($data);
        return $configuration;
    }

    /**
     * Retrieves the complete TypoScript setup for the current page as a nested array.
     *
     * @return array<string, mixed> the TypoScriptSetup for the current page, will be empty if
     *         no page is selected or if the TypoScript setup of the page is empty
     */
    private function getCompleteTypoScriptSetup(): array
    {
        $pageUid = PageFinder::getInstance()->getPageUid();
        if ($pageUid === 0) {
            return [];
        }

        $frontEndController = $this->getFrontEndController();
        $template = $frontEndController instanceof TypoScriptFrontendController ? $frontEndController->tmpl : null;
        if ($template instanceof TemplateService && $template->loaded) {
            return $template->setup;
        }

        $template = GeneralUtility::makeInstance(TemplateService::class);
        $template->tt_track = false;

        try {
            $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
        } catch (PageNotFoundException $e) {
            $rootLine = [];
        }

        $template->runThroughTemplates($rootLine);
        $template->generateConfig();

        return $template->setup;
    }

    protected function getFrontEndController(): ?TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
