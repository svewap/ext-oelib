<?php

declare(strict_types=1);

use OliverKlee\Oelib\Geocoding\GoogleGeocoding;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class takes care of cleaning up oelib after the testing framework.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_TestingFrameworkCleanup
{
    /**
     * Cleans up oelib after running a test.
     *
     * @return void
     */
    public function cleanUp()
    {
        \Tx_Oelib_ConfigurationProxy::purgeInstances();
        \Tx_Oelib_BackEndLoginManager::purgeInstance();
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();
        \Tx_Oelib_FrontEndLoginManager::purgeInstance();
        GoogleGeocoding::purgeInstance();
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();
        MapperRegistry::purgeInstance();
        \Tx_Oelib_PageFinder::purgeInstance();
        \Tx_Oelib_Session::purgeInstances();
        \Tx_Oelib_TemplateHelper::purgeCachedConfigurations();
        \Tx_Oelib_TranslatorRegistry::purgeInstance();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->cleanUp();
    }
}
