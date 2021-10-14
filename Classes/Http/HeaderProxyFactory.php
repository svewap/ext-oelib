<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http;

use OliverKlee\Oelib\Http\Interfaces\HeaderProxy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class returns either an instance of the RealHeaderProxy which
 * adds HTTP headers or an instance of the `HeaderCollector`. The
 * collector stores the headers that were added and does not send them. This
 * mode is for testing purposes.
 */
class HeaderProxyFactory
{
    /**
     * @var HeaderProxyFactory|null
     */
    private static $instance = null;

    /**
     * @var bool
     */
    private $isTestMode = false;

    /**
     * @var HeaderProxy|null
     */
    private $headerProxy = null;

    /**
     * Don't call this constructor; use getInstance() instead.
     */
    private function __construct()
    {
    }

    /**
     * Retrieves the singleton instance of the factory.
     *
     * @return self the singleton factory
     */
    public static function getInstance(): self
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Retrieves the singleton header proxy instance. Depending on the mode,
     * this instance is either a header collector or a real header proxy.
     *
     * @return HeaderProxy the singleton header proxy
     */
    public function getHeaderProxy(): HeaderProxy
    {
        $className = $this->isTestMode ? HeaderCollector::class : RealHeaderProxy::class;
        if (!$this->headerProxy instanceof $className) {
            $this->headerProxy = GeneralUtility::makeInstance($className);
        }

        return $this->headerProxy;
    }

    /**
     * Returns the header collector (i.e., like `getHeaderProxy`, but for test mode only).
     *
     * This is syntactic sugar to help type checkers (and human readers).
     *
     * @throws \BadMethodCallException if this method is called outside the test mode
     */
    public function getHeaderCollector(): HeaderCollector
    {
        $headerCollector = $this->getHeaderProxy();
        if (!$this->isTestMode || !$headerCollector instanceof HeaderCollector) {
            throw new \BadMethodCallException('getHeaderCollector() may only be called in test mode.', 1630827563);
        }

        return $headerCollector;
    }

    /**
     * Purges the current instance so that getInstance will create a new instance.
     */
    public static function purgeInstance(): void
    {
        self::$instance = null;
    }

    /**
     * Enables the test mode.
     */
    public function enableTestMode(): void
    {
        $this->isTestMode = true;
    }
}
