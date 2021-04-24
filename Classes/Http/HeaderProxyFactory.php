<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Http;

use OliverKlee\Oelib\Http\Interfaces\HeaderProxy;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class returns either an instance of the RealHeaderProxy which
 * adds HTTP headers or an instance of the HeaderCollector. The
 * collector stores the headers that were added and does not send them. This
 * mode is for testing purposes.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class HeaderProxyFactory
{
    /**
     * @var HeaderProxyFactory
     */
    private static $instance = null;

    /**
     * @var bool
     */
    private $isTestMode = false;

    /**
     * @var HeaderProxy
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
     * @return HeaderProxy|HeaderCollector|RealHeaderProxy the singleton header proxy
     */
    public function getHeaderProxy()
    {
        $className = $this->isTestMode ? HeaderCollector::class : RealHeaderProxy::class;
        if (!$this->headerProxy instanceof $className) {
            $this->headerProxy = GeneralUtility::makeInstance($className);
        }

        return $this->headerProxy;
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
     * Enables the test mode.
     *
     * @return void
     */
    public function enableTestMode()
    {
        $this->isTestMode = true;
    }
}
