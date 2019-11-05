<?php

namespace OliverKlee\Oelib\Tests\Unit\ViewHelper;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class PriceViewHelperTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_ViewHelper_Price
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_ViewHelper_Price();
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
