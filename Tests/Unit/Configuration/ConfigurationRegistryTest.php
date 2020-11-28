<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\Configuration;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationRegistryTest extends UnitTestCase
{
    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsConfigurationRegistryInstance()
    {
        self::assertInstanceOf(
            ConfigurationRegistry::class,
            ConfigurationRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            ConfigurationRegistry::getInstance(),
            ConfigurationRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = ConfigurationRegistry::getInstance();
        ConfigurationRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            ConfigurationRegistry::getInstance()
        );
    }

    ////////////////////////////////
    // Test concerning get and set
    ////////////////////////////////

    /**
     * @test
     */
    public function getForEmptyNamespaceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$namespace must not be empty.'
        );

        ConfigurationRegistry::get('');
    }

    /**
     * @test
     */
    public function setWithEmptyNamespaceThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$namespace must not be empty.'
        );

        ConfigurationRegistry::getInstance()->set(
            '',
            new Configuration()
        );
    }

    /**
     * @test
     */
    public function getAfterSetReturnsTheSetInstance()
    {
        $configuration = new Configuration();

        ConfigurationRegistry::getInstance()
            ->set('foo', $configuration);

        self::assertSame(
            $configuration,
            ConfigurationRegistry::get('foo')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setTwoTimesForTheSameNamespaceDoesNotFail()
    {
        ConfigurationRegistry::getInstance()->set(
            'foo',
            new Configuration()
        );
        ConfigurationRegistry::getInstance()->set(
            'foo',
            new Configuration()
        );
    }
}
