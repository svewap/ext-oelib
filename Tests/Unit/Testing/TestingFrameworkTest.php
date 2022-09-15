<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Testing;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Oelib\Tests\Unit\Testing\Fixtures\TestingCleanup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Testing\TestingFramework
 * @covers \OliverKlee\Oelib\Testing\TestingFrameworkCleanup
 */
final class TestingFrameworkTest extends UnitTestCase
{
    /**
     * @var TestingFramework
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TestingFramework('tx_oelib');
    }

    protected function tearDown(): void
    {
        $this->subject->cleanUpWithoutDatabase();
        $this->subject->purgeHooks();

        GeneralUtility::purgeInstances();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseExecutesCleanUpHook(): void
    {
        $this->subject->purgeHooks();

        $cleanUpWithoutDatabaseHookMock = $this->createMock(TestingCleanup::class);
        $cleanUpWithoutDatabaseHookMock->expects(self::atLeastOnce())->method('cleanUp');
        $hookClassName = \get_class($cleanUpWithoutDatabaseHookMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $cleanUpWithoutDatabaseHookMock);

        $this->subject->cleanUpWithoutDatabase();
    }

    // Tests regarding createTemplate()

    /**
     * @test
     */
    public function templateMustNotHaveZeroPid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "pid" must not be set in $recordData.'
        );
        $this->subject->createTemplate(42, ['pid' => 0]);
    }

    /**
     * @test
     */
    public function templateMustNotHaveNonZeroPid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "pid" must not be set in $recordData.'
        );
        $this->subject->createTemplate(42, ['pid' => 99999]);
    }

    /**
     * @test
     */
    public function templateMustHaveNoZeroUid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );
        $this->subject->createTemplate(42, ['uid' => 0]);
    }

    /**
     * @test
     */
    public function templateMustNotHaveNonZeroUid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );
        $this->subject->createTemplate(42, ['uid' => 99999]);
    }

    // Tests regarding createFrontEndUserGroup()

    /**
     * @test
     */
    public function frontEndUserGroupMustHaveNoZeroUid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUserGroup(['uid' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserGroupMustHaveNoNonZeroUid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUserGroup(['uid' => 99999]);
    }

    // Tests regarding createBackEndUser()

    /**
     * @test
     */
    public function createBackEndUserWithZeroUidProvidedInRecordDataThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createBackEndUser(['uid' => 0]);
    }

    /**
     * @test
     */
    public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createBackEndUser(['uid' => 999999]);
    }

    // Tests concerning fakeFrontend

    /**
     * @test
     */
    public function createFakeFrontThrowsExceptionForNegativePageUid(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$pageUid must be >= 0.'
        );

        $this->subject->createFakeFrontEnd(-1);
    }

    // Tests regarding user login and logout

    /**
     * @test
     */
    public function isLoggedThrowsExceptionWithoutFrontEnd(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'Please create a front end before calling isLoggedIn.'
        );

        $this->subject->isLoggedIn();
    }

    /**
     * @test
     */
    public function logoutFrontEndUserWithoutFrontEndThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'Please create a front end before calling logoutFrontEndUser.'
        );

        $this->subject->logoutFrontEndUser();
    }
}
