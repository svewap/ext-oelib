<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Interfaces;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Interfaces\Time;

/**
 * Test case.
 *
 * @author Stefano Kowalke <info@arroba-it.de>
 */
class TimeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function aMinuteHasSixtySeconds()
    {
        self::assertSame(
            60,
            Time::SECONDS_PER_MINUTE
        );
    }

    /**
     * @test
     */
    public function anHourHasSixtyMinutes()
    {
        self::assertSame(
            Time::SECONDS_PER_MINUTE * 60,
            Time::SECONDS_PER_HOUR
        );
    }

    /**
     * @test
     */
    public function aDayHasTwentyFourHours()
    {
        self::assertSame(
            Time::SECONDS_PER_HOUR * 24,
            Time::SECONDS_PER_DAY
        );
    }

    /**
     * @test
     */
    public function aWeekHasSevenDays()
    {
        self::assertSame(
            Time::SECONDS_PER_DAY * 7,
            Time::SECONDS_PER_WEEK
        );
    }

    /**
     * @test
     */
    public function aYearHasThreeHundredSixtyFiveDays()
    {
        self::assertSame(
            Time::SECONDS_PER_DAY * 365,
            Time::SECONDS_PER_YEAR
        );
    }
}
