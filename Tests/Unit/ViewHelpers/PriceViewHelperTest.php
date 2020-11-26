<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\ViewHelpers\PriceViewhelper;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class PriceViewHelperTest extends UnitTestCase
{
    /**
     * @var PriceViewhelper
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new PriceViewhelper();
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
