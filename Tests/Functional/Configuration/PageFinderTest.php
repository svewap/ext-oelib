<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\PageFinder;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class PageFinderTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var PageFinder
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = PageFinder::getInstance();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    ////////////////////////////////
    // Tests concerning getPageUid
    ////////////////////////////////

    /**
     * @test
     */
    public function getPageUidWithFrontEndPageUidReturnsFrontEndPageUid()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        self::assertSame($frontEndPageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function getPageUidWithoutFrontEndAndWithBackendPageUidReturnsBackEndPageUid()
    {
        $_POST['id'] = 42;

        $pageUid = $this->subject->getPageUid();
        unset($_POST['id']);

        self::assertSame(
            42,
            $pageUid
        );
    }

    /**
     * @test
     */
    public function getPageUidWithFrontEndAndBackendPageUidReturnsFrontEndPageUid()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);
        $_POST['id'] = $frontEndPageUid + 1;

        $pageUid = $this->subject->getPageUid();

        unset($_POST['id']);

        self::assertSame(
            $frontEndPageUid,
            $pageUid
        );
    }

    /**
     * @test
     */
    public function getPageUidForManuallySetPageUidAndSetFrontEndPageUidReturnsManuallySetPageUid()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);
        $this->subject->setPageUid($frontEndPageUid + 1);

        self::assertSame(
            $frontEndPageUid + 1,
            $this->subject->getPageUid()
        );
    }

    /////////////////////////////////
    // Tests concerning forceSource
    /////////////////////////////////

    /**
     * @test
     */
    public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidReturnsFrontEndPageUid()
    {
        $this->subject->forceSource(PageFinder::SOURCE_FRONT_END);
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        $this->subject->setPageUid($frontEndPageUid + 1);

        self::assertSame(
            $frontEndPageUid,
            $this->subject->getPageUid()
        );
    }

    /**
     * @test
     */
    public function forceSourceWithSourceSetToBackEndAndSetFrontEndUidReturnsBackEndEndPageUid()
    {
        $this->subject->forceSource(PageFinder::SOURCE_BACK_END);
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        $_POST['id'] = $frontEndPageUid + 1;
        $pageUid = $this->subject->getPageUid();
        unset($_POST['id']);

        self::assertSame($frontEndPageUid + 1, $pageUid);
    }

    /**
     * @test
     */
    public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidButNoFrontEndUidSetReturnsZero()
    {
        $this->subject->forceSource(PageFinder::SOURCE_FRONT_END);

        $this->subject->setPageUid(15);

        self::assertSame(
            0,
            $this->subject->getPageUid()
        );
    }

    //////////////////////////////////////
    // Tests concerning getCurrentSource
    //////////////////////////////////////

    /**
     * @test
     */
    public function getCurrentSourceForNoSourceForcedAndNoPageUidSetReturnsNoSourceFound()
    {
        self::assertSame(
            PageFinder::NO_SOURCE_FOUND,
            $this->subject->getCurrentSource()
        );
    }

    /**
     * @test
     */
    public function getCurrentSourceForSourceForcedToFrontEndReturnsSourceFrontEnd()
    {
        $this->subject->forceSource(PageFinder::SOURCE_FRONT_END);

        self::assertSame(
            PageFinder::SOURCE_FRONT_END,
            $this->subject->getCurrentSource()
        );
    }

    /**
     * @test
     */
    public function getCurrentSourceForSourceForcedToBackEndReturnsSourceBackEnd()
    {
        $this->subject->forceSource(PageFinder::SOURCE_BACK_END);

        self::assertSame(
            PageFinder::SOURCE_BACK_END,
            $this->subject->getCurrentSource()
        );
    }

    /**
     * @test
     */
    public function getCurrentSourceForManuallySetPageIdReturnsSourceManual()
    {
        $this->subject->setPageUid(42);

        self::assertSame(
            PageFinder::SOURCE_MANUAL,
            $this->subject->getCurrentSource()
        );
    }

    /**
     * @test
     */
    public function getCurrentSourceForSetFrontEndPageUidReturnsSourceFrontEnd()
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        self::assertSame(
            PageFinder::SOURCE_FRONT_END,
            $this->subject->getCurrentSource()
        );
    }

    /**
     * @test
     */
    public function getCurrentSourceForSetBackEndPageUidReturnsSourceBackEnd()
    {
        $_POST['id'] = 42;
        $pageSource = $this->subject->getCurrentSource();
        unset($_POST['id']);

        self::assertSame(
            PageFinder::SOURCE_BACK_END,
            $pageSource
        );
    }
}
