<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use OliverKlee\Oelib\ViewHelpers\DynamicDateViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\DynamicDateViewHelper
 */
final class DynamicDateViewHelperTest extends UnitTestCase
{
    /**
     * @var RenderingContextInterface&MockObject
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $renderingContextMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderingContextMock = $this->createMock(RenderingContextInterface::class);
    }

    /**
     * @test
     */
    public function isViewHelper(): void
    {
        $subject = new DynamicDateViewHelper();
        $subject->initializeArguments();

        self::assertInstanceOf(AbstractViewHelper::class, $subject);
    }

    /**
     * @test
     */
    public function implementsViewHelper(): void
    {
        $subject = new DynamicDateViewHelper();

        self::assertInstanceOf(ViewHelperInterface::class, $subject);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function initializeArgumentsCanBeCalled(): void
    {
        $subject = new DynamicDateViewHelper();

        $subject->initializeArguments();
    }

    /**
     * @test
     */
    public function doesNotEscapesChildren(): void
    {
        $subject = new DynamicDateViewHelper();

        self::assertFalse($subject->isChildrenEscapingEnabled());
    }

    /**
     * @test
     */
    public function escapesOutput(): void
    {
        $subject = new DynamicDateViewHelper();

        self::assertTrue($subject->isOutputEscapingEnabled());
    }

    /**
     * @test
     */
    public function renderStaticForNullChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $renderChildrenClosure = static function () {
            return null;
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContextMock);
    }

    /**
     * @test
     */
    public function renderStaticForEmptyStringChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $renderChildrenClosure = static function (): string {
            return '';
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContextMock);
    }

    /**
     * @test
     */
    public function renderStaticForDateStringChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $renderChildrenClosure = static function (): string {
            return '1975-04-02';
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContextMock);
    }

    /**
     * @test
     */
    public function renderStaticForIntegerTimestampChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $renderChildrenClosure = static function (): int {
            return 1459513954;
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContextMock);
    }

    /**
     * @test
     */
    public function renderStaticByDefaultUsesGermanDateAndTimeFormat(): void
    {
        $renderChildrenClosure = static function (): \DateTime {
            return new \DateTime('1980-12-07 14:37');
        };

        $result = DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContextMock);

        self::assertStringContainsString('07.12.1980 14:37', $result);
    }

    /**
     * @test
     */
    public function renderStaticUsesProvidedDateAndTimeFormat(): void
    {
        $renderChildrenClosure = static function (): \DateTime {
            return new \DateTime('1980-12-07 14:37');
        };

        $result = DynamicDateViewHelper::renderStatic(
            ['format' => 'Y-m-d g:ia'],
            $renderChildrenClosure,
            $this->renderingContextMock
        );

        self::assertStringContainsString('1980-12-07 2:37pm', $result);
    }
}
