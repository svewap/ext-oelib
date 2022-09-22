<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\ExtbaseConfiguration;
use OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures\TestingConfigurationCheck;
use OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures\TestingConfigurationCheckViewHelper;
use OliverKlee\Oelib\ViewHelpers\AbstractConfigurationCheckViewHelper;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\AbstractConfigurationCheckViewHelper
 */
final class AbstractConfigurationCheckViewHelperTest extends UnitTestCase
{
    /**
     * @var \Closure
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $renderChildrenClosure;

    /**
     * @var RenderingContextInterface
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $renderingContext;

    /**
     * @var ObjectProphecy<VariableProviderInterface>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $variableProviderProphecy;

    /**
     * @var VariableProviderInterface
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $variableProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderChildrenClosure = static function (): string {
            return '';
        };
        $renderingContextProphecy = $this->prophesize(RenderingContextInterface::class);
        $this->renderingContext = $renderingContextProphecy->reveal();
        $this->variableProviderProphecy = $this->prophesize(VariableProviderInterface::class);
        $this->variableProvider = $this->variableProviderProphecy->reveal();
        $renderingContextProphecy->getVariableProvider()->willReturn($this->variableProvider);
    }

    protected function tearDown(): void
    {
        ConfigurationProxy::purgeInstances();
        unset($GLOBALS['BE_USER']);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isViewHelper(): void
    {
        $subject = new TestingConfigurationCheckViewHelper();

        self::assertInstanceOf(AbstractViewHelper::class, $subject);
        self::assertInstanceOf(AbstractConfigurationCheckViewHelper::class, $subject);
    }

    /**
     * @test
     */
    public function implementsViewHelper(): void
    {
        $subject = new TestingConfigurationCheckViewHelper();

        self::assertInstanceOf(ViewHelperInterface::class, $subject);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function initializeArgumentsCanBeCalled(): void
    {
        $subject = new TestingConfigurationCheckViewHelper();

        $subject->initializeArguments();
    }

    /**
     * @test
     */
    public function escapesChildren(): void
    {
        $subject = new TestingConfigurationCheckViewHelper();

        self::assertTrue($subject->isChildrenEscapingEnabled());
    }

    /**
     * @test
     */
    public function doesNotEscapeOutput(): void
    {
        $subject = new TestingConfigurationCheckViewHelper();

        self::assertFalse($subject->isOutputEscapingEnabled());
    }

    /**
     * @test
     */
    public function renderStaticForConfigurationCheckDisabledReturnsEmptyString(): void
    {
        $extensionKey = 'oelib';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => false]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        $result = TestingConfigurationCheckViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderStaticForMissingSettingsInArgumentsThrowsException(): void
    {
        $this->expectExceptionCode(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No settings in the variable container found.');
        $this->expectExceptionCode(1651153736);

        $this->variableProviderProphecy->get('settings')->willReturn(null);

        $extensionKey = 'oelib';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        $result = TestingConfigurationCheckViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('This is a configuration check warning.', $result);
    }

    /**
     * @test
     */
    public function renderStaticForConfigurationCheckEnabledReturnsMessageFromConfigurationCheck(): void
    {
        $extensionKey = 'oelib';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);
        $this->variableProviderProphecy->get('settings')->willReturn([]);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        $result = TestingConfigurationCheckViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertStringContainsString('This is a configuration check warning.', $result);
    }

    /**
     * @test
     */
    public function renderStaticForConfigurationCheckEnabledPassesConfigurationToConfigurationCheck(): void
    {
        $key = 'foo';
        $value = 'bar';
        $settings = [$key => $value];
        $extensionKey = 'oelib';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);
        $this->variableProviderProphecy->get('settings')->willReturn($settings);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        TestingConfigurationCheckViewHelper::renderStatic([], $this->renderChildrenClosure, $this->renderingContext);

        $configuration = TestingConfigurationCheck::getCheckedConfiguration();
        self::assertInstanceOf(ExtbaseConfiguration::class, $configuration);
        self::assertSame($value, $configuration->getAsString($key));
    }
}
