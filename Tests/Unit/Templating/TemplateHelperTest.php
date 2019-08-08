<?php

namespace OliverKlee\Oelib\Tests\Unit\Templating;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Templating\Fixtures\PluginWithCustomConfigurationCheck;
use OliverKlee\Oelib\Tests\Unit\Templating\Fixtures\TestingConfigurationCheck;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TemplateHelperTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_ConfigCheck|ObjectProphecy
     */
    private $defaultConfigurationCheckProphecy = null;

    /**
     * @var \Tx_Oelib_ConfigCheck|ProphecySubjectInterface
     */
    private $defaultConfigurationCheck = null;

    /**
     * @var TestingConfigurationCheck|ObjectProphecy
     */
    private $customConfigurationCheckProphecy = null;

    /**
     * @var TestingConfigurationCheck|ProphecySubjectInterface
     */
    private $customConfigurationCheck = null;

    protected function setUp()
    {
        $this->customConfigurationCheckProphecy = $this->prophesize(TestingConfigurationCheck::class);
        $this->customConfigurationCheck = $this->customConfigurationCheckProphecy->reveal();
        GeneralUtility::addInstance(TestingConfigurationCheck::class, $this->customConfigurationCheck);

        $this->defaultConfigurationCheckProphecy = $this->prophesize(\Tx_Oelib_ConfigCheck::class);
        $this->defaultConfigurationCheck = $this->defaultConfigurationCheckProphecy->reveal();
        GeneralUtility::addInstance(\Tx_Oelib_ConfigCheck::class, $this->defaultConfigurationCheck);
    }

    protected function tearDown()
    {
        $templateHelperStub = $this->prophesize(\Tx_Oelib_TemplateHelper::class)->reveal();
        GeneralUtility::makeInstance(TestingConfigurationCheck::class, $templateHelperStub);
        GeneralUtility::makeInstance(\Tx_Oelib_ConfigCheck::class, $templateHelperStub);
        \Tx_Oelib_ConfigurationProxy::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function defaultConfigurationCheckCanBeUsed()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', true);
        $subject = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingTemplateHelper([]);

        self::assertInstanceOf(\Tx_Oelib_ConfigCheck::class, $subject->getConfigurationCheck());
    }

    /**
     * @test
     */
    public function checkConfigurationWithEnabledConfigurationAndDefaultConfigurationCheckCheckChecksIt()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', true);
        $subject = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingTemplateHelper([]);

        $this->defaultConfigurationCheckProphecy->checkItAndWrapIt()->shouldBeCalled();

        $subject->checkConfiguration();
    }

    /**
     * @test
     */
    public function customConfigurationCheckCanBeSet()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', true);
        $subject = new PluginWithCustomConfigurationCheck();
        $subject->init([]);

        self::assertInstanceOf(TestingConfigurationCheck::class, $subject->getConfigurationCheck());
    }

    /**
     * @test
     */
    public function checkConfigurationWithEnabledConfigurationAndCustomConfigurationCheckCheckChecksIt()
    {
        \Tx_Oelib_ConfigurationProxy::getInstance('oelib')->setAsBoolean('enableConfigCheck', true);
        $subject = new PluginWithCustomConfigurationCheck();
        $subject->init([]);

        $this->customConfigurationCheckProphecy->checkItAndWrapIt()->shouldBeCalled();

        $subject->checkConfiguration();
    }
}
