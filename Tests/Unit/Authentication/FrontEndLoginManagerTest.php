<?php

namespace OliverKlee\Oelib\Tests\Unit\Authentication;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndLoginManagerTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_FrontEndLoginManager
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = \Tx_Oelib_FrontEndLoginManager::getInstance();
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
            \Tx_Oelib_FrontEndLoginManager::class,
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
            \Tx_Oelib_FrontEndLoginManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        \Tx_Oelib_FrontEndLoginManager::purgeInstance();

        self::assertNotSame(
            $this->subject,
            \Tx_Oelib_FrontEndLoginManager::getInstance()
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
        $user = new \Tx_Oelib_Model_FrontEndUser();
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
        $user = new \Tx_Oelib_Model_FrontEndUser();
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
        $oldUser = new \Tx_Oelib_Model_FrontEndUser();
        $this->subject->logInUser($oldUser);
        $newUser = new \Tx_Oelib_Model_FrontEndUser();
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
        $user = new \Tx_Oelib_Model_FrontEndUser();
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
        $user = new \Tx_Oelib_Model_FrontEndUser();
        $this->subject->logInUser($user);

        $this->subject->logInUser(null);

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }
}
