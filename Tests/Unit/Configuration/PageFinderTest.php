<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class PageFinderTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    /**
     * @var \Tx_Oelib_PageFinder
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = \Tx_Oelib_PageFinder::getInstance();
    }

    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsPageFinderInstance()
    {
        self::assertInstanceOf(
            \Tx_Oelib_PageFinder::class,
            \Tx_Oelib_PageFinder::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            \Tx_Oelib_PageFinder::getInstance(),
            \Tx_Oelib_PageFinder::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = \Tx_Oelib_PageFinder::getInstance();
        \Tx_Oelib_PageFinder::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            \Tx_Oelib_PageFinder::getInstance()
        );
    }

    ////////////////////////////////
    // tests concerning setPageUid
    ////////////////////////////////

    /**
     * @test
     */
    public function getPageUidWithSetPageUidViaSetPageUidReturnsSetPageUid()
    {
        $this->subject->setPageUid(42);

        self::assertSame(
            42,
            $this->subject->getPageUid()
        );
    }

    /**
     * @test
     */
    public function setPageUidWithZeroGivenThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given page UID was "0". Only integer values greater than zero are allowed.'
        );

        $this->subject->setPageUid(0);
    }

    /**
     * @test
     */
    public function setPageUidWithNegativeNumberGivenThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given page UID was "-21". Only integer values greater than zero are allowed.'
        );

        $this->subject->setPageUid(-21);
    }
}
