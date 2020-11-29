<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Testing;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Oelib\Testing\TestingFramework;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingFrameworkTest extends UnitTestCase
{
    /**
     * @var TestingFramework
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingFramework('tx_oelib');
    }

    protected function tearDown()
    {
        $this->subject->cleanUpWithoutDatabase();
        $this->subject->purgeHooks();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedDummyFile()
    {
        $fileName = $this->subject->createDummyFile();

        $this->subject->cleanUpWithoutDatabase();

        self::assertFileNotExists($fileName);
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedDummyFolder()
    {
        $folderName = $this->subject->createDummyFolder('test_folder');

        $this->subject->cleanUpWithoutDatabase();

        self::assertFileNotExists($folderName);
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseDeletesCreatedNestedDummyFolders()
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
    public function cleanUpWithoutDatabaseDeletesCreatedDummyUploadFolder()
    {
        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        } else {
            $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        }
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());

        $this->subject->cleanUpWithoutDatabase();

        self::assertDirectoryNotExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseExecutesCleanUpHook()
    {
        $this->subject->purgeHooks();

        $cleanUpWithoutDatabaseHookMock = $this->createPartialMock(\stdClass::class, ['cleanUp']);
        $cleanUpWithoutDatabaseHookMock->expects(self::atLeastOnce())->method('cleanUp');
        $hookClassName = \get_class($cleanUpWithoutDatabaseHookMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $cleanUpWithoutDatabaseHookMock);

        $this->subject->cleanUpWithoutDatabase();
    }

    /*
     * Tests regarding createTemplate()
     */

    /**
     * @test
     */
    public function templateMustNotHaveZeroPid()
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
    public function templateMustNotHaveNonZeroPid()
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
    public function templateMustHaveNoZeroUid()
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
    public function templateMustNotHaveNonZeroUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );
        $this->subject->createTemplate(42, ['uid' => 99999]);
    }

    /*
     * Tests regarding createDummyFile()
     */

    /**
     * @test
     */
    public function createDummyFileCreatesFile()
    {
        $dummyFile = $this->subject->createDummyFile();

        self::assertFileExists($dummyFile);
    }

    /**
     * @test
     */
    public function createDummyFileCreatesFileInSubFolder()
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
    public function createDummyFileCreatesFileWithTheProvidedContent()
    {
        $dummyFile = $this->subject->createDummyFile('test.txt', 'Hello world!');

        self::assertSame('Hello world!', file_get_contents($dummyFile));
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesUploadFolder()
    {
        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        } else {
            $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        }
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        } else {
            $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        }
        $dummyFile = $this->subject->createDummyFile();

        self::assertFileExists($dummyFile);
    }

    /*
     * Tests regarding deleteDummyFile()
     */

    /**
     * @test
     */
    public function deleteDummyFileDeletesCreatedDummyFile()
    {
        $dummyFile = $this->subject->createDummyFile();
        $this->subject->deleteDummyFile(basename($dummyFile));

        self::assertFileNotExists($dummyFile);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function deleteDummyFileWithAlreadyDeletedFileThrowsNoException()
    {
        $dummyFile = $this->subject->createDummyFile();
        unlink($dummyFile);

        $this->subject->deleteDummyFile(basename($dummyFile));
    }

    /**
     * @test
     */
    public function deleteDummyFileWithInexistentFileThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->deleteDummyFile('does-not-exist.txt');
    }

    /**
     * @test
     */
    public function deleteDummyFileWithForeignFileThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        vfsStream::setup('root/');
        $testFileUrl = vfsStream::url('root/test.txt');

        $this->subject->deleteDummyFile($testFileUrl);
    }

    /*
     * Tests regarding createDummyFolder()
     */

    /**
     * @test
     */
    public function createDummyFolderCreatesFolder()
    {
        $dummyFolder = $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($dummyFolder);
    }

    /**
     * @test
     */
    public function createDummyFolderCanCreateFolderInDummyFolder()
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
    public function createDummyFolderForNonExistentUploadFolderSetCreatesUploadFolder()
    {
        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        } else {
            $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        }
        $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        } else {
            $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        }
        $dummyFolder = $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($dummyFolder);
    }

    /*
     * Tests regarding set- and getUploadFolderPath()
     */

    /**
     * @test
     */
    public function getUploadFolderPathReturnsUploadFolderPathIncludingTablePrefix()
    {
        self::assertRegExp(
            '/\\/typo3temp\\/tx_oelib\\/$/',
            $this->subject->getUploadFolderPath()
        );
    }

    /**
     * @test
     */
    public function getUploadFolderPathAfterSetReturnsSetUploadFolderPath()
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
    public function setUploadFolderPathAfterCreatingDummyFileThrowsException()
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

    /*
     * Tests regarding getPathRelativeToUploadDirectory()
     */

    /**
     * @test
     */
    public function getPathRelativeToUploadDirectoryWithPathOutsideUploadDirectoryThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The first parameter $absolutePath is not within the calling extension\'s upload directory.'
        );

        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->getPathRelativeToUploadDirectory(PATH_site);
        } else {
            $this->subject->getPathRelativeToUploadDirectory(Environment::getPublicPath() . '/');
        }
    }

    /*
     * Tests regarding createFrontEndUserGroup()
     */

    /**
     * @test
     */
    public function frontEndUserGroupMustHaveNoZeroUid()
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
    public function frontEndUserGroupMustHaveNoNonZeroUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUserGroup(['uid' => 99999]);
    }

    /*
     * Tests regarding createBackEndUser()
     */

    /**
     * @test
     */
    public function createBackEndUserWithZeroUidProvidedInRecordDataThrowsException()
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
    public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createBackEndUser(['uid' => 999999]);
    }

    /*
     * Tests concerning fakeFrontend
     */

    /**
     * @test
     */
    public function createFakeFrontThrowsExceptionForNegativePageUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$pageUid must be >= 0.'
        );

        $this->subject->createFakeFrontEnd(-1);
    }

    /*
     * Tests regarding user login and logout
     */

    /**
     * @test
     */
    public function isLoggedThrowsExceptionWithoutFrontEnd()
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
    public function logoutFrontEndUserWithoutFrontEndThrowsException()
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
