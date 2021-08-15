<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FrontEndLoginManagerTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var FrontEndLoginManager
     */
    private $subject = null;

    /**
     * @var TestingFramework
     */
    private $testingFramework = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');

        $this->subject = FrontEndLoginManager::getInstance();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    // Tests concerning isLoggedIn

    /**
     * @test
     */
    public function isLoggedInForNoFrontEndReturnsFalse()
    {
        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForFrontEndWithoutLoggedInUserReturnsFalse()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForAnonymousFrontEndSessionReturnsFalse()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $this->getFrontEndController()->fe_user->setAndSaveSessionData('oelib_test', 1);

        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInWithLoggedInFrontEndUserReturnsTrue()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue($this->subject->isLoggedIn());
    }

    // Tests concerning getLoggedInUser

    /**
     * @test
     */
    public function getLoggedInUserWithEmptyMapperNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->getLoggedInUser('');
    }

    /**
     * @test
     */
    public function getLoggedInUserWithoutFrontEndReturnsNull()
    {
        self::assertNull($this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithoutLoggedInUserReturnsNull()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->logoutFrontEndUser();

        self::assertNull($this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserInstance()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertInstanceOf(FrontEndUser::class, $this->subject->getLoggedInUser());
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

        self::assertSame($uid, $this->subject->getLoggedInUser()->getUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $uid = $this->testingFramework->createAndLoginFrontEndUser();

        /** @var FrontEndUserMapper $mapper */
        $mapper = MapperRegistry::get(FrontEndUserMapper::class);
        /** @var FrontEndUser $user */
        $user = $mapper->find($uid);

        self::assertSame($user, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserUsesMappedUserDataFromMemory()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $oldName = 'John Doe';
        $feUserUid = $this->testingFramework->createAndLoginFrontEndUser('', ['name' => $oldName]);

        $this->getFrontEndController()->fe_user->user['name'] = 'Jane Doe';
        $this->testingFramework->changeRecord('fe_users', $feUserUid, ['name' => 'James Doe']);

        self::assertSame($oldName, $this->subject->getLoggedInUser()->getName());
    }

    // Tests concerning logInUser

    /**
     * @test
     */
    public function logInUserOverwritesFormerRealLoggedInUser()
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        self::assertSame($user, $this->subject->getLoggedInUser());
    }
}
