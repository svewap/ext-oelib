<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;

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
            \Tx_Oelib_ConfigurationRegistry::class,
            \Tx_Oelib_ConfigurationRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            \Tx_Oelib_ConfigurationRegistry::getInstance(),
            \Tx_Oelib_ConfigurationRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = \Tx_Oelib_ConfigurationRegistry::getInstance();
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            \Tx_Oelib_ConfigurationRegistry::getInstance()
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

        \Tx_Oelib_ConfigurationRegistry::get('');
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

        \Tx_Oelib_ConfigurationRegistry::getInstance()->set(
            '',
            new \Tx_Oelib_Configuration()
        );
    }

    /**
     * @test
     */
    public function getAfterSetReturnsTheSetInstance()
    {
        $configuration = new \Tx_Oelib_Configuration();

        \Tx_Oelib_ConfigurationRegistry::getInstance()
            ->set('foo', $configuration);

        self::assertSame(
            $configuration,
            \Tx_Oelib_ConfigurationRegistry::get('foo')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setTwoTimesForTheSameNamespaceDoesNotFail()
    {
        \Tx_Oelib_ConfigurationRegistry::getInstance()->set(
            'foo',
            new \Tx_Oelib_Configuration()
        );
        \Tx_Oelib_ConfigurationRegistry::getInstance()->set(
            'foo',
            new \Tx_Oelib_Configuration()
        );
    }
}
