<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\PageFinder;

/**
 * Test case.
 */
class PageFinderTest extends UnitTestCase
{
    /**
     * @var PageFinder
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = PageFinder::getInstance();
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
            PageFinder::class,
            PageFinder::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            PageFinder::getInstance(),
            PageFinder::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = PageFinder::getInstance();
        PageFinder::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            PageFinder::getInstance()
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
