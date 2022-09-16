<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\ViewHelpers\IsFieldEnabledViewHelper;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\IsFieldEnabledViewHelper
 */
final class IsFieldEnabledViewHelperTest extends UnitTestCase
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

    /**
     * @test
     */
    public function isConditionViewHelper(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        self::assertInstanceOf(AbstractConditionViewHelper::class, $subject);
    }

    /**
     * @test
     */
    public function implementsViewHelper(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        self::assertInstanceOf(ViewHelperInterface::class, $subject);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function initializeArgumentsCanBeCalled(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        $subject->initializeArguments();
    }

    /**
     * @test
     */
    public function escapesChildren(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        self::assertTrue($subject->isChildrenEscapingEnabled());
    }

    /**
     * @test
     */
    public function doesNotEscapeOutput(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        self::assertFalse($subject->isOutputEscapingEnabled());
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

        IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function renderStaticForMissingSettingNameInSettingsThrowsException(): void
    {
        $this->expectExceptionCode(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No field "fieldsToShow" in settings found.');
        $this->expectExceptionCode(1651154598);

        $this->variableProviderProphecy->get('settings')->willReturn([]);

        IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );
    }

    /**
     * @return array<string, array<int, array{}|positive-int>>
     */
    public function nonStringSettingDataProvider(): array
    {
        return [
            'array' => [[]],
            'int' => [5],
        ];
    }

    /**
     * @test
     *
     * @param array{}|int $value
     *
     * @dataProvider nonStringSettingDataProvider
     */
    public function renderStaticForNonStringSettingNameInSettingsThrowsException($value): void
    {
        $this->expectExceptionCode(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The setting "fieldsToShow" needs to be a string.');
        $this->expectExceptionCode(1651155151);

        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => $value]);

        IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function renderStaticForMissingFieldNameThrowsException(): void
    {
        $this->expectExceptionCode(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "fieldName" must not be empty.');
        $this->expectExceptionCode(1651155957);

        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company']);

        IsFieldEnabledViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function renderStaticForEmptyFieldNameThrowsException(): void
    {
        $this->expectExceptionCode(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "fieldName" must not be empty.');
        $this->expectExceptionCode(1651155957);

        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company']);

        IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => ''],
            $this->renderChildrenClosure,
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function renderStaticForNonStringFieldNameThrowsException(): void
    {
        $this->expectExceptionCode(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The argument "fieldName" must be a string, but was array');
        $this->expectExceptionCode(1651496544);

        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company']);

        IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => []],
            $this->renderChildrenClosure,
            $this->renderingContext
        );
    }

    /**
     * @test
     */
    public function renderForSingleRequestedFieldEnabledRendersThenChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedFieldEnabledWithOtherAfterRendersThenChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company,name']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedFieldEnabledWithOtherBeforeRendersThenChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'name,company']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForOneOfTwoRequestedFieldsEnabledRendersThenChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company|name', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForBothRequestedFieldsEnabledRendersThenChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company,name']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'company|name', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('THEN', $result);
    }

    /**
     * @test
     */
    public function renderForRequestedFieldNotEnabledRendersElseChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => 'company']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'name', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('ELSE', $result);
    }

    /**
     * @test
     */
    public function renderForNoFieldEnabledRendersElseChild(): void
    {
        $this->variableProviderProphecy->get('settings')->willReturn(['fieldsToShow' => '']);

        $result = IsFieldEnabledViewHelper::renderStatic(
            ['fieldName' => 'name', 'then' => 'THEN', 'else' => 'ELSE'],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('ELSE', $result);
    }
}
