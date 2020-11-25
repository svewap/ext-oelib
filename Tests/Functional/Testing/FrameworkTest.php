<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Oelib\Tests\Functional\Templating\Fixtures\TestingTemplateHelper;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrameworkTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/oelib',
        'typo3conf/ext/user_oelibtest',
        'typo3conf/ext/user_oelibtest2',
    ];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Oelib_TestingFramework('tx_oelib', ['user_oelibtest']);
    }

    /*
     * Utility functions.
     */

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController
     */
    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    private function getContext(): Context
    {
        return GeneralUtility::makeInstance(Context::class);
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
    private function getSortingOfRelation(int $uidLocal, int $uidForeign): int
    {
        $row = $this->getDatabaseConnection()->selectSingleRow(
            'sorting',
            'tx_oelib_test_article_mm',
            'uid_local = ' . $uidLocal . ' AND uid_foreign = ' . $uidForeign
        );

        return (int)$row['sorting'];
    }

    /*
     * Tests regarding markTableAsDirty()
     */

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpNonSystemTable()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['is_dummy_record' => 1]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->markTableAsDirty('tx_oelib_test');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpSystemTable()
    {
        $this->getDatabaseConnection()->insertArray(
            'pages',
            ['tx_oelib_is_dummy_record' => 1]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->markTableAsDirty('pages');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpAdditionalAllowedTable()
    {
        $this->getDatabaseConnection()->insertArray(
            'user_oelibtest_test',
            ['tx_oelib_is_dummy_record' => 1]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->markTableAsDirty('user_oelibtest_test');
        $this->subject->cleanUp();
        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'user_oelibtest_test', 'uid=' . $uid)
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
        $title = 'TEST record';
        $this->subject->createRecord(
            'user_oelibtest_test',
            [
                'title' => $title,
            ]
        );
    }

    /**
     * @test
     */
    public function createRecordCanCreateHiddenRecord()
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['hidden' => 1]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid);
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function createRecordCanCreateDeletedRecord()
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['deleted' => 1]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid);
        self::assertSame(1, $count);
    }

    /**
     * @return bool[][]
     */
    public function booleanDataProvider(): array
    {
        return [
            'false' => [false],
            'true' => [true],
        ];
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function createRecordPersistsBooleansAsIntegers(bool $value)
    {
        $this->subject->createRecord('tx_oelib_test', ['bool_data1' => $value]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'bool_data1 = ' . (int)$value);
        self::assertSame(1, $count);
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
            $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $pid . ' AND title="bar"')
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

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function changeRecordPersistsBooleansAsIntegers(bool $value)
    {
        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->changeRecord('tx_oelib_test', $uid, ['bool_data1' => $value]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'bool_data1 = ' . (int)$value);
        self::assertSame(1, $count);
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
            $this->getDatabaseConnection()->selectCount(
                '*',
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidLocal must be > 0, but is: 0');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', 0, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithZeroSecondUid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidForeign must be > 0, but is: 0');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, 0);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeFirstUid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidLocal must be > 0, but is: -1');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', -1, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeSecondUid()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidForeign must be > 0, but is: -1');

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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $count = $this->getDatabaseConnection()->selectCount(
            '*',
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $count = $this->getDatabaseConnection()->selectCount(
            '*',
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
        $this->getDatabaseConnection()->insertArray(
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
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test'),
            'Some test records were not deleted from table "tx_oelib_test"'
        );

        // Checks whether the second dummy record still exists.
        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test_article_mm')
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
        if (Typo3Version::isNotHigherThan(8)) {
            $this->subject->setUploadFolderPath(PATH_site . 'typo3temp/tx_oelib_test/');
        } else {
            $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        }
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());

        $this->subject->cleanUp();

        self::assertDirectoryNotExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function cleanUpWillCleanUpHiddenRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);
        $this->subject->markTableAsDirty('tx_oelib_test');

        $this->subject->cleanUp();

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'hidden = 1');
        self::assertSame(0, $count);
    }

    /**
     * @test
     */
    public function cleanUpWillCleanUpDeletedRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['deleted' => 1, 'is_dummy_record' => 1]);
        $this->subject->markTableAsDirty('tx_oelib_test');

        $this->subject->cleanUp();

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'deleted = 1');
        self::assertSame(0, $count);
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
        $this->getDatabaseConnection()->insertArray(
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
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test'),
            'Some test records were not deleted from table "tx_oelib_test"'
        );
    }

    /*
     * Tests regarding getAutoIncrement()
     */

    /**
     * @test
     */
    public function getAutoIncrementReturnsOneForTruncatedTable()
    {
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
        // should be equal to the current auto increment value.
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
    public function getAutoIncrementForAllowedTableIsAllowed()
    {
        $this->subject->getAutoIncrement('fe_users');
        $this->subject->getAutoIncrement('pages');
        $this->subject->getAutoIncrement('tt_content');
        $this->subject->getAutoIncrement('sys_file');
        $this->subject->getAutoIncrement('sys_file_collection');
        $this->subject->getAutoIncrement('sys_file_reference');
        $this->subject->getAutoIncrement('sys_category');
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
    public function countRecordsWithEmptyOrMissingWhereClauseIsAllowed()
    {
        $this->subject->countRecords('tx_oelib_test', '');
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
    public function countRecordsWithAllowedTableIsAllowed()
    {
        $this->subject->countRecords('fe_groups');
        $this->subject->countRecords('fe_users');
        $this->subject->countRecords('pages');
        $this->subject->countRecords('tt_content');
        $this->subject->countRecords('sys_file');
        $this->subject->countRecords('sys_file_collection');
        $this->subject->countRecords('sys_file_reference');
        $this->subject->countRecords('sys_category');
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
     */
    public function countRecordsIgnoresNonDummyRecords()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->countRecords(
            'tx_oelib_test',
            'title = "foo"'
        );

        self::assertSame(
            0,
            $testResult
        );
    }

    /**
     * @test
     */
    public function countRecordsCanFindHiddenRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);

        self::assertSame(1, $this->subject->countRecords('tx_oelib_test'));
    }

    /**
     * @test
     */
    public function countRecordsCanFindDeletedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['deleted' => 1, 'is_dummy_record' => 1]);

        self::assertSame(1, $this->subject->countRecords('tx_oelib_test'));
    }

    /*
     * Tests regarding count()
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithEmptyOrMissingWhereClauseIsAllowed()
    {
        $this->subject->count('tx_oelib_test', []);
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
    public function countWithAllowedTableIsAllowed()
    {
        $this->subject->count('fe_groups');
        $this->subject->count('fe_users');
        $this->subject->count('pages');
        $this->subject->count('tt_content');
        $this->subject->count('sys_file');
        $this->subject->count('sys_file_collection');
        $this->subject->count('sys_file_reference');
        $this->subject->count('sys_category');
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
     */
    public function countIgnoresNonDummyRecords()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->count('tx_oelib_test', ['title' => 'foo']);

        self::assertSame(
            0,
            $testResult
        );
    }

    /**
     * @test
     */
    public function countCanFindHiddenRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);

        self::assertSame(1, $this->subject->count('tx_oelib_test'));
    }

    /**
     * @test
     */
    public function countCanFindDeletedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['deleted' => 1, 'is_dummy_record' => 1]);

        self::assertSame(1, $this->subject->count('tx_oelib_test'));
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function countCanFindWithBooleanValues(bool $value)
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['bool_data1' => (int)$value, 'is_dummy_record' => 1]
        );

        $result = $this->subject->count('tx_oelib_test', ['bool_data1' => $value]);

        self::assertSame(1, $result);
    }

    /*
     * Tests regarding existsRecord()
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function existsRecordWithEmptyOrMissingWhereClauseIsAllowed()
    {
        $this->subject->existsRecord('tx_oelib_test', '');
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
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsRecord(
            'tx_oelib_test',
            'title = "foo"'
        );

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
    public function existsRecordWithUidForNoMatchReturnsFalse()
    {
        $uid = $this->subject->createRecord('tx_oelib_test');
        $this->getDatabaseConnection()->delete('tx_oelib_test', ['uid' => $uid]);

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
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $testResult = $this->subject->existsRecordWithUid(
            'tx_oelib_test',
            $uid
        );

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
    public function existsExactlyOneRecordWithEmptyOrMissingWhereClauseIsAllowed()
    {
        $this->subject->existsExactlyOneRecord('tx_oelib_test', '');
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
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        $testResult = $this->subject->existsExactlyOneRecord(
            'tx_oelib_test',
            'title = "foo"'
        );

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
        $this->getDatabaseConnection()->delete('tx_oelib_test', ['uid' => $latestUid]);
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
        // Creates and deletes a record and then resets the auto increment.
        $latestUid = $this->subject->createRecord('user_oelibtest_test');
        $this->getDatabaseConnection()->delete('user_oelibtest_test', ['uid' => $latestUid]);
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
    public function resetAutoIncrementForAllowedTableIsAllowed()
    {
        $this->subject->resetAutoIncrement('fe_users');
        $this->subject->resetAutoIncrement('pages');
        $this->subject->resetAutoIncrement('tt_content');
        $this->subject->resetAutoIncrement('sys_file');
        $this->subject->resetAutoIncrement('sys_file_collection');
        $this->subject->resetAutoIncrement('sys_file_reference');
        $this->subject->resetAutoIncrement('sys_category');
        $this->subject->resetAutoIncrement('sys_category_record_mm');
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
    public function setResetAutoIncrementThresholdForOneAndOndHundredIsAllowed()
    {
        $this->subject->setResetAutoIncrementThreshold(1);
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
            $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $uid)
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
            $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function frontEndPageHasNoTitleByDefault()
    {
        $uid = $this->subject->createFrontEndPage();

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
            $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $uid)
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
            $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function systemFolderHasNoTitleByDefault()
    {
        $uid = $this->subject->createSystemFolder();

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
            $this->getDatabaseConnection()->selectCount('*', 'sys_template', 'uid=' . $uid)
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
            $this->getDatabaseConnection()->selectCount('*', 'sys_template', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConfig()
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        $row = $this->getDatabaseConnection()->selectSingleRow(
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
        $row = $this->getDatabaseConnection()->selectSingleRow(
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
        $configuration = (new TestingTemplateHelper([]))->retrievePageConfig($pageId);

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
        $row = $this->getDatabaseConnection()->selectSingleRow(
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
        $row = $this->getDatabaseConnection()->selectSingleRow(
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
        $configuration = (new TestingTemplateHelper([]))->retrievePageConfig($pageId);

        self::assertTrue(
            isset($configuration['test'])
        );
        self::assertSame(
            '42',
            $configuration['test']
        );
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_groups', 'uid=' . $uid)
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_groups', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function frontEndUserGroupHasNoTitleByDefault()
    {
        $uid = $this->subject->createFrontEndUserGroup();

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
            'title',
            'fe_groups',
            'uid = ' . $uid
        );

        self::assertSame(
            'Test title',
            $row['title']
        );
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_users', 'uid=' . $uid)
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_users', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function frontEndUserHasNoUserNameByDefault()
    {
        $uid = $this->subject->createFrontEndUser();

        $row = $this->getDatabaseConnection()->selectSingleRow(
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_users', 'uid=' . $uid)
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
            $this->getDatabaseConnection()->selectCount(
                '*',
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
            $this->getDatabaseConnection()->selectCount('*', 'be_users', 'uid=' . $uid)
        );
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesRecordWithoutUserNameByDefault()
    {
        $uid = $this->subject->createBackEndUser();

        $row = $this->getDatabaseConnection()->selectSingleRow('username', 'be_users', 'uid = ' . $uid);

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

        $row = $this->getDatabaseConnection()->selectSingleRow('username', 'be_users', 'uid = ' . $uid);

        self::assertSame(
            'Test name',
            $row['username']
        );
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

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9004000) {
            $isLoggedIn = $this->getFrontEndController()->loginUser;
        } else {
            $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        }

        self::assertFalse($isLoggedIn);
    }

    /**
     * @test
     */
    public function createFakeFrontEndSetsDefaultGroupList()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9004000) {
            $groups = GeneralUtility::intExplode(',', $this->getFrontEndController()->gr_list);
        } else {
            $groups = (array)$this->getContext()->getPropertyFromAspect('frontend.user', 'groupIds');
        }

        self::assertSame([0, -1], $groups);
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

        self::assertFalse($this->subject->isLoggedIn());
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

        self::assertFalse(\Tx_Oelib_FrontEndLoginManager::getInstance()->isLoggedIn());
    }

    /**
     * @test
     */
    public function logoutFrontEndUserSetsLoginUserToFalse()
    {
        $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd();

        $this->subject->logoutFrontEndUser();

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) < 9004000) {
            $isLoggedIn = $this->getFrontEndController()->loginUser;
        } else {
            $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');
        }

        self::assertFalse($isLoggedIn);
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_users')
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_users', 'name = "John Doe"')
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

        self::assertTrue($this->subject->isLoggedIn());
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_users')
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

        $row = $this->getDatabaseConnection()->selectSingleRow(
            'usergroup',
            'fe_users',
            'uid = ' . $frontEndUserUid
        );

        self::assertSame(
            $frontEndUserGroupUid,
            (int)$row['usergroup']
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
            $this->getDatabaseConnection()->selectCount('*', 'fe_groups')
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

        self::assertTrue($this->subject->isLoggedIn());
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
