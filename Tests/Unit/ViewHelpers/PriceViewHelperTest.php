<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\ViewHelpers\PriceViewHelper;

/**
 * Test case.
 */
class PriceViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var PriceViewHelper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new PriceViewHelper();
    }

    /**
     * @test
     */
    public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits()
    {
        self::assertSame(
            '0.00',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoDecimalDigits()
    {
        $this->subject->setValue(12345.678);

        self::assertSame(
            '12345.68',
            $this->subject->render()
        );
    }
}
