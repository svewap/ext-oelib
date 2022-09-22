<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use OliverKlee\Oelib\ViewHelpers\PriceViewHelper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\PriceViewHelper
 */
class PriceViewHelperTest extends UnitTestCase
{
    /**
     * @var PriceViewHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PriceViewHelper();
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
    public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits(): void
    {
        self::assertSame(
            '0.00',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoDecimalDigits(): void
    {
        $this->subject->setValue(12345.678);

        self::assertSame(
            '12345.68',
            $this->subject->render()
        );
    }
}
