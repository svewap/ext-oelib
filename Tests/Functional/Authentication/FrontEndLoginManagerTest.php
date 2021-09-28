<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class FrontEndLoginManagerTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');

        $this->subject = FrontEndLoginManager::getInstance();
    }

    protected function tearDown(): void
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
    public function isLoggedInForNoFrontEndReturnsFalse(): void
    {
        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForFrontEndWithoutLoggedInUserReturnsFalse(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInForAnonymousFrontEndSessionReturnsFalse(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        $this->getFrontEndController()->fe_user->setAndSaveSessionData('oelib_test', 1);

        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function isLoggedInWithLoggedInFrontEndUserReturnsTrue(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertTrue($this->subject->isLoggedIn());
    }

    // Tests concerning getLoggedInUser

    /**
     * @test
     */
    public function getLoggedInUserWithEmptyMapperNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        $this->subject->getLoggedInUser('');
    }

    /**
     * @test
     */
    public function getLoggedInUserWithoutFrontEndReturnsNull(): void
    {
        self::assertNull($this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithoutLoggedInUserReturnsNull(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->logoutFrontEndUser();

        self::assertNull($this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserInstance(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        self::assertInstanceOf(FrontEndUser::class, $this->subject->getLoggedInUser());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsFrontEndUserWithUidOfLoggedInUser(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $uid = $this->testingFramework->createAndLoginFrontEndUser();

        self::assertSame($uid, $this->subject->getLoggedInUser()->getUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance(): void
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
    public function getLoggedInUserUsesMappedUserDataFromMemory(): void
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
    public function logInUserOverwritesFormerRealLoggedInUser(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $this->testingFramework->createAndLoginFrontEndUser();

        $user = new FrontEndUser();
        $this->subject->logInUser($user);

        self::assertSame($user, $this->subject->getLoggedInUser());
    }
}
