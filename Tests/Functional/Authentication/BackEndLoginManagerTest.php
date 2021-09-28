<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * @covers \OliverKlee\Oelib\Authentication\BackEndLoginManager
 */
final class BackEndLoginManagerTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
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

        /** @var BackEndUserMapper $backEndUserMapper */
        $backEndUserMapper = MapperRegistry::get(BackEndUserMapper::class);
        $this->backEndUserMapper = $backEndUserMapper;

        $this->subject = BackEndLoginManager::getInstance();
    }

    /**
     * @throws NimutException
     */
    private function logInBackEndUser(): void
    {
        $this->setUpBackendUserFromFixture(1);
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     *
     * @return BackendUserAuthentication
     */
    private function getBackEndUserAuthentication(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
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

    // Tests concerning getLoggedInUser

    /**
     * @test
     */
    public function getLoggedInUserWithoutLoggedInUserReturnsNull(): void
    {
        self::assertNull($this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserInstance(): void
    {
        $this->logInBackEndUser();

        self::assertInstanceOf(BackEndUser::class, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser(): void
    {
        $this->logInBackEndUser();

        $result = $this->subject->getLoggedInUser();

        self::assertSame((int)$this->getBackEndUserAuthentication()->user['uid'], $result->getUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance(): void
    {
        $this->logInBackEndUser();

        /** @var BackEndUser $user */
        $user = $this->backEndUserMapper->find($this->getBackEndUserAuthentication()->user['uid']);

        self::assertSame($user, $this->subject->getLoggedInUser());
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

        self::assertSame($backEndUser, $this->subject->getLoggedInUser());
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

        self::assertSame($newBackEndUser, $this->subject->getLoggedInUser());
    }
}
