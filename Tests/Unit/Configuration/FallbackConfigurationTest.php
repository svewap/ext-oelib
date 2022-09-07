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
    public function implementsConfiguration(): void
    {
        $subject = new FallbackConfiguration(new DummyConfiguration(), new DummyConfiguration());

        self::assertInstanceOf(Configuration::class, $subject);
    }

    /**
     * @test
     */
    public function hasSourceNameFromBothConfigurations(): void
    {
        $primarySourceName = 'primary';
        $primary = new DummyConfiguration();
        $primary->setSourceName($primarySourceName);
        $secondarySourceName = 'secondary';
        $secondary = new DummyConfiguration();
        $secondary->setSourceName($secondarySourceName);

        $subject = new FallbackConfiguration($primary, $secondary);

        $expected = $primarySourceName . ' or ' . $secondarySourceName;
        self::assertSame($expected, $subject->getSourceName());
    }

    /**
     * @test
     */
    public function getAsStringForBothEmptyStringReturnsEmptyString(): void
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
    public function getAsStringForBothNonEmptyReturnsValueFromPrimary(): void
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
    public function getAsStringForPrimaryNonEmptyAndSecondaryEmptyReturnsValueFromPrimary(): void
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
    public function getAsStringForPrimaryEmptyAndSecondaryNonEmptyReturnsValueFromSecondary(): void
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
    public function hasStringForBothEmptyStringReturnsFalse(): void
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
    public function hasStringForBothNonEmptyReturnsTrue(): void
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
    public function hasStringForPrimaryNonEmptyAndSecondaryEmptyReturnsTrue(): void
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
    public function hasStringForPrimaryEmptyAndSecondaryNonEmptyReturnsTrue(): void
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
    public function getAsIntegerForBothZeroReturnsZero(): void
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
    public function getAsIntegerForBothNonZeroReturnsValueFromPrimary(): void
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
    public function getAsIntegerForPrimaryNonZeroAndSecondaryZeroReturnsValueFromPrimary(): void
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
    public function getAsIntegerForPrimaryZeroAndSecondaryNonZeroReturnsValueFromSecondary(): void
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
    public function hasIntegerForBothZeroReturnsFalse(): void
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
    public function hasIntegerForBothNonZeroReturnsTrue(): void
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
    public function hasIntegerForPrimaryNonZeroAndSecondaryZeroReturnsTrue(): void
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
    public function hasIntegerForPrimaryZeroAndSecondaryNonZeroReturnsTrue(): void
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
    public function getAsBooleanForBothFalseReturnsFalse(): void
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
    public function getAsBooleanForBothTrueReturnsTrue(): void
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
    public function getAsBooleanForPrimaryTrueAndSecondaryFalseReturnsTrue(): void
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
    public function getAsBooleanForPrimaryFalseAndSecondaryTrueReturnsTrue(): void
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
    public function getAsTrimmedArrayForBothEmptyArrayReturnsEmptyArray(): void
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
    public function getAsTrimmedArrayForBothNonEmptyReturnsValueFromPrimary(): void
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
    public function getAsTrimmedArrayForPrimaryNonEmptyAndSecondaryEmptyReturnsValueFromPrimary(): void
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
    public function getAsTrimmedArrayForPrimaryEmptyAndSecondaryNonEmptyReturnsValueFromSecondary(): void
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
    public function getAsTrimmedArrayTrimsValues(): void
    {
        $key = 'something';
        $primaryValue = 'primary';
        $primary = new DummyConfiguration([$key => " $primaryValue "]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue], $subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayExplodesValues(): void
    {
        $key = 'something';
        $primaryValue1 = 'primary 1';
        $primaryValue2 = 'primary 2';
        $primary = new DummyConfiguration([$key => "$primaryValue1, $primaryValue2"]);
        $secondaryValue = '';
        $secondary = new DummyConfiguration([$key => $secondaryValue]);
        $subject = new FallbackConfiguration($primary, $secondary);

        self::assertSame([$primaryValue1, $primaryValue2], $subject->getAsTrimmedArray($key));
    }
}
