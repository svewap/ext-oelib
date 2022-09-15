<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Authentication;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Model\BackEndUser;

/**
 * @covers \OliverKlee\Oelib\Authentication\BackEndLoginManager
 */
final class BackEndLoginManagerTest extends UnitTestCase
{
    /**
     * @var BackEndLoginManager
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = BackEndLoginManager::getInstance();
    }

    /**
     * @test
     */
    public function getInstanceReturnsBackEndLoginManagerInstance(): void
    {
        self::assertInstanceOf(BackEndLoginManager::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame($this->subject, BackEndLoginManager::getInstance());
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        BackEndLoginManager::purgeInstance();

        self::assertNotSame($this->subject, BackEndLoginManager::getInstance());
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
        $user = new BackEndUser();
        $user->setUid($uid);

        $this->subject->setLoggedInUser($user);

        self::assertSame($uid, $this->subject->getLoggedInUserUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserUidWithLoginWithoutUIdReturnsZeo(): void
    {
        $user = new BackEndUser();

        $this->subject->setLoggedInUser($user);

        self::assertSame(0, $this->subject->getLoggedInUserUid());
    }
}
