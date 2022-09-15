<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;

/**
 * @covers \OliverKlee\Oelib\Configuration\DummyConfiguration
 */
final class DummyConfigurationTest extends UnitTestCase
{
    /**
     * @var \OliverKlee\Oelib\Configuration\DummyConfiguration
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new DummyConfiguration();
    }

    /**
     * @test
     */
    public function implementsConfigurationInterface(): void
    {
        self::assertInstanceOf(ConfigurationInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function byDefaultHasDummySourceName(): void
    {
        $subject = new DummyConfiguration();

        self::assertSame('dummy configuration for testing', $subject->getSourceName());
    }

    /**
     * @test
     */
    public function canOverwriteSourceName(): void
    {
        $sourceName = 'set via setter';
        $subject = new DummyConfiguration();

        $subject->setSourceName($sourceName);

        self::assertSame($sourceName, $subject->getSourceName());
    }

    /**
     * @test
     */
    public function isObjectWithPublicAccessors(): void
    {
        self::assertInstanceOf(AbstractObjectWithPublicAccessors::class, $this->subject);
    }

    /**
     * @test
     */
    public function hasEmptyStringAsDefaultValueForInexistentString(): void
    {
        self::assertSame('', $this->subject->getAsString('nothing'));
    }

    /**
     * @test
     */
    public function hasZeroAsDefaultValueForInexistentInteger(): void
    {
        self::assertSame(0, $this->subject->getAsInteger('nothing'));
    }

    /**
     * @test
     */
    public function hasFalseAsDefaultValueForInexistentBoolean(): void
    {
        self::assertFalse($this->subject->getAsBoolean('nothing'));
    }

    /**
     * @test
     */
    public function canProvideDataViaConstructor(): void
    {
        $key = 'name';
        $value = 'Max';
        $subject = new DummyConfiguration([$key => $value]);

        self::assertSame($value, $subject->getAsString($key));
    }

    /**
     * @test
     */
    public function canGetString(): void
    {
        $key = 'name';
        $value = 'Max';
        $this->subject->setAllData([$key => $value]);

        self::assertSame($value, $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function canGetInteger(): void
    {
        $key = 'size';
        $value = 12;
        $this->subject->setAllData([$key => $value]);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function canGetBoolean(): void
    {
        $key = 'isActive';
        $this->subject->setAllData([$key => true]);

        self::assertTrue($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function canSetString(): void
    {
        $key = 'name';
        $value = 'Max';

        $this->subject->setAsString($key, $value);

        self::assertSame($value, $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function canSetInteger(): void
    {
        $key = 'size';
        $value = 12;

        $this->subject->setAsInteger($key, $value);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function canSetBoolean(): void
    {
        $key = 'isActive';
        $this->subject->setAsBoolean($key, true);

        self::assertTrue($this->subject->getAsBoolean($key));
    }
}
