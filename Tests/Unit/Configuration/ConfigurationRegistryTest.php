<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;

/**
 * @covers \OliverKlee\Oelib\Configuration\ConfigurationRegistry
 */
final class ConfigurationRegistryTest extends UnitTestCase
{
    // Tests concerning the Singleton property

    /**
     * @test
     */
    public function getInstanceReturnsConfigurationRegistryInstance(): void
    {
        self::assertInstanceOf(ConfigurationRegistry::class, ConfigurationRegistry::getInstance());
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            ConfigurationRegistry::getInstance(),
            ConfigurationRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        $firstInstance = ConfigurationRegistry::getInstance();
        ConfigurationRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            ConfigurationRegistry::getInstance()
        );
    }

    // Test concerning get and set

    /**
     * @test
     */
    public function getForEmptyNamespaceThrowsException(): void
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
    public function setWithEmptyNamespaceThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$namespace must not be empty.'
        );

        ConfigurationRegistry::getInstance()->set('', new DummyConfiguration());
    }

    /**
     * @test
     */
    public function getAfterSetWithTypoScriptConfigurationReturnsTheSetInstance(): void
    {
        $configuration = new TypoScriptConfiguration();

        ConfigurationRegistry::getInstance()->set('foo', $configuration);

        self::assertSame($configuration, ConfigurationRegistry::get('foo'));
    }

    /**
     * @test
     */
    public function getAfterSetWithDummyConfigurationReturnsTheSetInstance(): void
    {
        $configuration = new DummyConfiguration();

        ConfigurationRegistry::getInstance()->set('foo', $configuration);

        self::assertSame($configuration, ConfigurationRegistry::get('foo'));
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setTwoTimesForTheSameNamespaceDoesNotFail(): void
    {
        ConfigurationRegistry::getInstance()->set('foo', new DummyConfiguration());
        ConfigurationRegistry::getInstance()->set('foo', new DummyConfiguration());
    }
}
