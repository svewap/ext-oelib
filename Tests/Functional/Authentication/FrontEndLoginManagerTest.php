<?php

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndLoginManagerTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_FrontEndLoginManager
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');

        $this->subject = \Tx_Oelib_FrontEndLoginManager::getInstance();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    ////////////////////////////////
    // Tests concerning isLoggedIn
    ////////////////////////////////

    /**
     * @test
     */
    public function isLoggedInForNoFrontEndReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function isLoggedInForFrontEndWithoutLoggedInUserReturnsFalse()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function isLoggedInForAnonymousFrontEndSessionReturnsFalse()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        /** @var TypoScriptFrontendController $frontEndController */
        $frontEndController = $GLOBALS['TSFE'];
        $frontEndController->fe_user->setAndSaveSessionData('oelib_test', 1);

        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInWithLoggedInFrontEndUserReturnsTrue()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /////////////////////////////////////
    // Tests concerning getLoggedInUser
    /////////////////////////////////////

    /**
     * @test
     */
    public function getLoggedInUserWithEmptyMapperNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$mapperName must not be empty.'
        );

        $this->subject->getLoggedInUser('');
    }

    /**
     * @test
     */
    public function getLoggedInUserWithoutFrontEndReturnsNull()
    {
        $this->testingFramework->discardFakeFrontEnd();

        self::assertNull(
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithoutLoggedInUserReturnsNull()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->logoutFrontEndUser();

        self::assertNull(
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserInstance()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertInstanceOf(
            \Tx_Oelib_Model_FrontEndUser::class,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertInstanceOf(TestingModel::class, $this->subject->getLoggedInUser(TestingMapper::class));
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserWithUidOfLoggedInUser()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $uid = $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame(
            $uid,
            $this->subject->getLoggedInUser()->getUid()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $uid = $this->testingFramework->createAndLoginFrontEndUser();
        /** @var \Tx_Oelib_Mapper_FrontEndUser $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_FrontEndUser::class);
        /** @var \Tx_Oelib_Model_FrontEndUser $user */
        $user = $mapper->find($uid);

        self::assertSame(
            $user,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoadedModelOfUserNotInDatabaseReturnsThatInstance()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $nonExistentUid = $this->testingFramework->getAutoIncrement('fe_users');
        $user = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_FrontEndUser::class)->find($nonExistentUid);

        $this->testingFramework->loginFrontEndUser($nonExistentUid);

        self::assertSame(
            $user,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserUsesMappedUserDataFromMemory()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser(
            '',
            ['name' => 'John Doe']
        );

        /** @var TypoScriptFrontendController $frontEndController */
        $frontEndController = $GLOBALS['TSFE'];
        $frontEndController->fe_user->user['name'] = 'Jane Doe';
        $this->testingFramework->changeRecord(
            'fe_users',
            $feUserUid,
            ['name' => 'James Doe']
        );

        self::assertSame(
            'John Doe',
            $this->subject->getLoggedInUser()->getName()
        );
    }

    ///////////////////////////////
    // Tests concerning logInUser
    ///////////////////////////////

    /**
     * @test
     */
    public function logInUserOverwritesFormerRealLoggedInUser()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        $user = new \Tx_Oelib_Model_FrontEndUser();
        $this->subject->logInUser($user);

        self::assertSame(
            $user,
            $this->subject->getLoggedInUser()
        );
    }
}
