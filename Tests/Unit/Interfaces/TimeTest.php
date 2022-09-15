<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Interfaces;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Interfaces\Time;

/**
 * @covers \OliverKlee\Oelib\Interfaces\Time
 */
final class TimeTest extends UnitTestCase
{
    /**
     * @test
     */
    public function aMinuteHasSixtySeconds(): void
    {
        self::assertSame(
            60,
            Time::SECONDS_PER_MINUTE
        );
    }

    /**
     * @test
     */
    public function anHourHasSixtyMinutes(): void
    {
        self::assertSame(
            Time::SECONDS_PER_MINUTE * 60,
            Time::SECONDS_PER_HOUR
        );
    }

    /**
     * @test
     */
    public function aDayHasTwentyFourHours(): void
    {
        self::assertSame(
            Time::SECONDS_PER_HOUR * 24,
            Time::SECONDS_PER_DAY
        );
    }

    /**
     * @test
     */
    public function aWeekHasSevenDays(): void
    {
        self::assertSame(
            Time::SECONDS_PER_DAY * 7,
            Time::SECONDS_PER_WEEK
        );
    }

    /**
     * @test
     */
    public function aYearHasThreeHundredSixtyFiveDays(): void
    {
        self::assertSame(
            Time::SECONDS_PER_DAY * 365,
            Time::SECONDS_PER_YEAR
        );
    }
}
