<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Authentication\BackEndLoginManager
 */
final class BackEndLoginManagerTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var BackEndLoginManager
     */
    private $subject = null;

    /**
     * @var BackEndUserMapper
     */
    private $backEndUserMapper = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backEndUserMapper = MapperRegistry::get(BackEndUserMapper::class);

        $this->subject = BackEndLoginManager::getInstance();
    }

    private function logInBackEndUser(): void
    {
        $this->setUpBackendUserFromFixture(1);
    }

    // Tests concerning isLoggedIn

    /**
     * @test
     */
    public function isLoggedInWithoutLoggedInBackEndUserReturnsFalse(): void
    {
        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInWithLoggedInBackEndUserReturnsTrue(): void
    {
        $this->logInBackEndUser();

        self::assertTrue($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForFakedUserReturnsTrue(): void
    {
        /** @var BackEndUser $ghostUser */
        $ghostUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($ghostUser);

        self::assertTrue($this->subject->isLoggedIn());
    }

    // Tests concerning setLoggedInUser

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenSetsTheLoggedInUser(): void
    {
        /** @var BackEndUser $backEndUser */
        $backEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($backEndUser);

        self::assertSame($backEndUser->getUid(), $this->subject->getLoggedInUserUid());
    }

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenAndAlreadyStoredLoggedInUserOverridesTheOldUserWithTheNewOne(): void
    {
        /** @var BackEndUser $oldBackEndUser */
        $oldBackEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($oldBackEndUser);

        /** @var BackEndUser $newBackEndUser */
        $newBackEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($newBackEndUser);

        self::assertSame($newBackEndUser->getUid(), $this->subject->getLoggedInUserUid());
    }
}
