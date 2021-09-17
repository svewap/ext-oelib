<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;

class MapperRegistryTest extends UnitTestCase
{
    protected function tearDown(): void
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
    public function getInstanceReturnsMapperRegistryInstance(): void
    {
        self::assertInstanceOf(
            MapperRegistry::class,
            MapperRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            MapperRegistry::getInstance(),
            MapperRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
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
    public function getForEmptyKeyThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$className must not be empty.'
        );

        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        MapperRegistry::get('');
    }

    /**
     * @test
     */
    public function getForInexistentClassThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore-next-line We're testing a contract violation here on purpose.
        MapperRegistry::get(InexistentMapper::class);
    }

    /**
     * @test
     */
    public function getForExistingClassReturnsObjectOfRequestedClass(): void
    {
        self::assertInstanceOf(TestingMapper::class, MapperRegistry::get(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getForExistingClassCalledTwoTimesReturnsTheSameInstance(): void
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
    public function getAfterDenyDatabaseAccessReturnsNewMapperInstanceWithDatabaseAccessDisabled(): void
    {
        MapperRegistry::denyDatabaseAccess();

        self::assertFalse(
            MapperRegistry::get(TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled(): void
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
    public function getAfterInstanceWithDeniedDatabaseAccessWasPurgedReturnsMapperWithDatabaseAccessGranted(): void
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
    public function getReturnsMapperSetViaSet(): void
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
    public function setThrowsExceptionForMismatchingWrapperClass(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $mapper = new TestingMapper();
        MapperRegistry::set(TestingChildMapper::class, $mapper);
    }

    /**
     * @test
     */
    public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered(): void
    {
        $this->expectException(\BadMethodCallException::class);

        MapperRegistry::get(TestingMapper::class);

        $mapper = new TestingMapper();
        MapperRegistry::set(TestingMapper::class, $mapper);
    }
}
