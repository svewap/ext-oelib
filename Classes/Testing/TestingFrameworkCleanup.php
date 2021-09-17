<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Geocoding\GoogleGeocoding;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Session\Session;
use OliverKlee\Oelib\Templating\TemplateHelper;

/**
 * This class takes care of cleaning up oelib after the testing framework.
 */
class TestingFrameworkCleanup
{
    /**
     * Cleans up oelib after running a test.
     */
    public function cleanUp(): void
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
        TemplateHelper::purgeCachedConfigurations();
    }
}
