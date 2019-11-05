<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Session;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class SessionTest extends UnitTestCase
{
    protected function tearDown()
    {
        $GLOBALS['TSFE'] = null;
        parent::tearDown();
    }

    private function createFakeFrontEnd()
    {
        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();
    }

    /**
     * @test
     */
    public function getInstanceThrowsExceptionWithoutFrontEnd()
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
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
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'Only the types ::TYPE_USER and ::TYPE_TEMPORARY are allowed.'
        );

        $this->createFakeFrontEnd();

        \Tx_Oelib_Session::getInstance(42);
    }

    /**
     * @test
     */
    public function getInstanceWithUserTypeReturnsSessionInstance()
    {
        $this->createFakeFrontEnd();

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
        $this->createFakeFrontEnd();

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
        $this->createFakeFrontEnd();

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
        $this->createFakeFrontEnd();

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
        $this->createFakeFrontEnd();
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
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
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
