<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MapperRegistryTest extends UnitTestCase
{
    protected function tearDown()
    {
        \Tx_Oelib_MapperRegistry::purgeInstance();
        parent::tearDown();
    }

    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsMapperRegistryInstance()
    {
        self::assertInstanceOf(
            \Tx_Oelib_MapperRegistry::class,
            \Tx_Oelib_MapperRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            \Tx_Oelib_MapperRegistry::getInstance(),
            \Tx_Oelib_MapperRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = \Tx_Oelib_MapperRegistry::getInstance();
        \Tx_Oelib_MapperRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            \Tx_Oelib_MapperRegistry::getInstance()
        );
    }

    ////////////////////////////////////////
    // Test concerning get and setMappings
    ////////////////////////////////////////

    /**
     * @test
     */
    public function getForEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$className must not be empty.'
        );

        \Tx_Oelib_MapperRegistry::get('');
    }

    /**
     * @test
     */
    public function getForInexistentClassThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_MapperRegistry::get('Tx_Oelib_InexistentMapper');
    }

    /**
     * @test
     */
    public function getForExistingClassReturnsObjectOfRequestedClass()
    {
        self::assertInstanceOf(TestingMapper::class, \Tx_Oelib_MapperRegistry::get(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getForExistingClassCalledTwoTimesReturnsTheSameInstance()
    {
        self::assertSame(
            \Tx_Oelib_MapperRegistry::get(TestingMapper::class),
            \Tx_Oelib_MapperRegistry::get(TestingMapper::class)
        );
    }

    ////////////////////////////////////////////
    // Tests concerning denied database access
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getAfterDenyDatabaseAccessReturnsNewMapperInstanceWithDatabaseAccessDisabled()
    {
        \Tx_Oelib_MapperRegistry::denyDatabaseAccess();

        self::assertFalse(
            \Tx_Oelib_MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled()
    {
        \Tx_Oelib_MapperRegistry::get(TestingMapper::class);
        \Tx_Oelib_MapperRegistry::denyDatabaseAccess();

        self::assertFalse(
            \Tx_Oelib_MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getAfterInstanceWithDeniedDatabaseAccessWasPurgedReturnsMapperWithDatabaseAccessGranted()
    {
        \Tx_Oelib_MapperRegistry::getInstance();
        \Tx_Oelib_MapperRegistry::denyDatabaseAccess();
        \Tx_Oelib_MapperRegistry::purgeInstance();

        self::assertTrue(
            \Tx_Oelib_MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /////////////////////////
    // Tests concerning set
    /////////////////////////

    /**
     * @test
     */
    public function getReturnsMapperSetViaSet()
    {
        $mapper = new TestingMapper();
        \Tx_Oelib_MapperRegistry::set(
            TestingMapper::class,
            $mapper
        );

        self::assertSame(
            $mapper,
            \Tx_Oelib_MapperRegistry::get(TestingMapper::class)
        );
    }

    /**
     * @test
     */
    public function setThrowsExceptionForMismatchingWrapperClass()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new TestingMapper();
        \Tx_Oelib_MapperRegistry::set(TestingChildMapper::class, $mapper);
    }

    /**
     * @test
     */
    public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_MapperRegistry::get(TestingMapper::class);

        $mapper = new TestingMapper();
        \Tx_Oelib_MapperRegistry::set(TestingMapper::class, $mapper);
    }
}
