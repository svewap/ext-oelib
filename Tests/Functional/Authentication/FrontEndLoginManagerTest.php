<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Authentication;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Oelib\Authentication\FrontEndLoginManager
 */
class FrontEndLoginManagerTest extends FunctionalTestCase
{
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

        self::assertSame($user->getUid(), $this->subject->getLoggedInUserUid());
    }
}
