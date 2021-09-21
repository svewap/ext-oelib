<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\ViewHelpers\DynamicDateViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\DynamicDateViewHelper
 */
final class DynamicDateViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var DynamicDateViewHelper&MockObject
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        /** @var DynamicDateViewHelper&MockObject $subject */
        $subject = $this->createPartialMock(DynamicDateViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($subject);
        $this->subject = $subject;
    }

    /**
     * @test
     */
    public function isViewHelper(): void
    {
        self::assertInstanceOf(AbstractViewHelper::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsViewHelper(): void
    {
        self::assertInstanceOf(ViewHelperInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function renderForNullChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn(null);

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForEmptyStringChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn('');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForDateStringChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn('1975-04-02');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForIntegerTimestampChildrenThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn(1459513954);

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderByDefaultUsesGermanDateAndTimeFormat(): void
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $result = $this->subject->render();

        self::assertContains('07.12.1980 14:37', $result);
    }

    /**
     * @test
     */
    public function renderUsesProvidedDateAndTimeFormatForVisibleDate(): void
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $this->subject->setArguments(['displayFormat' => 'Y-m-d g:ia']);
        $result = $this->subject->render();

        self::assertContains('1980-12-07 2:37pm', $result);
    }

    /**
     * @test
     */
    public function renderAddsTimeAgoCssClass(): void
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $result = $this->subject->render();

        self::assertContains('class="js-time-ago"', $result);
    }

    /**
     * @test
     */
    public function renderUsesProvidedDateAndTimeFormatForTimeElementDate(): void
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $this->subject->setArguments(['displayFormat' => 'Y-m-d g:ia']);
        $result = $this->subject->render();

        self::assertContains('<time datetime="1980-12-07T14:37"', $result);
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

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
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

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
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

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
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

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
    }

    /**
     * @test
     */
    public function renderStaticByDefaultUsesGermanDateAndTimeFormat(): void
    {
        $renderChildrenClosure = static function (): \DateTime {
            return new \DateTime('1980-12-07 14:37');
        };

        $result = DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);

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
            $this->renderingContext
        );

        self::assertStringContainsString('1980-12-07 2:37pm', $result);
    }
}
