<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndLoginManagerTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_BackEndLoginManager
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var \Tx_Oelib_Mapper_BackEndUser
     */
    private $backEndUserMapper = null;

    protected function setUp()
    {
        parent::setUp();

        $this->setUpBackendUserFromFixture(1);
        Bootstrap::getInstance()->initializeBackendAuthentication();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
        $this->backEndUserMapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_BackEndUser::class);

        $this->subject = \Tx_Oelib_BackEndLoginManager::getInstance();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
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

    ////////////////////////////////
    // Tests concerning isLoggedIn
    ////////////////////////////////

    /**
     * @test
     */
    public function isLoggedInWithLoggedInBackEndUserReturnsTrue()
    {
        self::assertTrue($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForFakedUserReturnsTrue()
    {
        /** @var \Tx_Oelib_Model_BackEndUser $ghostUser */
        $ghostUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($ghostUser);

        self::assertTrue($this->subject->isLoggedIn());
    }

    /////////////////////////////////////
    // Tests concerning getLoggedInUser
    /////////////////////////////////////

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserInstance()
    {
        self::assertInstanceOf(\Tx_Oelib_Model_BackEndUser::class, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel()
    {
        self::assertInstanceOf(TestingModel::class, $this->subject->getLoggedInUser(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser()
    {
        self::assertSame(
            (int)$this->getBackEndUserAuthentication()->user['uid'],
            $this->subject->getLoggedInUser()->getUid()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance()
    {
        /** @var \Tx_Oelib_Model_BackEndUser $user */
        $user = $this->backEndUserMapper->find($this->getBackEndUserAuthentication()->user['uid']);

        self::assertSame($user, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserUsesMappedUserDataFromMemory()
    {
        $this->getBackEndUserAuthentication()->user['realName'] = 'John Doe';

        self::assertSame('John Doe', $this->subject->getLoggedInUser()->getName());
    }

    ////////////////////////////////////
    // Tests concerning setLoggedInUser
    ////////////////////////////////////

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenSetsTheLoggedInUser()
    {
        /** @var \Tx_Oelib_Model_BackEndUser $backEndUser */
        $backEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($backEndUser);

        self::assertSame($backEndUser, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenAndAlreadyStoredLoggedInUserOverridesTheOldUserWithTheNewOne()
    {
        /** @var \Tx_Oelib_Model_BackEndUser $oldBackEndUser */
        $oldBackEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($oldBackEndUser);
        /** @var \Tx_Oelib_Model_BackEndUser $newBackEndUser */
        $newBackEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($newBackEndUser);

        self::assertSame($newBackEndUser, $this->subject->getLoggedInUser());
    }
}
