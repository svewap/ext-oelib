<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Authentication;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Model\FrontEndUser;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Authentication\FrontEndLoginManager
 */
final class FrontEndLoginManagerTest extends UnitTestCase
{
    /**
     * @var FrontEndLoginManager
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = FrontEndLoginManager::getInstance();
    }

    protected function tearDown(): void
    {
        FrontEndLoginManager::purgeInstance();
        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsFrontEndLoginManagerInstance(): void
    {
        self::assertInstanceOf(
            FrontEndLoginManager::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            $this->subject,
            FrontEndLoginManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        FrontEndLoginManager::purgeInstance();

        self::assertNotSame(
            $this->subject,
            FrontEndLoginManager::getInstance()
        );
    }

    ///////////////////////////////
    // Tests concerning logInUser
    ///////////////////////////////

    /**
     * @test
     */
    public function logInUserChangesToLoggedInStatus(): void
    {
        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function logInUserWithNullSetsStatusToNotLoggedIn(): void
    {
        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        $this->subject->logInUser(null);

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserUidWithoutLoginReturnsZero(): void
    {
        self::assertSame(0, $this->subject->getLoggedInUserUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserUidWithLoginReturnsUserUid(): void
    {
        $uid = 12;
        $user = new FrontEndUser();
        $user->setUid($uid);

        $this->subject->logInUser($user);

        self::assertSame($uid, $this->subject->getLoggedInUserUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserUidWithLoginWithoutUIdReturnsZeo(): void
    {
        $user = new FrontEndUser();

        $this->subject->logInUser($user);

        self::assertSame(0, $this->subject->getLoggedInUserUid());
    }
}
