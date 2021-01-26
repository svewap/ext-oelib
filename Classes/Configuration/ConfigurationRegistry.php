<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Configuration;

use OliverKlee\Oelib\System\Typo3Version;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * This class represents a registration that allows the storage and retrieval
 * of configuration objects.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationRegistry
{
    /**
     * @var ConfigurationRegistry the Singleton instance
     */
    private static $instance = null;

    /**
     * @var array<string, TypoScriptConfiguration> already created configurations (by namespace)
     */
    private $configurations = [];

    /**
     * The constructor. Use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Destructs a configuration for a given namespace and drops the reference to
     * it.
     *
     * @param string $namespace
     *       the namespace of the configuration to drop, must not be empty, must
     *       have been set in this registry
     *
     * @return void
     */
    private function dropConfiguration(string $namespace)
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
        if (!self::$instance) {
            self::$instance = new ConfigurationRegistry();
        }

        return self::$instance;
    }

    /**
     * Purges the current instance so that getInstance will create a new
     * instance.
     *
     * @return void
     */
    public static function purgeInstance()
    {
        self::$instance = null;
    }

    /**
     * Retrieves a TypoScriptConfiguration by namespace.
     *
     * @param string $namespace
     *        the name of a configuration namespace, e.g., "plugin.tx_oelib",
     *        must not be empty
     *
     * @return TypoScriptConfiguration the configuration for the given namespace
     *
     * @see getByNamespace
     */
    public static function get(string $namespace): TypoScriptConfiguration
    {
        return self::getInstance()->getByNamespace($namespace);
    }

    /**
     * Retrieves a TypoScriptConfiguration by namespace.
     *
     * @param string $namespace
     *        the name of a configuration namespace, e.g., "plugin.tx_oelib",
     *        must not be empty
     *
     * @return TypoScriptConfiguration the configuration for the given namespace
     */
    private function getByNamespace(string $namespace): TypoScriptConfiguration
    {
        $this->checkForNonEmptyNamespace($namespace);

        if (!isset($this->configurations[$namespace])) {
            $this->configurations[$namespace]
                = $this->retrieveConfigurationFromTypoScriptSetup($namespace);
        }

        return $this->configurations[$namespace];
    }

    /**
     * Sets a configuration for a certain namespace.
     *
     * @param string $namespace
     *        the namespace of the configuration to set, must not be empty
     * @param TypoScriptConfiguration $configuration
     *        the configuration to set
     *
     * @return void
     */
    public function set(string $namespace, TypoScriptConfiguration $configuration)
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
     *
     * @param string $namespace
     *        namespace name to check
     *
     * @return void
     */
    private function checkForNonEmptyNamespace(string $namespace)
    {
        if ($namespace === '') {
            throw new \InvalidArgumentException('$namespace must not be empty.', 1331318549);
        }
    }

    /**
     * Retrieves the configuration from TS Setup of the current page for a given
     * namespace.
     *
     * @param string $namespace
     *        the namespace of the configuration to retrieve, must not be empty
     *
     * @return TypoScriptConfiguration the TypoScript configuration for that namespace, might be empty
     */
    private function retrieveConfigurationFromTypoScriptSetup(string $namespace): TypoScriptConfiguration
    {
        $data = $this->getCompleteTypoScriptSetup();

        foreach (\explode('.', $namespace) as $namespacePart) {
            if (!array_key_exists($namespacePart . '.', $data)) {
                $data = [];
                break;
            }

            $data = $data[$namespacePart . '.'];
        }

        /** @var TypoScriptConfiguration $configuration */
        $configuration = GeneralUtility::makeInstance(TypoScriptConfiguration::class);
        $configuration->setData($data);
        return $configuration;
    }

    /**
     * Retrieves the complete TypoScript setup for the current page as a nested
     * array.
     *
     * @return array the TypoScriptSetup for the current page, will be empty if
     *               no page is selected or if the TS setup of the page is empty
     */
    private function getCompleteTypoScriptSetup(): array
    {
        $pageUid = PageFinder::getInstance()->getPageUid();
        if ($pageUid === 0) {
            return [];
        }

        if ($this->existsFrontEnd()) {
            return $this->getFrontEndController()->tmpl->setup;
        }

        /** @var TemplateService $template */
        $template = GeneralUtility::makeInstance(TemplateService::class);
        $template->tt_track = 0;
        if (Typo3Version::isNotHigherThan(8)) {
            $template->init();
        }

        /** @var PageRepository $page */
        $page = GeneralUtility::makeInstance(PageRepository::class);
        if (Typo3Version::isNotHigherThan(8)) {
            $rootLine = $page->getRootLine($pageUid);
        } else {
            /** @var RootlineUtility $rootLineUtility */
            $rootLineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid);
            try {
                $rootLine = $rootLineUtility->get();
            } catch (PageNotFoundException $e) {
                $rootLine = [];
            }
        }

        $template->runThroughTemplates($rootLine);
        $template->generateConfig();

        return $template->setup;
    }

    /**
     * Checks whether there is an initialized front end with a loaded TS template.
     *
     * Note: This function can return TRUE even in the BE if there is a front
     * end.
     *
     * @return bool TRUE if there is an initialized front end, FALSE
     *                 otherwise
     */
    private function existsFrontEnd(): bool
    {
        $frontEndController = $this->getFrontEndController();
        return $frontEndController instanceof TypoScriptFrontendController
            && $frontEndController->tmpl instanceof TemplateService && $frontEndController->tmpl->loaded;
    }

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController|null
     */
    protected function getFrontEndController()
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
