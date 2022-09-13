<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ExtbaseConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;

/**
 * @covers \OliverKlee\Oelib\Configuration\ExtbaseConfiguration
 */
final class ExtbaseConfigurationTest extends UnitTestCase
{
    /**
     * @var \OliverKlee\Oelib\Configuration\ExtbaseConfiguration
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ExtbaseConfiguration();
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
        $subject = new ExtbaseConfiguration();

        self::assertSame('in the plugin Flexforms or in your TypoScript template', $subject->getSourceName());
    }

    /**
     * @test
     */
    public function isReadOnlyObjectWithPublicAccessors(): void
    {
        self::assertInstanceOf(AbstractReadOnlyObjectWithPublicAccessors::class, $this->subject);
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
    public function canGetString(): void
    {
        $key = 'name';
        $value = 'Max';
        $subject = new ExtbaseConfiguration([$key => $value]);

        self::assertSame($value, $subject->getAsString($key));
    }

    /**
     * @test
     */
    public function canGetInteger(): void
    {
        $key = 'size';
        $value = 12;
        $subject = new ExtbaseConfiguration([$key => $value]);

        self::assertSame($value, $subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function canGetBoolean(): void
    {
        $key = 'isActive';
        $subject = new ExtbaseConfiguration([$key => true]);

        self::assertTrue($subject->getAsBoolean($key));
    }
}
