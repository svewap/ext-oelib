<?php

declare(strict_types=1);

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\PageFinder;
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
        BackEndLoginManager::purgeInstance();
        ConfigurationProxy::purgeInstances();
        ConfigurationRegistry::purgeInstance();
        FrontEndLoginManager::purgeInstance();
        GoogleGeocoding::purgeInstance();
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();
        MapperRegistry::purgeInstance();
        PageFinder::purgeInstance();
        \Tx_Oelib_Session::purgeInstances();
        \Tx_Oelib_TemplateHelper::purgeCachedConfigurations();
        \Tx_Oelib_TranslatorRegistry::purgeInstance();

        /** @var \Tx_Oelib_MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(\Tx_Oelib_MailerFactory::class);
        $mailerFactory->cleanUp();
    }
}
