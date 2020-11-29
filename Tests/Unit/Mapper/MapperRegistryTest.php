<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\MapperRegistry;
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
        MapperRegistry::purgeInstance();
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
            MapperRegistry::class,
            MapperRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            MapperRegistry::getInstance(),
            MapperRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = MapperRegistry::getInstance();
        MapperRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            MapperRegistry::getInstance()
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

        MapperRegistry::get('');
    }

    /**
     * @test
     */
    public function getForInexistentClassThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        MapperRegistry::get(InexistentMapper::class);
    }

    /**
     * @test
     */
    public function getForExistingClassReturnsObjectOfRequestedClass()
    {
        self::assertInstanceOf(TestingMapper::class, MapperRegistry::get(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getForExistingClassCalledTwoTimesReturnsTheSameInstance()
    {
        self::assertSame(
            MapperRegistry::get(TestingMapper::class),
            MapperRegistry::get(TestingMapper::class)
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
        MapperRegistry::denyDatabaseAccess();

        self::assertFalse(
            MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled()
    {
        MapperRegistry::get(TestingMapper::class);
        MapperRegistry::denyDatabaseAccess();

        self::assertFalse(
            MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getAfterInstanceWithDeniedDatabaseAccessWasPurgedReturnsMapperWithDatabaseAccessGranted()
    {
        MapperRegistry::getInstance();
        MapperRegistry::denyDatabaseAccess();
        MapperRegistry::purgeInstance();

        self::assertTrue(
            MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
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
        MapperRegistry::set(
            TestingMapper::class,
            $mapper
        );

        self::assertSame(
            $mapper,
            MapperRegistry::get(TestingMapper::class)
        );
    }

    /**
     * @test
     */
    public function setThrowsExceptionForMismatchingWrapperClass()
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new TestingMapper();
        MapperRegistry::set(TestingChildMapper::class, $mapper);
    }

    /**
     * @test
     */
    public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered()
    {
        $this->expectException(\BadMethodCallException::class);

        MapperRegistry::get(TestingMapper::class);

        $mapper = new TestingMapper();
        MapperRegistry::set(TestingMapper::class, $mapper);
    }
}
