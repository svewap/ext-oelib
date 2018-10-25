<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_SessionTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework for creating a fake front end
     */
    private $testingFramework;

    protected function setUp()
    {
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /////////////////////////////////////////////////////////
    // Tests for setting and getting the Singleton instance
    /////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceThrowsExceptionWithoutFrontEnd()
    {
        $this->setExpectedException(
            'BadMethodCallException',
            'This class must not be instantiated when there is no front end.'
        );

        $GLOBALS['TSFE'] = null;

        \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER);
    }

    /**
     * @test
     */
    public function getInstanceWithInvalidTypeThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
        );

        $this->testingFramework->createFakeFrontEnd();

        \Tx_Oelib_Session::getInstance(42);
    }

    /**
     * @test
     */
    public function getInstanceWithUserTypeReturnsSessionInstance()
    {
        $this->testingFramework->createFakeFrontEnd();

        self::assertInstanceOf(
            \Tx_Oelib_Session::class,
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER)
        );
    }

    /**
     * @test
     */
    public function getInstanceWithTemporaryTypeReturnsSessionInstance()
    {
        $this->testingFramework->createFakeFrontEnd();

        self::assertInstanceOf(
            \Tx_Oelib_Session::class,
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_TEMPORARY)
        );
    }

    /**
     * @test
     */
    public function getInstanceWithSameTypeReturnsSameInstance()
    {
        $this->testingFramework->createFakeFrontEnd();

        self::assertSame(
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER),
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER)
        );
    }

    /**
     * @test
     */
    public function getInstanceWithDifferentTypesReturnsDifferentInstance()
    {
        $this->testingFramework->createFakeFrontEnd();

        self::assertNotSame(
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER),
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_TEMPORARY)
        );
    }

    /**
     * @test
     */
    public function getInstanceWithSameTypesAfterPurgeInstancesReturnsNewInstance()
    {
        $this->testingFramework->createFakeFrontEnd();
        $firstInstance = \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER);
        \Tx_Oelib_Session::purgeInstances();

        self::assertNotSame(
            $firstInstance,
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER)
        );
    }

    /**
     * @test
     */
    public function setInstanceWithInvalidTypeThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
        );

        \Tx_Oelib_Session::setInstance(42, new \Tx_Oelib_FakeSession());
    }

    /**
     * @test
     */
    public function getInstanceWithUserTypeReturnsInstanceFromSetInstance()
    {
        $instance = new \Tx_Oelib_FakeSession();
        \Tx_Oelib_Session::setInstance(\Tx_Oelib_Session::TYPE_USER, $instance);

        self::assertSame(
            $instance,
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER)
        );
    }

    /**
     * @test
     */
    public function getInstanceWithTemporaryTypeReturnsInstanceFromSetInstance()
    {
        $instance = new \Tx_Oelib_FakeSession();
        \Tx_Oelib_Session::setInstance(
            \Tx_Oelib_Session::TYPE_TEMPORARY,
            $instance
        );

        self::assertSame(
            $instance,
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_TEMPORARY)
        );
    }

    /**
     * @test
     */
    public function getInstanceWithDifferentTypesReturnsDifferentInstancesSetViaSetInstance()
    {
        \Tx_Oelib_Session::setInstance(
            \Tx_Oelib_Session::TYPE_USER,
            new \Tx_Oelib_FakeSession()
        );
        \Tx_Oelib_Session::setInstance(
            \Tx_Oelib_Session::TYPE_TEMPORARY,
            new \Tx_Oelib_FakeSession()
        );

        self::assertNotSame(
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_USER),
            \Tx_Oelib_Session::getInstance(\Tx_Oelib_Session::TYPE_TEMPORARY)
        );
    }
}
