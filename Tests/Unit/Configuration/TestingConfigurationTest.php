<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures\TestingConfiguration;

/**
 * Test case.
 *
 * @covers \OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures\TestingConfiguration
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TestingConfigurationTest extends UnitTestCase
{
    /**
     * @var TestingConfiguration
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new TestingConfiguration();
    }

    /**
     * @test
     */
    public function implementsConfigurationInterface()
    {
        self::assertInstanceOf(ConfigurationInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function isObjectWithPublicAccessors()
    {
        self::assertInstanceOf(AbstractObjectWithPublicAccessors::class, $this->subject);
    }

    /**
     * @test
     */
    public function canSaveString()
    {
        $key = 'name';
        $value = 'Max';

        $this->subject->setAsString($key, $value);

        self::assertSame($value, $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function canSaveInteger()
    {
        $key = 'size';
        $value = 12;

        $this->subject->setAsInteger($key, $value);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function canSaveBoolean()
    {
        $key = 'isActive';
        $value = true;

        $this->subject->setAsBoolean($key, $value);

        self::assertSame($value, $this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function hasDefaultValuesForInexistentFields()
    {
        self::assertSame('', $this->subject->getAsString('nothing'));
    }
}
