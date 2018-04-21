<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_MapperRegistryTest extends \Tx_Phpunit_TestCase
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
     */
    public function getForMalformedKeyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$className must be in the format tx_extensionname[_Folder]_ClassName, but was "foo".'
        );

        \Tx_Oelib_MapperRegistry::get('foo');
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
        self::assertInstanceOf(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)
        );
    }

    /**
     * @test
     */
    public function getForExistingClassWithExtbaseCapitalizationReturnsObjectOfRequestedClass()
    {
        self::assertInstanceOf(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)
        );
    }

    /**
     * @test
     */
    public function getForExistingClassWithAllLowercaseReturnsObjectOfRequestedClass()
    {
        self::assertInstanceOf(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
            \Tx_Oelib_MapperRegistry::get('tx_oelib_tests_unit_fixtures_testingmapper')
        );
    }

    /**
     * @test
     */
    public function getForExistingClassCalledTwoTimesReturnsTheSameInstance()
    {
        self::assertSame(
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class),
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)
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
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getAfterDenyDatabaseAccessReturnsExistingMapperInstanceWithDatabaseAccessDisabled()
    {
        \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class);
        \Tx_Oelib_MapperRegistry::denyDatabaseAccess();

        self::assertFalse(
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)->hasDatabaseAccess()
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
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function getWithActivatedTestingModeReturnsMapperWithTestingLayer()
    {
        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode(
            new \Tx_Oelib_TestingFramework('tx_oelib')
        );

        self::assertInstanceOf(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)
        );
    }

    /**
     * @test
     */
    public function getAfterInstanceWithActivatedTestingModeWasPurgedReturnsMapperWithoutTestingLayer()
    {
        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode(new \Tx_Oelib_TestingFramework('tx_oelib'));
        \Tx_Oelib_MapperRegistry::purgeInstance();

        self::assertNotInstanceOf(
            'Tx_Oelib_Tests_Unit_Fixtures_TestingMapperTesting',
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)
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
        $mapper = new \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper();
        \Tx_Oelib_MapperRegistry::set(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
            $mapper
        );

        self::assertSame(
            $mapper,
            \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class)
        );
    }

    /**
     * @test
     */
    public function getWithAllLowercaseReturnsMapperSetViaSetWithExtbaseCasing()
    {
        $className = \Tx_Oelib_AnotherTestingMapper::class;

        /** @var $mapper \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper */
        $mapper = $this->getMock(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class, [], [], $className);
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

        /** @var $mapper \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper */
        $mapper = $this->getMock(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class, [], [], $className);
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
        $mapper = new \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper();
        \Tx_Oelib_MapperRegistry::set(
            'Tx_Oelib_Mapper_Foo',
            $mapper
        );
    }

    /**
     * @test
     *
     * @expectedException \BadMethodCallException
     */
    public function setThrowsExceptionIfTheMapperTypeAlreadyIsRegistered()
    {
        \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class);

        $mapper = new \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper();
        \Tx_Oelib_MapperRegistry::set(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingMapper::class,
            $mapper
        );
    }
}
