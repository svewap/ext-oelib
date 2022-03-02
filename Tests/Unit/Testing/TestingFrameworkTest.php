<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Testing;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Oelib\Tests\Unit\Testing\Fixtures\TestingCleanup;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Testing\TestingFramework
 */
final class TestingFrameworkTest extends UnitTestCase
{
    /**
     * @var TestingFramework
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new TestingFramework('tx_oelib');
    }

    protected function tearDown(): void
    {
        $this->subject->cleanUpWithoutDatabase();
        $this->subject->purgeHooks();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedDummyFile(): void
    {
        $fileName = $this->subject->createDummyFile();

        $this->subject->cleanUpWithoutDatabase();

        self::assertFileNotExists($fileName);
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedDummyFolder(): void
    {
        $folderName = $this->subject->createDummyFolder('test_folder');

        $this->subject->cleanUpWithoutDatabase();

        self::assertFileNotExists($folderName);
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedNestedDummyFolders(): void
    {
        $outerDummyFolder = $this->subject->createDummyFolder('test_folder');
        $innerDummyFolder = $this->subject->createDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
            '/test_folder'
        );

        $this->subject->cleanUpWithoutDatabase();

        self::assertFalse(
            file_exists($outerDummyFolder) && file_exists($innerDummyFolder)
        );
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedDummyUploadFolder(): void
    {
        $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());

        $this->subject->cleanUpWithoutDatabase();

        self::assertDirectoryNotExists($this->subject->getUploadFolderPath());
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

    // Tests regarding createDummyFile()

    /**
     * @test
     */
    public function createDummyFileCreatesFile(): void
    {
        $dummyFile = $this->subject->createDummyFile();

        self::assertFileExists($dummyFile);
    }

    /**
     * @test
     */
    public function createDummyFileCreatesFileInSubFolder(): void
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');
        $dummyFile = $this->subject->createDummyFile(
            $this->subject->getPathRelativeToUploadDirectory($dummyFolder) . '/test.txt'
        );

        self::assertFileExists($dummyFile);
    }

    /**
     * @test
     */
    public function createDummyFileCreatesFileWithTheProvidedContent(): void
    {
        $dummyFile = $this->subject->createDummyFile('test.txt', 'Hello world!');

        self::assertSame('Hello world!', file_get_contents($dummyFile));
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesUploadFolder(): void
    {
        $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder(): void
    {
        $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        $dummyFile = $this->subject->createDummyFile();

        self::assertFileExists($dummyFile);
    }

    // Tests regarding deleteDummyFile()

    /**
     * @test
     */
    public function deleteDummyFileDeletesCreatedDummyFile(): void
    {
        $dummyFile = $this->subject->createDummyFile();
        /** @var non-empty-string $basename */
        $basename = \basename($dummyFile);
        $this->subject->deleteDummyFile($basename);

        self::assertFileNotExists($dummyFile);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function deleteDummyFileWithAlreadyDeletedFileThrowsNoException(): void
    {
        $dummyFile = $this->subject->createDummyFile();
        unlink($dummyFile);

        /** @var non-empty-string $basename */
        $basename = \basename($dummyFile);
        $this->subject->deleteDummyFile($basename);
    }

    /**
     * @test
     */
    public function deleteDummyFileWithInexistentFileThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->deleteDummyFile('does-not-exist.txt');
    }

    /**
     * @test
     */
    public function deleteDummyFileWithForeignFileThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        vfsStream::setup('root/');
        $testFileUrl = vfsStream::url('root/test.txt');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->deleteDummyFile($testFileUrl);
    }

    // Tests regarding createDummyFolder()

    /**
     * @test
     */
    public function createDummyFolderCreatesFolder(): void
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($dummyFolder);
    }

    /**
     * @test
     */
    public function createDummyFolderCanCreateFolderInDummyFolder(): void
    {
        $outerDummyFolder = $this->subject->createDummyFolder('test_folder');
        $innerDummyFolder = $this->subject->createDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
            '/test_folder'
        );

        self::assertDirectoryExists($innerDummyFolder);
    }

    /**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder(): void
    {
        $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder(): void
    {
        $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        $dummyFolder = $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($dummyFolder);
    }

    // Tests regarding set- and getUploadFolderPath()

    /**
     * @test
     */
    public function getUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix(): void
    {
        self::assertRegExp(
            '/\\/typo3temp\\/tx_oelib\\/$/',
            $this->subject->getUploadFolderPath()
        );
    }

    /**
     * @test
     */
    public function getUploadFolderPathAfterSetReturnsSetUploadFolderPath(): void
    {
        $this->subject->setUploadFolderPath('/foo/bar/');

        self::assertSame(
            '/foo/bar/',
            $this->subject->getUploadFolderPath()
        );
    }

    /**
     * @test
     */
    public function setUploadFolderPathAfterCreatingDummyFileThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'The upload folder path must not be changed if there are already dummy files or folders.'
        );

        $this->subject->createDummyFile();
        $this->subject->setUploadFolderPath('/foo/bar/');
    }

    // Tests regarding getPathRelativeToUploadDirectory()

    /**
     * @test
     */
    public function getPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The first parameter $absolutePath is not within the calling extension\'s upload directory.'
        );

        $this->subject->getPathRelativeToUploadDirectory(Environment::getPublicPath() . '/');
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
