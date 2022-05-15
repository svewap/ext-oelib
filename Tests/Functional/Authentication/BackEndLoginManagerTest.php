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
     * @var non-empty-string[]
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

        $this->backEndUserMapper = MapperRegistry::get(BackEndUserMapper::class);

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
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser(): void
    {
        $this->logInBackEndUser();

        $result = $this->subject->getLoggedInUser();

        $userAuthentication = $this->getBackEndUserAuthentication();
        self::assertIsArray($userAuthentication->user);
        $expectedUid = (int)$userAuthentication->user['uid'];

        self::assertInstanceOf(BackEndUser::class, $result);
        self::assertSame($expectedUid, $result->getUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance(): void
    {
        $this->logInBackEndUser();

        $userAuthentication = $this->getBackEndUserAuthentication();
        self::assertIsArray($userAuthentication->user);
        $user = $this->backEndUserMapper->find($userAuthentication->user['uid']);
        self::assertInstanceOf(BackEndUser::class, $user);

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
