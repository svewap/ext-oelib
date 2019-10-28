<?php

use OliverKlee\PhpUnit\TestCase;
use org\bovigo\vfs\vfsStream;
use TYPO3\CMS\Core\TimeTracker\NullTimeTracker;
use TYPO3\CMS\Core\TimeTracker\TimeTracker;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Test case.
 *
 * @author Mario Rimann <typo3-coding@rimann.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_LegacyUnit_TestingFrameworkTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    protected $subject = null;

    /**
     * @var string absolute path to a "foreign" folder which was created for
     *             test purposes and which should be deleted in tearDown();
     *             this is needed for
     *             testDeleteDummyFolderWithForeignFolderThrowsException
     */
    protected $foreignFolderToDelete = '';

    /**
     * backed-up extension configuration of the TYPO3 configuration variables
     *
     * @var array
     */
    protected $extConfBackup = [];

    /**
     * backed-up T3_VAR configuration
     *
     * @var array
     */
    protected $t3VarBackup = [];

    protected function setUp()
    {
        $this->extConfBackup = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'];
        $this->t3VarBackup = $GLOBALS['T3_VAR']['getUserObj'];

        $this->subject = new \Tx_Oelib_TestingFramework('tx_oelib', ['user_oelibtest']);
    }

    protected function tearDown()
    {
        $this->subject->setResetAutoIncrementThreshold(1);
        $this->subject->cleanUp();
        $this->subject->purgeHooks();
        $this->deleteForeignFolder();

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF'] = $this->extConfBackup;
        $GLOBALS['T3_VAR']['getUserObj'] = $this->t3VarBackup;
    }

    /*
     * Utility functions.
     */

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController
     */
    private function getFrontEndController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Returns the sorting value of the relation between the local UID given by
     * the first parameter $uidLocal and the foreign UID given by the second
     * parameter $uidForeign.
     *
     * @param int $uidLocal
     *        the UID of the local record, must be > 0
     * @param int $uidForeign
     *        the UID of the foreign record, must be > 0
     *
     * @return int the sorting value of the relation
     */
    protected function getSortingOfRelation($uidLocal, $uidForeign)
    {
        $row = \Tx_Oelib_Db::selectSingle(
            'sorting',
            'tx_oelib_test_article_mm',
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
        );

        return (int)$row['sorting'];
    }

    /**
     * Checks whether the extension user_oelibtest is currently loaded and lets
     * a test fail if the extension is not loaded.
     *
     * @return void
     */
    protected function checkIfExtensionUserOelibtestIsLoaded()
    {
        if (!ExtensionManagementUtility::isLoaded('user_oelibtest')) {
            self::markTestSkipped(
                'The extension user_oelibtest is not installed, but needs to be installed. Please install it.'
            );
        }
    }

    /**
     * Checks whether the extension user_oelibtest2 is currently loaded and lets
     * a test fail if the extension is not loaded.
     *
     * @return void
     */
    protected function checkIfExtensionUserOelibtest2IsLoaded()
    {
        if (!ExtensionManagementUtility::isLoaded('user_oelibtest')) {
            self::markTestSkipped(
                'The extension user_oelibtest2 is not installed, but needs to be installed. Please install it.'
            );
        }
    }

    /**
     * Deletes a "foreign" folder which was created for test purposes.
     *
     * @return void
     */
    protected function deleteForeignFolder()
    {
        if ($this->foreignFolderToDelete === '') {
            return;
        }

        GeneralUtility::rmdir($this->foreignFolderToDelete);
        $this->foreignFolderToDelete = '';
    }

    /*
     * Tests regarding markTableAsDirty()
     */

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpNonSystemTable()
    {
        $uid = \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['is_dummy_record' => 1]
        );

        $this->subject->markTableAsDirty('tx_oelib_test');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('tx_oelib_test', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpSystemTable()
    {
        $uid = \Tx_Oelib_Db::insert(
            'pages',
            ['tx_oelib_is_dummy_record' => 1]
        );

        $this->subject->markTableAsDirty('pages');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('pages', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpAdditionalAllowedTable()
    {
        $this->checkIfExtensionUserOelibtestIsLoaded();

        $uid = \Tx_Oelib_Db::insert(
            'user_oelibtest_test',
            ['tx_oelib_is_dummy_record' => 1]
        );

        $this->subject->markTableAsDirty('user_oelibtest_test');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('user_oelibtest_test', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsOnInexistentTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "tx_oelib_DOESNOTEXIST" is not allowed for markTableAsDirty.'
        );
        $this->subject->markTableAsDirty('tx_oelib_DOESNOTEXIST');
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsOnNotAllowedSystemTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "sys_domain" is not allowed for markTableAsDirty.'
        );
        $this->subject->markTableAsDirty('sys_domain');
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsOnForeignTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "tx_seminars_seminars" is not allowed for markTableAsDirty.'
        );
        $this->subject->markTableAsDirty('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsWithEmptyTableName()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "" is not allowed for markTableAsDirty.'
        );
        $this->subject->markTableAsDirty('');
    }

    /*
     * Tests regarding createRecord()
     */

    /**
     * @test
     */
    public function createRecordOnValidTableWithNoData()
    {
        self::assertNotSame(
            0,
            $this->subject->createRecord('tx_oelib_test', [])
        );
    }

    /**
     * @test
     */
    public function createRecordWithValidData()
    {
        $title = 'TEST record';
        $uid = $this->subject->createRecord(
            'tx_oelib_test',
            [
                'title' => $title,
            ]
        );
        self::assertNotSame(
            0,
            $uid
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'title',
            'tx_oelib_test',
            'uid = ' . $uid
        );

        self::assertSame(
            $title,
            $row['title']
        );
    }

    /**
     * @test
     */
    public function createRecordOnInvalidTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "tx_oelib_DOESNOTEXIST" is not allowed.'
        );
        $this->subject->createRecord('tx_oelib_DOESNOTEXIST', []);
    }

    /**
     * @test
     */
    public function createRecordWithEmptyTableName()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "" is not allowed.'
        );
        $this->subject->createRecord('', []);
    }

    /**
     * @test
     */
    public function createRecordWithUidFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );
        $this->subject->createRecord(
            'tx_oelib_test',
            ['uid' => 99999]
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function createRecordOnValidAdditionalAllowedTableWithValidDataSucceeds()
    {
        $this->checkIfExtensionUserOelibtestIsLoaded();

        $title = 'TEST record';
        $this->subject->createRecord(
            'user_oelibtest_test',
            [
                'title' => $title,
            ]
        );
    }

    /*
     * Tests regarding changeRecord()
     */

    /**
     * @test
     */
    public function changeRecordWithExistingRecord()
    {
        $uid = $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid,
            ['title' => 'bar']
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'title',
            'tx_oelib_test',
            'uid = ' . $uid
        );

        self::assertSame(
            'bar',
            $row['title']
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnForeignTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table "tx_seminars_seminars" is not on the lists with allowed tables.'
        );
        $this->subject->changeRecord(
            'tx_seminars_seminars',
            99999,
            ['title' => 'foo']
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnInexistentTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table "tx_oelib_DOESNOTEXIST" is not on the lists with allowed tables.'
        );
        $this->subject->changeRecord(
            'tx_oelib_DOESNOTEXIST',
            99999,
            ['title' => 'foo']
        );
    }

    /**
     * @test
     */
    public function changeRecordOnAllowedSystemTableForPages()
    {
        $pid = $this->subject->createFrontEndPage();

        $this->subject->changeRecord(
            'pages',
            $pid,
            ['title' => 'bar']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('pages', 'uid=' . $pid . ' AND title="bar"')
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnOtherSystemTable()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table "sys_domain" is not on the lists with allowed tables.'
        );
        $this->subject->changeRecord(
            'sys_domain',
            1,
            ['title' => 'bar']
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function changeRecordOnAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserOelibtestIsLoaded();

        $uid = $this->subject->createRecord(
            'user_oelibtest_test',
            ['title' => 'foo']
        );

        $this->subject->changeRecord(
            'user_oelibtest_test',
            $uid,
            ['title' => 'bar']
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsWithUidZero()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $uid must not be zero.'
        );
        $this->subject->changeRecord('tx_oelib_test', 0, ['title' => 'foo']);
    }

    /**
     * @test
     */
    public function changeRecordFailsWithEmptyData()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The array with the new record data must not be empty.'
        );
        $uid = $this->subject->createRecord('tx_oelib_test', []);

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid,
            []
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsWithUidFieldInRecordData()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $recordData must not contain changes to the UID of a record.'
        );
        $uid = $this->subject->createRecord('tx_oelib_test', []);

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid,
            ['uid' => '55742']
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsWithDummyRecordFieldInRecordData()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $recordData must not contain changes to the field ' .
            '"is_dummy_record". It is impossible to convert a dummy record into a regular record.'
        );
        $uid = $this->subject->createRecord('tx_oelib_test', []);

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid,
            ['is_dummy_record' => 0]
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnInexistentRecord()
    {
        $uid = $this->subject->createRecord('tx_oelib_test', []);
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'There is no record with UID ' . ($uid + 1) . ' on table "tx_oelib_test".'
        );

        $this->subject->changeRecord(
            'tx_oelib_test',
            $uid + 1,
            ['title' => 'foo']
        );
    }

    /*
     * Tests regarding createRelation()
     */

    /**
     * @test
     */
    public function createRelationWithValidData()
    {
        $uidLocal = $this->subject->createRecord('tx_oelib_test');
        $uidForeign = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign
        );

        // Checks whether the record really exists.
        self::assertSame(
            1,
            $this->subject->countRecords(
                'tx_oelib_test_article_mm',
                'uid_local=' . $uidLocal . ' AND uid_foreign=' . $uidForeign
            )
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function createRelationWithValidDataOnAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserOelibtestIsLoaded();

        $uidLocal = $this->subject->createRecord('user_oelibtest_test');
        $uidForeign = $this->subject->createRecord('user_oelibtest_test');

        $this->subject->createRelation(
            'user_oelibtest_test_article_mm',
            $uidLocal,
            $uidForeign
        );
    }

    /**
     * @test
     */
    public function createRelationWithInvalidTable()
    {
        $table = 'tx_oelib_test_DOESNOTEXIST_mm';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "' . $table . '" is not allowed.'
        );
        $this->subject->createRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     */
    public function createRelationWithEmptyTableName()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "" is not allowed.'
        );
        $this->subject->createRelation('', 99999, 199999);
    }

    /**
     * @test
     */
    public function createRelationWithZeroFirstUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uidLocal must be an integer > 0, but actually is "0"'
        );
        $uid = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation('tx_oelib_test_article_mm', 0, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithZeroSecondUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uidForeign must be an integer > 0, but actually is "0"'
        );
        $uid = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, 0);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeFirstUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uidLocal must be an integer > 0, but actually is "-1"'
        );
        $uid = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation('tx_oelib_test_article_mm', -1, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeSecondUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uidForeign must be an integer > 0, but actually is "-1"'
        );
        $uid = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, -1);
    }

    /**
     * @test
     */
    public function createRelationWithAutomaticSorting()
    {
        $uidLocal = $this->subject->createRecord('tx_oelib_test');
        $uidForeign = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign
        );
        $previousSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
        self::assertGreaterThan(
            0,
            $previousSorting
        );

        $uidForeign = $this->subject->createRecord('tx_oelib_test');
        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign
        );
        $nextSorting = $this->getSortingOfRelation($uidLocal, $uidForeign);
        self::assertSame(
            $previousSorting + 1,
            $nextSorting
        );
    }

    /**
     * @test
     */
    public function createRelationWithManualSorting()
    {
        $uidLocal = $this->subject->createRecord('tx_oelib_test');
        $uidForeign = $this->subject->createRecord('tx_oelib_test');
        $sorting = 99999;

        $this->subject->createRelation(
            'tx_oelib_test_article_mm',
            $uidLocal,
            $uidForeign,
            $sorting
        );

        self::assertSame(
            $sorting,
            $this->getSortingOfRelation($uidLocal, $uidForeign)
        );
    }

    /*
     * Tests regarding createRelationFromTca()
     */

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne()
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records'
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'related_records',
            'tx_oelib_test',
            'uid = ' . $firstRecordUid
        );

        self::assertSame(
            1,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne()
    {
        $firstRecordUid = $this->subject->createRecord(
            'tx_oelib_test',
            ['related_records' => 1]
        );
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records'
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'related_records',
            'tx_oelib_test',
            'uid = ' . $firstRecordUid
        );

        self::assertSame(
            2,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterCreatesRecordInRelationTable()
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'related_records'
        );

        $count = $this->subject->countRecords(
            'tx_oelib_test_article_mm',
            'uid_local=' . $firstRecordUid
        );
        self::assertSame(
            1,
            $count
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesCounter()
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional'
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'bidirectional',
            'tx_oelib_test',
            'uid = ' . $firstRecordUid
        );

        self::assertSame(
            1,
            (int)$row['bidirectional']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesOppositeFieldCounterInForeignTable()
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional'
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'related_records',
            'tx_oelib_test',
            'uid = ' . $secondRecordUid
        );

        self::assertSame(
            1,
            (int)$row['related_records']
        );
    }

    /**
     * @test
     */
    public function createRelationAndUpdateCounterWithBidirectionalRelationCreatesRecordInRelationTable()
    {
        $firstRecordUid = $this->subject->createRecord('tx_oelib_test');
        $secondRecordUid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $firstRecordUid,
            $secondRecordUid,
            'bidirectional'
        );

        $count = $this->subject->countRecords(
            'tx_oelib_test_article_mm',
            'uid_local=' . $secondRecordUid . ' AND uid_foreign=' .
            $firstRecordUid
        );
        self::assertSame(
            1,
            $count
        );
    }

    /*
     * Tests regarding cleanUp()
     */

    /**
     * @test
     */
    public function cleanUpWithRegularCleanUpDeletesTestsRecords()
    {
        // Creates a dummy record (and marks that table as dirty).
        $this->subject->createRecord('tx_oelib_test');

        // Creates a dummy record directly in the database, without putting this
        // table name to the list of dirty tables.
        \Tx_Oelib_Db::insert(
            'tx_oelib_test_article_mm',
            ['is_dummy_record' => 1]
        );

        // Runs a regular clean up. This should now delete only the first record
        // which was created through the testing framework and thus that table
        // is on the list of dirty tables. The second record was directly put
        // into the database and it's table is not on this list and will not be
        // removed by a regular clean up run.
        $this->subject->cleanUp();

        // Checks whether the first dummy record is deleted.
        self::assertSame(
            0,
            $this->subject->countRecords('tx_oelib_test'),
            'Some test records were not deleted from table "tx_oelib_test"'
        );

        // Checks whether the second dummy record still exists.
        self::assertSame(
            1,
            $this->subject->countRecords('tx_oelib_test_article_mm')
        );

        // Runs a deep clean up to delete all dummy records.
        $this->subject->cleanUp(true);
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyFile()
    {
        $fileName = $this->subject->createDummyFile();

        $this->subject->cleanUp();

        self::assertFileNotExists($fileName);
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyFolder()
    {
        $folderName = $this->subject->createDummyFolder('test_folder');

        $this->subject->cleanUp();

        self::assertFileNotExists($folderName);
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedNestedDummyFolders()
    {
        $outerDummyFolder = $this->subject->createDummyFolder('test_folder');
        $innerDummyFolder = $this->subject->createDummyFolder(
            $this->subject->getPathRelativeToUploadDirectory($outerDummyFolder) .
            '/test_folder'
        );

        $this->subject->cleanUp();

        self::assertFalse(
            file_exists($outerDummyFolder) && file_exists($innerDummyFolder)
        );
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());

        $this->subject->cleanUp();

        self::assertDirectoryNotExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function cleanUpExecutesCleanUpHook()
    {
        $this->subject->purgeHooks();

        $cleanUpHookMock = $this->createPartialMock(\stdClass::class, ['cleanUp']);
        $cleanUpHookMock->expects(self::atLeastOnce())->method('cleanUp');
        $hookClassName = \get_class($cleanUpHookMock);

        $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][$hookClassName] = $hookClassName;
        GeneralUtility::addInstance($hookClassName, $cleanUpHookMock);

        $this->subject->cleanUp();
    }

    /*
     * Tests regarding cleanUpWithoutDatabase()
     */

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseWithRegularCleanUpNotDeletesTestsRecords()
    {
        // Creates a dummy record (and marks that table as dirty).
        $this->subject->createRecord('tx_oelib_test');

        // Creates a dummy record directly in the database, without putting this
        // table name to the list of dirty tables.
        \Tx_Oelib_Db::insert(
            'tx_oelib_test_article_mm',
            ['is_dummy_record' => 1]
        );

        // Runs a regular clean up. This should now delete only the first record
        // which was created through the testing framework and thus that table
        // is on the list of dirty tables. The second record was directly put
        // into the database and it's table is not on this list and will not be
        // removed by a regular clean up run.
        $this->subject->cleanUpWithoutDatabase();

        // Checks whether the first dummy record is deleted.
        self::assertSame(
            1,
            $this->subject->countRecords('tx_oelib_test'),
            'Some test records were not deleted from table "tx_oelib_test"'
        );
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
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
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
     * Tests regarding getAutoIncrement()
     */

    /**
     * @test
     */
    public function getAutoIncrementReturnsOneForTruncatedTable()
    {
        \Tx_Oelib_Db::getDatabaseConnection()->sql_query('TRUNCATE TABLE tx_oelib_test;');

        self::assertSame(
            1,
            $this->subject->getAutoIncrement('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function getAutoIncrementGetsCurrentAutoIncrement()
    {
        $uid = $this->subject->createRecord('tx_oelib_test');

        // $uid will equals be the previous auto increment value, so $uid + 1
        // should be equal to the current auto inrement value.
        self::assertSame(
            $uid + 1,
            $this->subject->getAutoIncrement('tx_oelib_test')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForFeUsersTableIsAllowed()
    {
        $this->subject->getAutoIncrement('fe_users');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForPagesTableIsAllowed()
    {
        $this->subject->getAutoIncrement('pages');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForTtContentTableIsAllowed()
    {
        $this->subject->getAutoIncrement('tt_content');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithOtherSystemTableFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );
        $this->subject->getAutoIncrement('sys_domains');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForSysFileIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_file');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForSysFileCollectionIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_file_collection');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForSysFileReferenceIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_file_reference');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getAutoIncrementForSysCategoryIsAllowed()
    {
        $this->subject->getAutoIncrement('sys_category');
    }

    /**
     * @test
     */
    public function getAutoIncrementForSysCategoryRecordMmFails()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->getAutoIncrement('sys_category_record_mm');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithEmptyTableNameFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );
        $this->subject->getAutoIncrement('');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithForeignTableFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );
        $this->subject->getAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithInexistentTableFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );
        $this->subject->getAutoIncrement('tx_oelib_DOESNOTEXIST');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithTableWithoutUidFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );
        $this->subject->getAutoIncrement('tx_oelib_test_article_mm');
    }

    /*
     * Tests regarding countRecords()
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->countRecords('tx_oelib_test', '');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithMissingWhereClauseIsAllowed()
    {
        $this->subject->countRecords('tx_oelib_test');
    }

    /**
     * @test
     */
    public function countRecordsWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->countRecords('');
    }

    /**
     * @test
     */
    public function countRecordsWithInvalidTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $table = 'foo_bar';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithFeGroupsTableIsAllowed()
    {
        $table = 'fe_groups';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithFeUsersTableIsAllowed()
    {
        $table = 'fe_users';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithPagesTableIsAllowed()
    {
        $table = 'pages';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithTtContentTableIsAllowed()
    {
        $table = 'tt_content';
        $this->subject->countRecords($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithSysFileTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_file');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithSysFileCollectionTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_file_collection');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithSysFileReferenceTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_file_reference');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithSysCategoryTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_category');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsWithSysCategoryRecordMmTableTableIsAllowed()
    {
        $this->subject->countRecords('sys_category_record_mm');
    }

    /**
     * @test
     */
    public function countRecordsWithOtherTableThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->countRecords('sys_domain');
    }

    /**
     * @test
     */
    public function countRecordsReturnsZeroForNoMatches()
    {
        self::assertSame(
            0,
            $this->subject->countRecords('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function countRecordsReturnsOneForOneDummyRecordMatch()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function countRecordsWithMissingWhereClauseReturnsOneForOneDummyRecordMatch()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function countRecordsReturnsTwoForTwoMatches()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertSame(
            2,
            $this->subject->countRecords('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countRecordsForPagesTableIsAllowed()
    {
        $this->subject->countRecords('pages');
    }

    /**
     * @test
     */
    public function countRecordsIgnoresNonDummyRecords()
    {
        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->countRecords(
            'tx_oelib_test',
            'title = "foo"'
        );

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_oelib_test');

        self::assertSame(
            0,
            $testResult
        );
    }

    /*
     * Tests regarding count()
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->count('tx_oelib_test', []);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithMissingWhereClauseIsAllowed()
    {
        $this->subject->count('tx_oelib_test');
    }

    /**
     * @test
     */
    public function countWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->count('');
    }

    /**
     * @test
     */
    public function countWithInvalidTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $table = 'foo_bar';
        $this->subject->count($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithFeGroupsTableIsAllowed()
    {
        $table = 'fe_groups';
        $this->subject->count($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithFeUsersTableIsAllowed()
    {
        $table = 'fe_users';
        $this->subject->count($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithPagesTableIsAllowed()
    {
        $table = 'pages';
        $this->subject->count($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithTtContentTableIsAllowed()
    {
        $table = 'tt_content';
        $this->subject->count($table);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithSysFileTableTableIsAllowed()
    {
        $this->subject->count('sys_file');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithSysFileCollectionTableTableIsAllowed()
    {
        $this->subject->count('sys_file_collection');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithSysFileReferenceTableTableIsAllowed()
    {
        $this->subject->count('sys_file_reference');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithSysCategoryTableTableIsAllowed()
    {
        $this->subject->count('sys_category');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithSysCategoryRecordMmTableTableIsAllowed()
    {
        $this->subject->count('sys_category_record_mm');
    }

    /**
     * @test
     */
    public function countWithOtherTableThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->count('sys_domain');
    }

    /**
     * @test
     */
    public function countReturnsZeroForNoMatches()
    {
        self::assertSame(
            0,
            $this->subject->count('tx_oelib_test', ['title' => 'foo'])
        );
    }

    /**
     * @test
     */
    public function countReturnsOneForOneDummyRecordMatch()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->subject->count('tx_oelib_test', ['title' => 'foo'])
        );
    }

    /**
     * @test
     */
    public function countWithMissingWhereClauseReturnsOneForOneDummyRecordMatch()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->subject->count('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function countReturnsTwoForTwoMatches()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertSame(
            2,
            $this->subject->count('tx_oelib_test', ['title' => 'foo'])
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countForPagesTableIsAllowed()
    {
        $this->subject->count('pages');
    }

    /**
     * @test
     */
    public function countIgnoresNonDummyRecords()
    {
        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->count('tx_oelib_test', ['title' => 'foo']);

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_oelib_test');

        self::assertSame(
            0,
            $testResult
        );
    }

    /*
     * Tests regarding existsRecord()
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function existsRecordWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->existsRecord('tx_oelib_test', '');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function existsRecordWithMissingWhereClauseIsAllowed()
    {
        $this->subject->existsRecord('tx_oelib_test');
    }

    /**
     * @test
     */
    public function existsRecordWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->existsRecord('');
    }

    /**
     * @test
     */
    public function existsRecordWithInvalidTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $table = 'foo_bar';
        $this->subject->existsRecord($table);
    }

    /**
     * @test
     */
    public function existsRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsRecordForOneMatchReturnsTrue()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->subject->existsRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsRecordForTwoMatchesReturnsTrue()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->subject->existsRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsRecordIgnoresNonDummyRecords()
    {
        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsRecord(
            'tx_oelib_test',
            'title = "foo"'
        );

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_oelib_test');

        self::assertFalse(
            $testResult
        );
    }

    /*
     * Tests regarding existsRecordWithUid()
     */

    /**
     * @test
     */
    public function existsRecordWithUidWithZeroUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uid must be > 0.'
        );

        $this->subject->existsRecordWithUid('tx_oelib_test', 0);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithNegativeUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uid must be > 0.'
        );

        $this->subject->existsRecordWithUid('tx_oelib_test', -1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->existsRecordWithUid('', 1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given table name is not in the list of allowed tables.');

        $table = 'foo_bar';
        $this->subject->existsRecordWithUid($table, 1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidForNoMatcheReturnsFalse()
    {
        $uid = $this->subject->createRecord('tx_oelib_test');
        \Tx_Oelib_Db::delete('tx_oelib_test', 'uid = ' . $uid);

        self::assertFalse(
            $this->subject->existsRecordWithUid(
                'tx_oelib_test',
                $uid
            )
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidForMatchReturnsTrue()
    {
        $uid = $this->subject->createRecord('tx_oelib_test');

        self::assertTrue(
            $this->subject->existsRecordWithUid('tx_oelib_test', $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidIgnoresNonDummyRecords()
    {
        $uid = \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsRecordWithUid(
            'tx_oelib_test',
            $uid
        );

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'uid = ' . $uid
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_oelib_test');

        self::assertFalse(
            $testResult
        );
    }

    /*
     * Tests regarding existsExactlyOneRecord()
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed()
    {
        $this->subject->existsExactlyOneRecord('tx_oelib_test', '');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed()
    {
        $this->subject->existsExactlyOneRecord('tx_oelib_test');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->existsExactlyOneRecord('');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithInvalidTableNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $table = 'foo_bar';
        $this->subject->existsExactlyOneRecord($table);
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsExactlyOneRecord(
                'tx_oelib_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForOneMatchReturnsTrue()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->subject->existsExactlyOneRecord(
                'tx_oelib_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForTwoMatchesReturnsFalse()
    {
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->subject->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertFalse(
            $this->subject->existsExactlyOneRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordIgnoresNonDummyRecords()
    {
        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsExactlyOneRecord(
            'tx_oelib_test',
            'title = "foo"'
        );

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'title = "foo"'
        );
        // We need to do this manually to not confuse the auto_increment counter
        // of the testing framework.
        $this->subject->resetAutoIncrement('tx_oelib_test');

        self::assertFalse(
            $testResult
        );
    }

    /*
     * Tests regarding resetAutoIncrement()
     */

    /**
     * @test
     */
    public function resetAutoIncrementForTestTableSucceeds()
    {
        $this->subject->resetAutoIncrement('tx_oelib_test');

        $latestUid = $this->subject->createRecord('tx_oelib_test');
        \Tx_Oelib_Db::delete('tx_oelib_test', 'uid = ' . $latestUid);
        $this->subject->resetAutoIncrement('tx_oelib_test');

        self::assertSame(
            $latestUid,
            $this->subject->getAutoIncrement('tx_oelib_test')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForUnchangedTestTableCanBeRun()
    {
        $this->subject->resetAutoIncrement('tx_oelib_test');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForAdditionalAllowedTableSucceeds()
    {
        $this->checkIfExtensionUserOelibtestIsLoaded();

        // Creates and deletes a record and then resets the auto increment.
        $latestUid = $this->subject->createRecord('user_oelibtest_test');
        \Tx_Oelib_Db::delete('user_oelibtest_test', 'uid = ' . $latestUid);
        $this->subject->resetAutoIncrement('user_oelibtest_test');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForTableWithoutUidIsAllowed()
    {
        $this->subject->resetAutoIncrement('tx_oelib_test_article_mm');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForFeUsersTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('fe_users');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForPagesTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('pages');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForTtContentTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('tt_content');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithOtherSystemTableFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->resetAutoIncrement('sys_domains');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForSysFileTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_file');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForSysFileCollectionTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_file_collection');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForSysFileReferenceTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_file_reference');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForSysCategoryTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_category');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForSysCategoryRecordMmTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('sys_category_record_mm');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithEmptyTableNameFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->resetAutoIncrement('');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithForeignTableFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->resetAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithInexistentTableFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->resetAutoIncrement('tx_oelib_DOESNOTEXIST');
    }

    /*
     * Tests regarding setResetAutoIncrementThreshold
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setResetAutoIncrementThresholdForOneIsAllowed()
    {
        $this->subject->setResetAutoIncrementThreshold(1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setResetAutoIncrementThresholdFor100IsAllowed()
    {
        $this->subject->setResetAutoIncrementThreshold(100);
    }

    /**
     * @test
     */
    public function setResetAutoIncrementThresholdForZeroFails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$threshold must be > 0.'
        );

        $this->subject->setResetAutoIncrementThreshold(0);
    }

    /**
     * @test
     */
    public function setResetAutoIncrementThresholdForMinus1Fails()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$threshold must be > 0.'
        );

        $this->subject->setResetAutoIncrementThreshold(-1);
    }

    /*
     * Tests regarding createFrontEndPage()
     */

    /**
     * @test
     */
    public function frontEndPageCanBeCreated()
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function createFrontEndPageSetsCorrectDocumentType()
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(
            0,
            $uid
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'doktype',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            1,
            (int)$row['doktype']
        );
    }

    /**
     * @test
     */
    public function frontEndPageWillBeCreatedOnRootPage()
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(
            0,
            $uid
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            0,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function frontEndPageWillBeCleanedUp()
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndPageHasNoTitleByDefault()
    {
        $uid = $this->subject->createFrontEndPage();

        $row = \Tx_Oelib_Db::selectSingle(
            'title',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['title']
        );
    }

    /*
     * Tests regarding createSystemFolder()
     */

    /**
     * @test
     */
    public function systemFolderCanBeCreated()
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function createSystemFolderSetsCorrectDocumentType()
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(
            0,
            $uid
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'doktype',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            254,
            (int)$row['doktype']
        );
    }

    /**
     * @test
     */
    public function systemFolderWillBeCreatedOnRootPage()
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(
            0,
            $uid
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            0,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function systemFolderCanBeCreatedOnOtherPage()
    {
        $parent = $this->subject->createSystemFolder();
        $uid = $this->subject->createSystemFolder($parent);

        self::assertNotSame(
            0,
            $uid
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'pid',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            $parent,
            (int)$row['pid']
        );
    }

    /**
     * @test
     */
    public function systemFolderWillBeCleanedUp()
    {
        $uid = $this->subject->createSystemFolder();
        self::assertNotSame(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'pages',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function systemFolderHasNoTitleByDefault()
    {
        $uid = $this->subject->createSystemFolder();

        $row = \Tx_Oelib_Db::selectSingle(
            'title',
            'pages',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['title']
        );
    }

    /*
     * Tests regarding createTemplate()
     */

    /**
     * @test
     */
    public function templateCanBeCreatedOnNonRootPage()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        self::assertNotSame(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'sys_template',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function templateCannotBeCreatedOnRootPage()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$pageId must be > 0.'
        );

        $this->subject->createTemplate(0);
    }

    /**
     * @test
     */
    public function templateCannotBeCreatedWithNegativePageNumber()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$pageId must be > 0.'
        );

        $this->subject->createTemplate(-1);
    }

    /**
     * @test
     */
    public function templateWillBeCleanedUp()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        self::assertNotSame(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'sys_template',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConfig()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        $row = \Tx_Oelib_Db::selectSingle(
            'config',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertFalse(
            isset($row['config'])
        );
    }

    /**
     * @test
     */
    public function templateCanHaveConfig()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate(
            $pageId,
            ['config' => 'plugin.tx_oelib.test = 1']
        );
        $row = \Tx_Oelib_Db::selectSingle(
            'config',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertSame(
            'plugin.tx_oelib.test = 1',
            $row['config']
        );
    }

    /**
     * @test
     */
    public function templateConfigIsReadableAsTsTemplate()
    {
        $pageId = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageId,
            ['config' => 'plugin.tx_oelib.test = 42']
        );
        $templateHelper = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingTemplateHelper([]);
        $configuration = $templateHelper->retrievePageConfig($pageId);

        self::assertTrue(
            isset($configuration['test'])
        );
        self::assertSame(
            '42',
            $configuration['test']
        );
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConstants()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        $row = \Tx_Oelib_Db::selectSingle(
            'constants',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertFalse(
            isset($row['constants'])
        );
    }

    /**
     * @test
     */
    public function templateCanHaveConstants()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate(
            $pageId,
            ['constants' => 'plugin.tx_oelib.test = 1']
        );
        $row = \Tx_Oelib_Db::selectSingle(
            'constants',
            'sys_template',
            'uid = ' . $uid
        );

        self::assertSame(
            'plugin.tx_oelib.test = 1',
            $row['constants']
        );
    }

    /**
     * @test
     */
    public function templateConstantsAreUsedInTsSetup()
    {
        $pageId = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageId,
            [
                'constants' => 'plugin.tx_oelib.test = 42',
                'config' => 'plugin.tx_oelib.test = {$plugin.tx_oelib.test}',
            ]
        );
        $templateHelper = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingTemplateHelper([]);
        $configuration = $templateHelper->retrievePageConfig($pageId);

        self::assertTrue(
            isset($configuration['test'])
        );
        self::assertSame(
            '42',
            $configuration['test']
        );
    }

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
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function createDummyFileForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
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
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        $this->subject->createDummyFolder('test_folder');

        self::assertDirectoryExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function createDummyFolderForNonExistentUploadFolderSetCreatesFileInCreatedUploadFolder()
    {
        $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
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
            '/\\/uploads\\/tx_oelib\\/$/',
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

        $this->subject->getPathRelativeToUploadDirectory(PATH_site);
    }

    /*
     * Tests regarding createFrontEndUserGroup()
     */

    /**
     * @test
     */
    public function frontEndUserGroupCanBeCreated()
    {
        $uid = $this->subject->createFrontEndUserGroup();

        self::assertNotSame(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'fe_groups',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupTableWillBeCleanedUp()
    {
        $uid = $this->subject->createFrontEndUserGroup();
        self::assertNotSame(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'fe_groups',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupHasNoTitleByDefault()
    {
        $uid = $this->subject->createFrontEndUserGroup();

        $row = \Tx_Oelib_Db::selectSingle(
            'title',
            'fe_groups',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['title']
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupCanHaveTitle()
    {
        $uid = $this->subject->createFrontEndUserGroup(
            ['title' => 'Test title']
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'title',
            'fe_groups',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test title',
            $row['title']
        );
    }

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
     * Tests regarding createFrontEndUser()
     */

    /**
     * @test
     */
    public function frontEndUserCanBeCreated()
    {
        $uid = $this->subject->createFrontEndUser();

        self::assertNotSame(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'fe_users',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserTableWillBeCleanedUp()
    {
        $uid = $this->subject->createFrontEndUser();
        self::assertNotSame(
            0,
            $uid
        );

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords(
                'fe_users',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserHasNoUserNameByDefault()
    {
        $uid = $this->subject->createFrontEndUser();

        $row = \Tx_Oelib_Db::selectSingle(
            'username',
            'fe_users',
            'uid = ' . $uid
        );

        self::assertSame(
            '',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveUserName()
    {
        $uid = $this->subject->createFrontEndUser(
            '',
            ['username' => 'Test name']
        );

        $row = \Tx_Oelib_Db::selectSingle(
            'username',
            'fe_users',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test name',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveSeveralUserGroups()
    {
        $feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidThree = $this->subject->createFrontEndUserGroup();
        $uid = $this->subject->createFrontEndUser(
            $feUserGroupUidOne . ', ' . $feUserGroupUidTwo . ', ' . $feUserGroupUidThree
        );

        self::assertNotSame(
            0,
            $uid
        );

        self::assertSame(
            1,
            $this->subject->countRecords(
                'fe_users',
                'uid=' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUser('', ['uid' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoNonZeroUid()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "uid" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUser('', ['uid' => 99999]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "usergroup" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUser('', ['usergroup' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "usergroup" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUser('', ['usergroup' => 99999]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoUserGroupListInTheDataArray()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The column "usergroup" must not be set in $recordData.'
        );

        $this->subject->createFrontEndUser(
            '',
            ['usergroup' => '1,2,4,5']
        );
    }

    /**
     * @test
     */
    public function createFrontEndUserWithEmptyGroupCreatesGroup()
    {
        $this->subject->createFrontEndUser('');

        self::assertTrue(
            $this->subject->existsExactlyOneRecord('fe_groups')
        );
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.'
        );

        $feUserGroupUidOne = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidTwo = $this->subject->createFrontEndUserGroup();
        $feUserGroupUidThree = $this->subject->createFrontEndUserGroup();

        $this->subject->createFrontEndUser(
            $feUserGroupUidOne . ', ' . $feUserGroupUidTwo . ', 0, ' . $feUserGroupUidThree
        );
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.'
        );

        $feUserGroupUid = $this->subject->createFrontEndUserGroup();

        $this->subject->createFrontEndUser(
            $feUserGroupUid . ', abc'
        );
    }

    /*
     * Tests regarding createBackEndUser()
     */

    /**
     * @test
     */
    public function createBackEndUserReturnsUidGreaterZero()
    {
        self::assertNotSame(
            0,
            $this->subject->createBackEndUser()
        );
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesBackEndUserRecordInTheDatabase()
    {
        self::assertSame(
            1,
            $this->subject->countRecords(
                'be_users',
                'uid=' . $this->subject->createBackEndUser()
            )
        );
    }

    /**
     * @test
     */
    public function cleanUpCleansUpDirtyBackEndUserTable()
    {
        $uid = $this->subject->createBackEndUser();

        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->subject->countRecords('be_users', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesRecordWithoutUserNameByDefault()
    {
        $uid = $this->subject->createBackEndUser();

        $row = \Tx_Oelib_Db::selectSingle('username', 'be_users', 'uid = ' . $uid);

        self::assertSame(
            '',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName()
    {
        $uid = $this->subject->createBackEndUser(['username' => 'Test name']);

        $row = \Tx_Oelib_Db::selectSingle('username', 'be_users', 'uid = ' . $uid);

        self::assertSame(
            'Test name',
            $row['username']
        );
    }

    /**
     * @test
     */
    public function createBackEndUserWithZeroUidProvidedInRecordDataThrowsExeption()
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
    public function createBackEndUserWithNonZeroUidProvidedInRecordDataThrowsExeption()
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
    public function createFakeFrontEndCreatesGlobalFrontEnd()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(TypoScriptFrontendController::class, $GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsPositivePageUidIfCalledWithoutParameters()
    {
        $this->subject->createFrontEndPage();
        self::assertGreaterThan(
            0,
            $this->subject->createFakeFrontEnd()
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsCurrentFrontEndPageUid()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $result = $this->subject->createFakeFrontEnd();

        self::assertSame(
            $this->getFrontEndController()->id,
            $result
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesNullTimeTrackerInstance()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8000000) {
            self::markTestSkipped('This test is not applicable for TYPO3 >= 8.');
        }

        $GLOBALS['TT'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(NullTimeTracker::class, $GLOBALS['TT']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesTimeTrackerInstance()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 8000000) {
            self::markTestSkipped('This test is not applicable for TYPO3 < 8.');
        }

        $GLOBALS['TT'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(TimeTracker::class, $GLOBALS['TT']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesSysPage()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(PageRepository::class, $this->getFrontEndController()->sys_page);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesFrontEndUser()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(
            FrontendUserAuthentication::class,
            $this->getFrontEndController()->fe_user
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesContentObjectRenderer()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(ContentObjectRenderer::class, $this->getFrontEndController()->cObj);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesTemplate()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInstanceOf(TemplateService::class, $this->getFrontEndController()->tmpl);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReadsTypoScriptSetupFromPage()
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageUid,
            ['config' => 'foo = bar']
        );

        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame(
            'bar',
            $this->getFrontEndController()->tmpl->setup['foo']
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndWithTemplateRecordMarksTemplateAsLoaded()
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageUid,
            ['config' => 'foo = 42']
        );

        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame(
            1,
            $this->getFrontEndController()->tmpl->loaded
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesConfiguration()
    {
        $GLOBALS['TSFE'] = null;
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertInternalType(
            'array',
            $this->getFrontEndController()->config
        );
    }

    /**
     * @test
     */
    public function loginUserIsFalseAfterCreateFakeFrontEnd()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertFalse(
            $this->getFrontEndController()->loginUser
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsDefaultGroupList()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertSame(
            '0,-1',
            $this->getFrontEndController()->gr_list
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsProvidedPageUid()
    {
        $pageUid = $this->subject->createFrontEndPage();

        self::assertSame(
            $pageUid,
            $this->subject->createFakeFrontEnd($pageUid)
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId()
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame(
            $pageUid,
            $this->getFrontEndController()->id
        );
    }

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
    public function isLoggedInInitiallyIsFalse()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        self::assertFalse(
            $this->subject->isLoggedIn()
        );
    }

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
    public function logoutFrontEndUserAfterLoginSwitchesLoginManagerToNotLoggedIn()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->createAndLoginFrontEndUser();
        $this->subject->logoutFrontEndUser();

        self::assertFalse(
            \Tx_Oelib_FrontEndLoginManager::getInstance()->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function logoutFrontEndUserSetsLoginUserToFalse()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->logoutFrontEndUser();

        self::assertFalse(
            $this->getFrontEndController()->loginUser
        );
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

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function logoutFrontEndUserCanBeCalledTwoTimes()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->logoutFrontEndUser();
        $this->subject->logoutFrontEndUser();
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserCreatesFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->createAndLoginFrontEndUser();

        self::assertSame(
            1,
            $this->subject->countRecords('fe_users')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->createAndLoginFrontEndUser(
            '',
            ['name' => 'John Doe']
        );

        self::assertSame(
            1,
            $this->subject->countRecords('fe_users', 'name = "John Doe"')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserLogsInFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $this->subject->createAndLoginFrontEndUser();

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertSame(
            1,
            $this->subject->countRecords('fe_users')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $frontEndUserUid = $this->subject->createAndLoginFrontEndUser(
            $frontEndUserGroupUid
        );

        $dbResultRow = \Tx_Oelib_Db::selectSingle(
            'usergroup',
            'fe_users',
            'uid = ' . $frontEndUserUid
        );

        self::assertSame(
            $frontEndUserGroupUid,
            (int)$dbResultRow['usergroup']
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupDoesNotCreateFrontEndUserGroup()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser(
            $frontEndUserGroupUid
        );

        self::assertSame(
            1,
            $this->subject->countRecords('fe_groups')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForExtensionTableReturnsDummyColumnName()
    {
        self::assertSame(
            'is_dummy_record',
            $this->subject->getDummyColumnName('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForSystemTableReturnsOelibPrefixedColumnName()
    {
        self::assertSame(
            'tx_oelib_is_dummy_record',
            $this->subject->getDummyColumnName('fe_users')
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForThirdPartyExtensionTableReturnsPrefixedColumnName()
    {
        $this->checkIfExtensionUserOelibtestIsLoaded();

        $testingFramework = new \Tx_Oelib_TestingFramework(
            'user_oelibtest',
            ['user_oelibtest2']
        );
        self::assertSame(
            'user_oelibtest_is_dummy_record',
            $testingFramework->getDummyColumnName('user_oelibtest2_test')
        );
    }
}
