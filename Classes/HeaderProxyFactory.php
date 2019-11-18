<?php

declare(strict_types=1);

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class returns either an instance of the \Tx_Oelib_RealHeaderProxy which
 * adds HTTP headers or an instance of the \Tx_Oelib_HeaderCollector. The
 * collector stores the headers that were added and does not send them. This
 * mode is for testing purposes.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_HeaderProxyFactory
{
    /**
     * @var \Tx_Oelib_HeaderProxyFactory
     */
    private static $instance = null;

    /**
     * @var bool
     */
    private $isTestMode = false;

    /**
     * @var \Tx_Oelib_AbstractHeaderProxy
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
     * @return \Tx_Oelib_HeaderProxyFactory the singleton factory
     */
    public static function getInstance(): \Tx_Oelib_HeaderProxyFactory
    {
        if (!is_object(self::$instance)) {
            self::$instance = new \Tx_Oelib_HeaderProxyFactory();
        }

        return self::$instance;
    }

    /**
     * Retrieves the singleton header proxy instance. Depending on the mode,
     * this instance is either a header collector or a real header proxy.
     *
     * @return \Tx_Oelib_AbstractHeaderProxy|\Tx_Oelib_HeaderCollector|\Tx_Oelib_RealHeaderProxy the singleton header
     *     proxy
     */
    public function getHeaderProxy()
    {
        if ($this->isTestMode) {
            $className = \Tx_Oelib_HeaderCollector::class;
        } else {
            $className = \Tx_Oelib_RealHeaderProxy::class;
        }

        if (!is_object($this->headerProxy) || (get_class($this->headerProxy) !== $className)) {
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
