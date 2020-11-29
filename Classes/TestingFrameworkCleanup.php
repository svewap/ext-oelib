<?php

declare(strict_types=1);

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Geocoding\GoogleGeocoding;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Language\TranslatorRegistry;
use OliverKlee\Oelib\Mail\MailerFactory;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Session\Session;
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
        HeaderProxyFactory::purgeInstance();
        MapperRegistry::purgeInstance();
        PageFinder::purgeInstance();
        Session::purgeInstances();
        \Tx_Oelib_TemplateHelper::purgeCachedConfigurations();
        TranslatorRegistry::purgeInstance();

        /** @var MailerFactory $mailerFactory */
        $mailerFactory = GeneralUtility::makeInstance(MailerFactory::class);
        $mailerFactory->cleanUp();
    }
}
