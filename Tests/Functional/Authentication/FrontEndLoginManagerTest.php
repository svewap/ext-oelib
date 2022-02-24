<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Oelib\Authentication\FrontEndLoginManager
 */
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

        $user = $this->getFrontEndController()->fe_user;
        if ($user instanceof FrontendUserAuthentication) {
            $user->setAndSaveSessionData('oelib_test', 1);
        }

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

        $user = $this->subject->getLoggedInUser();

        self::assertInstanceOf(FrontEndUser::class, $user);
        self::assertSame($uid, $user->getUid());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance(): void
    {
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());
        $uid = $this->testingFramework->createAndLoginFrontEndUser();

        $user = MapperRegistry::get(FrontEndUserMapper::class)->find($uid);

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

        $user = $this->getFrontEndController()->fe_user;
        if ($user instanceof FrontendUserAuthentication) {
            $user->user['name'] = 'Jane Doe';
        }
        $this->testingFramework->changeRecord('fe_users', $feUserUid, ['name' => 'James Doe']);

        /** @var FrontEndUser $loggedInUser */
        $loggedInUser = $this->subject->getLoggedInUser();
        self::assertSame($oldName, $loggedInUser->getName());
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
