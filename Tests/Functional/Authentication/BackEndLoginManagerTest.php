<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\BackEndLoginManager;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

class BackEndLoginManagerTest extends FunctionalTestCase
{
    /**
     * @var string[]
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

    protected function setUp()
    {
        parent::setUp();

        $this->backEndUserMapper = MapperRegistry::get(BackEndUserMapper::class);

        $this->subject = BackEndLoginManager::getInstance();
    }

    /**
     * @return void
     *
     * @throws NimutException
     */
    private function logInBackEndUser()
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
    public function isLoggedInWithoutLoggedInBackEndUserReturnsFalse()
    {
        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInWithLoggedInBackEndUserReturnsTrue()
    {
        $this->logInBackEndUser();

        self::assertTrue($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForFakedUserReturnsTrue()
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
    public function getLoggedInUserWithoutLoggedInUserReturnsNull()
    {
        self::assertNull($this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserInstance()
    {
        $this->logInBackEndUser();

        self::assertInstanceOf(BackEndUser::class, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel()
    {
        $this->logInBackEndUser();

        $result = $this->subject->getLoggedInUser(TestingMapper::class);

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser()
    {
        $this->logInBackEndUser();

        $result = $this->subject->getLoggedInUser();

        self::assertSame((int)$this->getBackEndUserAuthentication()->user['uid'], $result->getUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance()
    {
        $this->logInBackEndUser();

        /** @var BackEndUser $user */
        $user = $this->backEndUserMapper->find($this->getBackEndUserAuthentication()->user['uid']);

        self::assertSame($user, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserUsesMappedUserDataFromMemory()
    {
        $this->logInBackEndUser();

        $name = 'John Doe';
        $this->getBackEndUserAuthentication()->user['realName'] = $name;

        self::assertSame($name, $this->subject->getLoggedInUser()->getName());
    }

    // Tests concerning setLoggedInUser

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenSetsTheLoggedInUser()
    {
        /** @var BackEndUser $backEndUser */
        $backEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($backEndUser);

        self::assertSame($backEndUser, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenAndAlreadyStoredLoggedInUserOverridesTheOldUserWithTheNewOne()
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
