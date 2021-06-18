<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\ViewHelpers\DynamicDateViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\CMS\Fluid\Core\ViewHelper\Exception as ViewHelperException;
use TYPO3\CMS\Fluid\Core\ViewHelper\Facets\CompilableInterface;

final class DynamicDateViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var DynamicDateViewHelper|MockObject
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = $this->createPartialMock(DynamicDateViewHelper::class, ['renderChildren']);
        $this->injectDependenciesIntoViewHelper($this->subject);
    }

    /**
     * @test
     */
    public function classIsViewHelper()
    {
        self::assertInstanceOf(AbstractViewHelper::class, $this->subject);
    }

    /**
     * @test
     */
    public function classIsCompilable()
    {
        self::assertInstanceOf(CompilableInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function renderForNullChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn(null);

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForEmptyStringChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn('');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForDateStringChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn('1975-04-02');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForIntegerTimestampChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $this->subject->expects(self::once())->method('renderChildren')->willReturn(1459513954);

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderByDefaultUsesGermanDateAndTimeFormat()
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $result = $this->subject->render();

        self::assertContains('07.12.1980 14:37', $result);
    }

    /**
     * @test
     */
    public function renderUsesProvidedDateAndTimeFormatForVisibleDate()
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $result = $this->subject->render('Y-m-d g:ia');

        self::assertContains('1980-12-07 2:37pm', $result);
    }

    /**
     * @test
     */
    public function renderAddsTimeAgoCssClass()
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $result = $this->subject->render('Y-m-d g:ia');

        self::assertContains('class="js-time-ago"', $result);
    }

    /**
     * @test
     */
    public function renderUsesProvidedDateAndTimeFormatForTimeElementDate()
    {
        $date = new \DateTime('1980-12-07 14:37');
        $this->subject->expects(self::once())->method('renderChildren')->willReturn($date);

        $result = $this->subject->render('Y-m-d g:ia');

        self::assertContains('<time datetime="1980-12-07T14:37"', $result);
    }

    /**
     * @test
     */
    public function renderStaticForNullChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $renderChildrenClosure = static function () {
            return null;
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
    }

    /**
     * @test
     */
    public function renderStaticForEmptyStringChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $renderChildrenClosure = static function () {
            return '';
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
    }

    /**
     * @test
     */
    public function renderStaticForDateStringChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $renderChildrenClosure = static function () {
            return '1975-04-02';
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
    }

    /**
     * @test
     */
    public function renderStaticForIntegerTimestampChildrenThrowsException()
    {
        $this->expectException(ViewHelperException::class);
        $renderChildrenClosure = static function () {
            return 1459513954;
        };

        DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);
    }

    /**
     * @test
     */
    public function renderStaticByDefaultUsesGermanDateAndTimeFormat()
    {
        $renderChildrenClosure = static function () {
            return new \DateTime('1980-12-07 14:37');
        };

        $result = DynamicDateViewHelper::renderStatic([], $renderChildrenClosure, $this->renderingContext);

        self::assertContains('07.12.1980 14:37', $result);
    }

    /**
     * @test
     */
    public function renderStaticUsesProvidedDateAndTimeFormat()
    {
        $renderChildrenClosure = static function () {
            return new \DateTime('1980-12-07 14:37');
        };

        $result = DynamicDateViewHelper::renderStatic(
            ['format' => 'Y-m-d g:ia'],
            $renderChildrenClosure,
            $this->renderingContext
        );

        self::assertContains('1980-12-07 2:37pm', $result);
    }
}
