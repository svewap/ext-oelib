<?php

use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_MapperRegistryTest extends \Tx_Phpunit_TestCase
{
    protected function tearDown()
    {
        \Tx_Oelib_MapperRegistry::purgeInstance();
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
        $this->setExpectedException(
            'InvalidArgumentException',
            '$className must not be empty.'
        );

        \Tx_Oelib_MapperRegistry::get('');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function getForInexistentClassThrowsException()
    {
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
    public function getWithAllLowercaseReturnsMapperSetViaSetWithExtbaseCasing()
    {
        $className = \Tx_Oelib_AnotherTestingMapper::class;

        /** @var $mapper TestingMapper $mapper */
        $mapper = $this->getMock(TestingMapper::class, [], [], $className);
        \Tx_Oelib_MapperRegistry::set($className, $mapper);

        self::assertSame(
            $mapper,
            \Tx_Oelib_MapperRegistry::get(strtolower($className))
        );
    }

    /**
     * @test
     */
    public function getWithExtbaseCasingReturnsMapperSetViaSetWithAllLowercase()
    {
        $className = \Tx_Oelib_AnotherTestingMapper::class;

        /** @var TestingMapper $mapper */
        $mapper = $this->getMock(TestingMapper::class, [], [], $className);
        \Tx_Oelib_MapperRegistry::set(strtolower($className), $mapper);

        self::assertSame(
            $mapper,
            \Tx_Oelib_MapperRegistry::get($className)
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function setThrowsExceptionForMismatchingWrapperClass()
    {
        $mapper = new TestingMapper();
        \Tx_Oelib_MapperRegistry::set('Tx_Oelib_Mapper_Foo', $mapper);
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered()
    {
        \Tx_Oelib_MapperRegistry::get(TestingMapper::class);

        $mapper = new TestingMapper();
        \Tx_Oelib_MapperRegistry::set(TestingMapper::class, $mapper);
    }
}
