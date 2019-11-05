<?php

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationRegistryTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
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
        \Tx_Oelib_PageFinder::getInstance()->setPageUid(
            $this->testingFramework->createFrontEndPage()
        );

        self::assertInstanceOf(
            \Tx_Oelib_Configuration::class,
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
        );
    }

    /**
     * @test
     */
    public function getForTheSameNamespaceCalledTwoTimesReturnsTheSameInstance()
    {
        \Tx_Oelib_PageFinder::getInstance()->setPageUid(
            $this->testingFramework->createFrontEndPage()
        );

        self::assertSame(
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib'),
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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

        \Tx_Oelib_PageFinder::getInstance()->setPageUid($pageUid);

        self::assertSame(
            42,
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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

        \Tx_Oelib_PageFinder::getInstance()->forceSource(
            \Tx_Oelib_PageFinder::SOURCE_BACK_END
        );

        self::assertSame(
            42,
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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
        \Tx_Oelib_PageFinder::getInstance()->forceSource(
            \Tx_Oelib_PageFinder::SOURCE_FRONT_END
        );

        self::assertSame(
            42,
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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
        \Tx_Oelib_PageFinder::getInstance()->forceSource(
            \Tx_Oelib_PageFinder::SOURCE_FRONT_END
        );
        /** @var TypoScriptFrontendController $frontEndController */
        $frontEndController = $GLOBALS['TSFE'];
        $frontEndController->tmpl->rootId = 0;
        $frontEndController->tmpl->rootLine = false;
        $frontEndController->tmpl->setup = [];
        $frontEndController->tmpl->loaded = 0;

        self::assertSame(
            42,
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
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
        \Tx_Oelib_PageFinder::getInstance()->setPageUid($pageUid);

        $configuration = new \Tx_Oelib_Configuration();
        \Tx_Oelib_ConfigurationRegistry::getInstance()
            ->set('plugin.tx_oelib', $configuration);

        self::assertSame(
            $configuration,
            \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')
        );
    }
}
