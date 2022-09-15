<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\PageFinder;
use OliverKlee\Oelib\Testing\TestingFramework;

/**
 * @covers \OliverKlee\Oelib\Configuration\PageFinder
 */
class PageFinderTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var PageFinder
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');

        $this->subject = PageFinder::getInstance();
    }

    protected function tearDown(): void
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
    public function getPageUidWithFrontEndPageUidReturnsFrontEndPageUid(): void
    {
        $frontEndPageUid = $this->testingFramework->createFrontEndPage();
        $this->testingFramework->createFakeFrontEnd($frontEndPageUid);

        self::assertSame($frontEndPageUid, $this->subject->getPageUid());
    }

    /**
     * @test
     */
    public function getPageUidWithoutFrontEndAndWithBackendPageUidReturnsBackEndPageUid(): void
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
    public function getPageUidWithFrontEndAndBackendPageUidReturnsFrontEndPageUid(): void
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
    public function getPageUidForManuallySetPageUidAndSetFrontEndPageUidReturnsManuallySetPageUid(): void
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
    public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidReturnsFrontEndPageUid(): void
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
    public function forceSourceWithSourceSetToBackEndAndSetFrontEndUidReturnsBackEndEndPageUid(): void
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
    public function forceSourceWithSourceSetToFrontEndAndManuallySetPageUidButNoFrontEndUidSetReturnsZero(): void
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
    public function getCurrentSourceForNoSourceForcedAndNoPageUidSetReturnsNoSourceFound(): void
    {
        self::assertSame(
            PageFinder::NO_SOURCE_FOUND,
            $this->subject->getCurrentSource()
        );
    }

    /**
     * @test
     */
    public function getCurrentSourceForSourceForcedToFrontEndReturnsSourceFrontEnd(): void
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
    public function getCurrentSourceForSourceForcedToBackEndReturnsSourceBackEnd(): void
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
    public function getCurrentSourceForManuallySetPageIdReturnsSourceManual(): void
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
    public function getCurrentSourceForSetFrontEndPageUidReturnsSourceFrontEnd(): void
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
    public function getCurrentSourceForSetBackEndPageUidReturnsSourceBackEnd(): void
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
