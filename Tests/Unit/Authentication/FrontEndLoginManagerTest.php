<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Authentication;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndLoginManagerTest extends UnitTestCase
{
    /**
     * @var FrontEndLoginManager
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = FrontEndLoginManager::getInstance();
    }

    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsFrontEndLoginManagerInstance()
    {
        self::assertInstanceOf(
            FrontEndLoginManager::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            $this->subject,
            FrontEndLoginManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
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
    public function logInUserChangesToLoggedInStatus()
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
    public function logInUserSetsLoggedInUser()
    {
        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        self::assertSame(
            $user,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function logInUserOverwritesFormerSimulatedLoggedInUser()
    {
        $oldUser = new FrontEndUser();
        $this->subject->logInUser($oldUser);
        $newUser = new FrontEndUser();
        $this->subject->logInUser($newUser);

        self::assertSame(
            $newUser,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function logInUserWithNullSetsUserToNull()
    {
        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        $this->subject->logInUser(null);

        self::assertNull(
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function logInUserWithNullSetsStatusToNotLoggedIn()
    {
        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        $this->subject->logInUser(null);

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }
}
