<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\FallbackConfiguration;
use OliverKlee\Oelib\Interfaces\Configuration;

/**
 * @covers \OliverKlee\Oelib\Configuration\FallbackConfiguration
 */
final class FallbackConfigurationTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsConfiguration()
    {
        $subject = new FallbackConfiguration(new DummyConfiguration(), new DummyConfiguration());

        self::assertInstanceOf(Configuration::class, $subject);
    }

    /**
     * @test
     */
    public function getAsStringForBothEmptyStringReturnsEmptyString()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame('', $subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringForBothNonEmptyReturnsValueFromPrimary()
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringForPrimaryNonEmptyAndSecondaryEmptyReturnsValueFromPrimary()
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringForPrimaryEmptyAndSecondaryNonEmptyReturnsValueFromSecondary()
    {
        $key = 'something';
        $primaryValue = '';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($secondaryValue, $subject->getAsString($key));
    }

    /**
     * @test
     */
    public function hasStringForBothEmptyStringReturnsFalse()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertFalse($subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasStringForBothNonEmptyReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 'primary']);
        $secondary = new DummyConfiguration([$key => 'secondary']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasStringForPrimaryNonEmptyAndSecondaryEmptyReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 'primary']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasStringForPrimaryEmptyAndSecondaryNonEmptyReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => 'secondary']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasString($key));
    }

    /**
     * @test
     */
    public function getAsIntegerForBothZeroReturnsZero()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame(0, $subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerForBothNonZeroReturnsValueFromPrimary()
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerForPrimaryNonZeroAndSecondaryZeroReturnsValueFromPrimary()
    {
        $key = 'something';
        $primaryValue = 1;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 0;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($primaryValue, $subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerForPrimaryZeroAndSecondaryNonZeroReturnsValueFromSecondary()
    {
        $key = 'something';
        $primaryValue = 0;
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 2;
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame($secondaryValue, $subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForBothZeroReturnsFalse()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertFalse($subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForBothNonZeroReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 1]);
        $secondary = new DummyConfiguration([$key => 2]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForPrimaryNonZeroAndSecondaryZeroReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 1]);
        $secondary = new DummyConfiguration([$key => 0]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForPrimaryZeroAndSecondaryNonZeroReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => 0]);
        $secondary = new DummyConfiguration([$key => 2]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function getAsBooleanForBothFalseReturnsFalse()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => false]);
        $secondary = new DummyConfiguration([$key => false]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertFalse($subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanForBothTrueReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => true]);
        $secondary = new DummyConfiguration([$key => true]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanForPrimaryTrueAndSecondaryFalseReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => true]);
        $secondary = new DummyConfiguration([$key => false]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanForPrimaryFalseAndSecondaryTrueReturnsTrue()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => false]);
        $secondary = new DummyConfiguration([$key => true]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertTrue($subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayForBothEmptyArrayReturnsEmptyArray()
    {
        $key = 'something';
        $primary = new DummyConfiguration([$key => '']);
        $secondary = new DummyConfiguration([$key => '']);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([], $subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayForBothNonEmptyReturnsValueFromPrimary()
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayForPrimaryNonEmptyAndSecondaryEmptyReturnsValueFromPrimary()
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayForPrimaryEmptyAndSecondaryNonEmptyReturnsValueFromSecondary()
    {
        $key = 'something';
        $primaryValue = '';
        $primary = new DummyConfiguration([$key => $primaryValue]);
        $secondaryValue = 'secondary';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$secondaryValue], $subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayTrimsValues()
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => " ${primaryValue} "]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayExplodesValues()
    {
        $key = 'something';
        $primaryValue1 = 'primary 1';
        $primaryValue2 = 'primary 2';
        $primary = new DummyConfiguration([$key => "${primaryValue1}, ${primaryValue2}"]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue1, $primaryValue2], $subject->getAsTrimmedArray($key));
    }
}
