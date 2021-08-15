<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ConfigurationRegistryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    ////////////////////////////////
    // Test concerning get and set
    ////////////////////////////////

    /**
     * @test
     */
    public function getForNonEmptyNamespaceReturnsConfigurationInstance()
    {
        PageFinder::getInstance()->setPageUid(
            $this->testingFramework->createFrontEndPage()
        );

        self::assertInstanceOf(
            TypoScriptConfiguration::class,
            ConfigurationRegistry::get('plugin.tx_oelib')
        );
    }

    /**
     * @test
     */
    public function getForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance()
    {
        PageFinder::getInstance()->setPageUid(
            $this->testingFramework->createFrontEndPage()
        );

        self::assertSame(
            ConfigurationRegistry::get('plugin.tx_oelib'),
            ConfigurationRegistry::get('plugin.tx_oelib')
        );
    }

    //////////////////////////////////////
    // Tests concerning TypoScript setup
    //////////////////////////////////////

    /**
     * @test
     */
    public function getReturnsDataFromTypoScriptSetupFromManuallySetPage()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            ['config' => 'plugin.tx_oelib.test = 42']
        );

        PageFinder::getInstance()->setPageUid($pageUid);

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')
                ->getAsInteger('test')
        );
    }

    /**
     * @test
     */
    public function getReturnsDataFromTypoScriptSetupFromBackEndPage()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            ['config' => 'plugin.tx_oelib.test = 42']
        );
        $_POST['id'] = $pageUid;

        PageFinder::getInstance()->forceSource(
            PageFinder::SOURCE_BACK_END
        );

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')
                ->getAsInteger('test')
        );

        unset($_POST['id']);
    }

    /**
     * @test
     */
    public function getReturnsDataFromTypoScriptSetupFromFrontEndPage()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            ['config' => 'plugin.tx_oelib.test = 42']
        );

        $this->testingFramework->createFakeFrontEnd($pageUid);
        PageFinder::getInstance()->forceSource(
            PageFinder::SOURCE_FRONT_END
        );

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')
                ->getAsInteger('test')
        );
    }

    /**
     * @test
     */
    public function readsDataFromTypoScriptSetupEvenForFrontEndWithoutLoadedTemplate()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            ['config' => 'plugin.tx_oelib.test = 42']
        );

        $this->testingFramework->createFakeFrontEnd($pageUid);
        PageFinder::getInstance()->forceSource(
            PageFinder::SOURCE_FRONT_END
        );
        /** @var TypoScriptFrontendController $frontEndController */
        $frontEndController = $GLOBALS['TSFE'];
        if (Typo3Version::isNotHigherThan(8)) {
            // @phpstan-ignore-next-line We run the PHPStan checks with TYPO3 9LTS, and this code is for 8 only.
            $frontEndController->tmpl->rootId = 0;
        }
        $frontEndController->tmpl->rootLine = false;
        $frontEndController->tmpl->setup = [];
        $frontEndController->tmpl->loaded = 0;

        self::assertSame(
            42,
            ConfigurationRegistry::get('plugin.tx_oelib')
                ->getAsInteger('test')
        );
    }

    /**
     * @test
     */
    public function getAfterSetReturnsManuallySetConfigurationEvenIfThereIsAPage()
    {
        $pageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createTemplate(
            $pageUid,
            ['config' => 'plugin.tx_oelib.bar = 42']
        );
        PageFinder::getInstance()->setPageUid($pageUid);

        $configuration = new TypoScriptConfiguration();
        ConfigurationRegistry::getInstance()
            ->set('plugin.tx_oelib', $configuration);

        self::assertSame(
            $configuration,
            ConfigurationRegistry::get('plugin.tx_oelib')
        );
    }
}
