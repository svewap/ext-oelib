<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Testing\TestingFramework;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @covers \OliverKlee\Oelib\Testing\TestingFramework
 */
final class TestingFrameworkTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/oelib',
        'typo3conf/ext/user_oelibtest',
        'typo3conf/ext/user_oelibtest2',
    ];

    /**
     * @var TestingFramework
     */
    private $subject = null;

    protected function setUp(): void
    {
        $GLOBALS['TSFE'] = null;
        parent::setUp();

        $this->subject = new TestingFramework('tx_oelib', ['user_oelibtest']);
    }

    protected function tearDown(): void
    {
        $this->subject->cleanUpWithoutDatabase();
    }

    // Utility functions.

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

    // Tests regarding markTableAsDirty()

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpNonSystemTable(): void
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
    public function markTableAsDirtyWillCleanUpSystemTable(): void
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
    public function markTableAsDirtyWillCleanUpAdditionalAllowedTable(): void
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
    public function markTableAsDirtyFailsOnInexistentTable(): void
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
    public function markTableAsDirtyFailsOnNotAllowedSystemTable(): void
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
    public function markTableAsDirtyFailsOnForeignTable(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The table name "tx_seminars_seminars" is not allowed for markTableAsDirty.'
        );
        $this->subject->markTableAsDirty('tx_seminars_seminars');
    }

    // Tests regarding createRecord()

    /**
     * @test
     */
    public function createRecordOnValidTableWithNoData(): void
    {
        self::assertNotSame(
            0,
            $this->subject->createRecord('tx_oelib_test', [])
        );
    }

    /**
     * @test
     */
    public function createRecordWithValidData(): void
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
    public function createRecordOnInvalidTable(): void
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
    public function createRecordWithEmptyTableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table name "" is not allowed.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->createRecord('', []);
    }

    /**
     * @test
     */
    public function createRecordWithUidFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "uid" must not be set in $recordData.');

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
    public function createRecordOnValidAdditionalAllowedTableWithValidDataSucceeds(): void
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
    public function createRecordCanCreateHiddenRecord(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['hidden' => 1]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid);
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function createRecordCanCreateDeletedRecord(): void
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
    public function createRecordPersistsBooleansAsIntegers(bool $value): void
    {
        $this->subject->createRecord('tx_oelib_test', ['bool_data1' => $value]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'bool_data1 = ' . (int)$value);
        self::assertSame(1, $count);
    }

    // Tests regarding changeRecord()

    /**
     * @test
     */
    public function changeRecordWithExistingRecord(): void
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
    public function changeRecordFailsOnForeignTable(): void
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
    public function changeRecordFailsOnInexistentTable(): void
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
    public function changeRecordOnAllowedSystemTableForPages(): void
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
    public function changeRecordFailsOnOtherSystemTable(): void
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
    public function changeRecordOnAdditionalAllowedTableSucceeds(): void
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
    public function changeRecordFailsWithUidZero(): void
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
    public function changeRecordFailsWithEmptyData(): void
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
    public function changeRecordFailsWithUidFieldInRecordData(): void
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
    public function changeRecordFailsWithDummyRecordFieldInRecordData(): void
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
    public function changeRecordFailsOnInexistentRecord(): void
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
    public function changeRecordPersistsBooleansAsIntegers(bool $value): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->changeRecord('tx_oelib_test', $uid, ['bool_data1' => $value]);

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'bool_data1 = ' . (int)$value);
        self::assertSame(1, $count);
    }

    // Tests regarding createRelation()

    /**
     * @test
     */
    public function createRelationWithValidData(): void
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
    public function createRelationWithValidDataOnAdditionalAllowedTableSucceeds(): void
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
    public function createRelationWithInvalidTable(): void
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
    public function createRelationWithEmptyTableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table name "" is not allowed.');
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->createRelation('', 99999, 199999);
    }

    /**
     * @test
     */
    public function createRelationWithZeroFirstUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidLocal must be > 0, but is: 0');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', 0, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithZeroSecondUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidForeign must be > 0, but is: 0');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, 0);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeFirstUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidLocal must be > 0, but is: -1');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', -1, $uid);
    }

    /**
     * @test
     */
    public function createRelationWithNegativeSecondUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uidForeign must be > 0, but is: -1');

        $uid = $this->subject->createRecord('tx_oelib_test');

        $this->subject->createRelation('tx_oelib_test_article_mm', $uid, -1);
    }

    /**
     * @test
     */
    public function createRelationWithAutomaticSorting(): void
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

    // Tests regarding createRelationFromTca()

    /**
     * @test
     */
    public function createRelationAndUpdateCounterIncreasesZeroValueCounterByOne(): void
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
    public function createRelationAndUpdateCounterIncreasesNonZeroValueCounterToOne(): void
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
    public function createRelationAndUpdateCounterCreatesRecordInRelationTable(): void
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
    public function createRelationAndUpdateCounterWithBidirectionalRelationIncreasesCounter(): void
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
    public function createRelationAndUpdateCounterWithBidirectionalIncreasesOppositeFieldCounterInForeignTable(): void
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
    public function createRelationAndUpdateCounterWithBidirectionalRelationCreatesRecordInRelationTable(): void
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

    // Tests regarding cleanUp()

    /**
     * @test
     */
    public function cleanUpWithRegularCleanUpDeletesTestsRecords(): void
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
    public function cleanUpDeletesCreatedDummyFile(): void
    {
        $fileName = $this->subject->createDummyFile();

        $this->subject->cleanUp();

        self::assertFileNotExists($fileName);
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedDummyFolder(): void
    {
        $folderName = $this->subject->createDummyFolder('test_folder');

        $this->subject->cleanUp();

        self::assertFileNotExists($folderName);
    }

    /**
     * @test
     */
    public function cleanUpDeletesCreatedNestedDummyFolders(): void
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
    public function cleanUpDeletesCreatedDummyUploadFolder(): void
    {
        $this->subject->setUploadFolderPath(Environment::getPublicPath() . '/typo3temp/tx_oelib_test/');
        $this->subject->createDummyFile();

        self::assertDirectoryExists($this->subject->getUploadFolderPath());

        $this->subject->cleanUp();

        self::assertDirectoryNotExists($this->subject->getUploadFolderPath());
    }

    /**
     * @test
     */
    public function cleanUpWillCleanUpHiddenRecords(): void
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
    public function cleanUpWillCleanUpDeletedRecords(): void
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['deleted' => 1, 'is_dummy_record' => 1]);
        $this->subject->markTableAsDirty('tx_oelib_test');

        $this->subject->cleanUp();

        $count = $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'deleted = 1');
        self::assertSame(0, $count);
    }

    /**
     * @test
     */
    public function cleanUpRestoresCurrentScriptAfterCreateFakeFrontEnd(): void
    {
        $previous = Environment::getCurrentScript();
        $this->subject->createFakeFrontEnd();

        $this->subject->cleanUp();

        self::assertSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function cleanUpRestoresHttpHostAfterCreateFakeFrontEnd(): void
    {
        $previous = $GLOBALS['_SERVER']['HTTP_HOST'];
        $this->subject->createFakeFrontEnd();

        $this->subject->cleanUp();

        self::assertSame($previous, $GLOBALS['_SERVER']['HTTP_HOST']);
    }

    /**
     * @test
     */
    public function cleanUpUnsetsGlobalRequest(): void
    {
        $this->subject->createFakeFrontEnd();
        $GLOBALS['TYPO3_REQUEST'] = $this->prophesize(ServerRequestInterface::class)->reveal();

        $this->subject->cleanUp();

        self::assertNull($GLOBALS['TYPO3_REQUEST'] ?? null);
    }

    /**
     * @test
     */
    public function cleanUpReplacesExistingSystemEnvironmentVariables(): void
    {
        $this->subject->createFakeFrontEnd();
        $GLOBALS['_SERVER']['QUERY_STRING'] = 'hello.php';
        $previous = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');

        $this->subject->cleanUp();

        self::assertNotSame($previous, GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'));
    }

    // Tests regarding cleanUpWithoutDatabase()

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseWithRegularCleanUpNotDeletesTestsRecords(): void
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

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseRestoresCurrentScriptAfterCreateFakeFrontEnd(): void
    {
        $previous = Environment::getCurrentScript();
        $this->subject->createFakeFrontEnd();

        $this->subject->cleanUpWithoutDatabase();

        self::assertSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseRestoresHttpHostAfterCreateFakeFrontEnd(): void
    {
        $previous = $GLOBALS['_SERVER']['HTTP_HOST'];
        $this->subject->createFakeFrontEnd();

        $this->subject->cleanUpWithoutDatabase();

        self::assertSame($previous, $GLOBALS['_SERVER']['HTTP_HOST']);
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseUnsetsGlobalRequest(): void
    {
        $this->subject->createFakeFrontEnd();
        $GLOBALS['TYPO3_REQUEST'] = $this->prophesize(ServerRequestInterface::class)->reveal();

        $this->subject->cleanUpWithoutDatabase();

        self::assertNull($GLOBALS['TYPO3_REQUEST'] ?? null);
    }

    /**
     * @test
     */
    public function cleanUpWithoutDatabaseReplacesExistingSystemEnvironmentVariables(): void
    {
        $this->subject->createFakeFrontEnd();
        $GLOBALS['_SERVER']['QUERY_STRING'] = 'hello.php';
        $previous = GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST');

        $this->subject->cleanUpWithoutDatabase();

        self::assertNotSame($previous, GeneralUtility::getIndpEnv('TYPO3_REQUEST_HOST'));
    }

    // Tests regarding getAutoIncrement()

    /**
     * @test
     */
    public function getAutoIncrementReturnsOneForTruncatedTable(): void
    {
        self::assertSame(
            1,
            $this->subject->getAutoIncrement('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function getAutoIncrementGetsCurrentAutoIncrement(): void
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
    public function getAutoIncrementForAllowedTableIsAllowed(): void
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
    public function getAutoIncrementWithOtherSystemTableFails(): void
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
    public function getAutoIncrementForSysCategoryRecordMmFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->getAutoIncrement('sys_category_record_mm');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithEmptyTableNameFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->getAutoIncrement('');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithForeignTableFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->getAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithInexistentTableFails(): void
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
    public function getAutoIncrementWithTableWithoutUidFails(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );
        $this->subject->getAutoIncrement('tx_oelib_test_article_mm');
    }

    // Tests regarding count()

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countWithEmptyOrMissingWhereClauseIsAllowed(): void
    {
        $this->subject->count('tx_oelib_test', []);
        $this->subject->count('tx_oelib_test');
    }

    /**
     * @test
     */
    public function countWithEmptyTableNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->count('');
    }

    /**
     * @test
     */
    public function countWithInvalidTableNameThrowsException(): void
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
    public function countWithAllowedTableIsAllowed(): void
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
    public function countWithOtherTableThrowsException(): void
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
    public function countReturnsZeroForNoMatches(): void
    {
        self::assertSame(
            0,
            $this->subject->count('tx_oelib_test', ['title' => 'foo'])
        );
    }

    /**
     * @test
     */
    public function countReturnsOneForOneDummyRecordMatch(): void
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
    public function countWithMissingWhereClauseReturnsOneForOneDummyRecordMatch(): void
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
    public function countReturnsTwoForTwoMatches(): void
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
    public function countIgnoresNonDummyRecords(): void
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
    public function countCanFindHiddenRecord(): void
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);

        self::assertSame(1, $this->subject->count('tx_oelib_test'));
    }

    /**
     * @test
     */
    public function countCanFindDeletedRecord(): void
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
    public function countCanFindWithBooleanValues(bool $value): void
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['bool_data1' => (int)$value, 'is_dummy_record' => 1]
        );

        $result = $this->subject->count('tx_oelib_test', ['bool_data1' => $value]);

        self::assertSame(1, $result);
    }

    // Tests regarding existsRecordWithUid()

    /**
     * @test
     */
    public function existsRecordWithUidWithZeroUidThrowsException(): void
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
    public function existsRecordWithUidWithNegativeUidThrowsException(): void
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
    public function existsRecordWithUidWithEmptyTableNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->existsRecordWithUid('', 1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given table name is not in the list of allowed tables.');

        $table = 'foo_bar';
        $this->subject->existsRecordWithUid($table, 1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidForNoMatchReturnsFalse(): void
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
    public function existsRecordWithUidForMatchReturnsTrue(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test');

        self::assertTrue(
            $this->subject->existsRecordWithUid('tx_oelib_test', $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidIgnoresNonDummyRecords(): void
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

    // Tests regarding resetAutoIncrement()

    /**
     * @test
     */
    public function resetAutoIncrementForTestTableSucceeds(): void
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
    public function resetAutoIncrementForUnchangedTestTableCanBeRun(): void
    {
        $this->subject->resetAutoIncrement('tx_oelib_test');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForAdditionalAllowedTableSucceeds(): void
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
    public function resetAutoIncrementForTableWithoutUidIsAllowed(): void
    {
        $this->subject->resetAutoIncrement('tx_oelib_test_article_mm');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetAutoIncrementForAllowedTableIsAllowed(): void
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
    public function resetAutoIncrementWithOtherSystemTableFails(): void
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
    public function resetAutoIncrementWithEmptyTableNameFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->resetAutoIncrement('');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithForeignTableFails(): void
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
    public function resetAutoIncrementWithInexistentTableFails(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The given table name is invalid. This means it is either empty or not in the list of allowed tables.'
        );

        $this->subject->resetAutoIncrement('tx_oelib_DOESNOTEXIST');
    }

    // Tests regarding setResetAutoIncrementThreshold

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setResetAutoIncrementThresholdForOneAndOndHundredIsAllowed(): void
    {
        $this->subject->setResetAutoIncrementThreshold(1);
        $this->subject->setResetAutoIncrementThreshold(100);
    }

    /**
     * @test
     */
    public function setResetAutoIncrementThresholdForZeroFails(): void
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
    public function setResetAutoIncrementThresholdForMinus1Fails(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$threshold must be > 0.'
        );

        $this->subject->setResetAutoIncrementThreshold(-1);
    }

    // Tests regarding createFrontEndPage()

    /**
     * @test
     */
    public function createFrontEndPageCreatesFrontEndPageAndReturnsItsUid(): void
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(0, $uid);
        self::assertSame(1, $this->getDatabaseConnection()->selectCount('*', 'pages', 'uid=' . $uid));
    }

    /**
     * @test
     */
    public function createFrontEndPageByDefaultPopulatesSlugWithPageUid(): void
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(0, $uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('slug', 'pages', 'uid = ' . $uid);

        self::assertSame('/' . $uid, $row['slug']);
    }

    /**
     * @test
     */
    public function createFrontEndPageSavesPageWithProvidedData(): void
    {
        $title = 'Test page';
        $uid = $this->subject->createFrontEndPage(0, ['title' => $title]);

        self::assertNotSame(0, $uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('title', 'pages', 'uid = ' . $uid);

        self::assertSame($title, $row['title']);
    }

    /**
     * @test
     */
    public function createFrontEndPageCanUseSlugFromProvidedData(): void
    {
        $slug = '/home';
        $uid = $this->subject->createFrontEndPage(0, ['slug' => $slug]);

        self::assertNotSame(0, $uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('slug', 'pages', 'uid = ' . $uid);

        self::assertSame($slug, $row['slug']);
    }

    /**
     * @test
     */
    public function createFrontEndPageSetsPageDocumentType(): void
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(0, $uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('doktype', 'pages', 'uid = ' . $uid);

        self::assertSame(1, (int)$row['doktype']);
    }

    /**
     * @test
     */
    public function createFrontEndPageByDefaultCreatesPageOnRootPage(): void
    {
        $uid = $this->subject->createFrontEndPage();

        self::assertNotSame(0, $uid);

        $row = $this->getDatabaseConnection()->selectSingleRow('pid', 'pages', 'uid = ' . $uid);

        self::assertSame(0, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function createFrontEndPageCanCreatePageOnOtherPage(): void
    {
        $parentUid = $this->subject->createFrontEndPage();
        $uid = $this->subject->createFrontEndPage($parentUid);

        $row = $this->getDatabaseConnection()->selectSingleRow('pid', 'pages', 'uid = ' . $uid);

        self::assertSame($parentUid, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function frontEndPageWillBeCleanedUp(): void
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
    public function frontEndPageHasNoTitleByDefault(): void
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

    // Tests regarding createSystemFolder()

    /**
     * @test
     */
    public function systemFolderCanBeCreated(): void
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
    public function createSystemFolderSetsCorrectDocumentType(): void
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
    public function systemFolderWillBeCreatedOnRootPage(): void
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
    public function systemFolderCanBeCreatedOnOtherPage(): void
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
    public function systemFolderWillBeCleanedUp(): void
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
    public function systemFolderHasNoTitleByDefault(): void
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

    // Tests regarding createTemplate()

    /**
     * @test
     */
    public function templateCanBeCreatedOnNonRootPage(): void
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
    public function templateCannotBeCreatedOnRootPage(): void
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
    public function templateCannotBeCreatedWithNegativePageNumber(): void
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
    public function templateWillBeCleanedUp(): void
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
    public function templateInitiallyHasNoConfig(): void
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
    public function templateCanHaveConfig(): void
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
    public function templateInitiallyHasNoConstants(): void
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
    public function templateCanHaveConstants(): void
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

    // Tests regarding createFrontEndUserGroup()

    /**
     * @test
     */
    public function frontEndUserGroupCanBeCreated(): void
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
    public function frontEndUserGroupTableWillBeCleanedUp(): void
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
    public function frontEndUserGroupHasNoTitleByDefault(): void
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
    public function frontEndUserGroupCanHaveTitle(): void
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

    // Tests regarding createFrontEndUser()

    /**
     * @test
     */
    public function frontEndUserCanBeCreated(): void
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
    public function frontEndUserTableWillBeCleanedUp(): void
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
    public function frontEndUserHasNoUserNameByDefault(): void
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
    public function frontEndUserCanHaveUserName(): void
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
    public function frontEndUserCanHaveSeveralUserGroups(): void
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
    public function frontEndUserMustHaveNoZeroUid(): void
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
    public function frontEndUserMustHaveNoNonZeroUid(): void
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
    public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray(): void
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
    public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray(): void
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
    public function frontEndUserMustHaveNoUserGroupListInTheDataArray(): void
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
    public function createFrontEndUserWithEmptyGroupCreatesGroup(): void
    {
        $this->subject->createFrontEndUser('');

        $count = $this->getDatabaseConnection()->selectCount('*', 'fe_users');
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided(): void
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
    public function frontEndUserMustHaveNoAlphabeticalCharactersInTheUserGroupList(): void
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

    // Tests regarding createBackEndUser()

    /**
     * @test
     */
    public function createBackEndUserReturnsUidGreaterZero(): void
    {
        self::assertNotSame(
            0,
            $this->subject->createBackEndUser()
        );
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesBackEndUserRecordInTheDatabase(): void
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
    public function cleanUpCleansUpDirtyBackEndUserTable(): void
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
    public function createBackEndUserCreatesRecordWithoutUserNameByDefault(): void
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
    public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName(): void
    {
        $uid = $this->subject->createBackEndUser(['username' => 'Test name']);

        $row = $this->getDatabaseConnection()->selectSingleRow('username', 'be_users', 'uid = ' . $uid);

        self::assertSame(
            'Test name',
            $row['username']
        );
    }

    // Tests concerning fakeFrontend

    /**
     * @test
     */
    public function createFakeFrontEndCreatesGlobalFrontEnd(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(TypoScriptFrontendController::class, $GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsCalledWithoutParametersReturnsZero(): void
    {
        $this->subject->createFrontEndPage();

        self::assertSame(0, $this->subject->createFakeFrontEnd());
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(
            FrontendUserAuthentication::class,
            $this->getFrontEndController()->fe_user
        );
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesContentObjectRenderer(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(ContentObjectRenderer::class, $this->getFrontEndController()->cObj);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesTemplate(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertInstanceOf(TemplateService::class, $this->getFrontEndController()->tmpl);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReadsTypoScriptSetupFromPage(): void
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
    public function createFakeFrontEndWithTemplateRecordMarksTemplateAsLoaded(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createTemplate(
            $pageUid,
            ['config' => 'foo = 42']
        );

        $this->subject->createFakeFrontEnd($pageUid);

        self::assertTrue($this->getFrontEndController()->tmpl->loaded);
    }

    /**
     * @test
     */
    public function createFakeFrontEndCreatesConfiguration(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertIsArray($this->getFrontEndController()->config);
    }

    /**
     * @test
     */
    public function loginUserIsFalseAfterCreateFakeFrontEnd(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');

        self::assertFalse($isLoggedIn);
    }

    /**
     * @test
     */
    public function createFakeFrontEndWithPageUidSetsDefaultGroupList(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $groups = (array)$this->getContext()->getPropertyFromAspect('frontend.user', 'groupIds');

        self::assertSame([0, -1], $groups);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReturnsProvidedPageUid(): void
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
    public function createFakeFrontEndUsesProvidedPageUidAsFrontEndId(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertSame($pageUid, (int)$this->getFrontEndController()->id);
    }

    /**
     * @test
     */
    public function getFakeFrontEndDomainReturnsDevDomain(): void
    {
        self::assertSame('typo3-test.dev', $this->subject->getFakeFrontEndDomain());
    }

    /**
     * @test
     */
    public function getFakeSiteUrlReturnsSiteUrlOfDevDomain(): void
    {
        self::assertSame('http://typo3-test.dev/', $this->subject->getFakeSiteUrl());
    }

    /**
     * @return array<string, array{0: string, 1: string|bool|null}>
     */
    public function globalsDataProvider(): array
    {
        return [
            'HTTP_HOST' => ['HTTP_HOST', 'typo3-test.dev'],
            'TYPO3_HOST_ONLY' => ['TYPO3_HOST_ONLY', 'typo3-test.dev'],
            'TYPO3_PORT' => ['TYPO3_PORT', ''],
            'QUERY_STRING' => ['QUERY_STRING', ''],
            'HTTP_REFERER' => ['HTTP_REFERER', 'http://typo3-test.dev/'],
            'TYPO3_REQUEST_HOST' => ['TYPO3_REQUEST_HOST', 'http://typo3-test.dev'],
            'TYPO3_REQUEST_SCRIPT' => ['TYPO3_REQUEST_SCRIPT', 'http://typo3-test.dev/index.php'],
            'TYPO3_REQUEST_DIR' => ['TYPO3_REQUEST_DIR', 'http://typo3-test.dev/'],
            'TYPO3_SITE_URL' => ['TYPO3_SITE_URL', 'http://typo3-test.dev/'],
            'TYPO3_SSL' => ['TYPO3_SSL', false],
            'TYPO3_REV_PROXY' => ['TYPO3_REV_PROXY', false],
            'SCRIPT_NAME' => ['SCRIPT_NAME', '/index.php'],
            'TYPO3_DOCUMENT_ROOT' => ['TYPO3_DOCUMENT_ROOT', '/var/www/html/public'],
            'SCRIPT_FILENAME' => ['SCRIPT_FILENAME', '/var/www/html/public/index.php'],
            'REMOTE_ADDR' => ['REMOTE_ADDR', '127.0.0.1'],
            'REMOTE_HOST' => ['REMOTE_HOST', ''],
            'HTTP_USER_AGENT' => [
                'HTTP_USER_AGENT',
                'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:93.0) Gecko/20100101 Firefox/93.0',
            ],
            'HTTP_ACCEPT_LANGUAGE' => ['HTTP_ACCEPT_LANGUAGE', 'de,en-US;q=0.7,en;q=0.3'],
            'HTTP_ACCEPT_ENCODING' => ['HTTP_ACCEPT_ENCODING', 'gzip, deflate, br'],
        ];
    }

    /**
     * @test
     *
     * @param string|bool|null $expected
     *
     * @dataProvider globalsDataProvider
     */
    public function createFakeFrontEndPopulatesGlobals(string $key, $expected): void
    {
        $this->subject->createFakeFrontEnd();

        self::assertSame($expected, GeneralUtility::getIndpEnv($key));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function pageSpecificGlobalsWithoutPageUidDataProvider(): array
    {
        return [
            'REQUEST_URI' => ['REQUEST_URI', '/'],
            'TYPO3_REQUEST_URL' => ['TYPO3_REQUEST_URL', 'http://typo3-test.dev/'],
            'TYPO3_SITE_SCRIPT' => ['TYPO3_SITE_SCRIPT', ''],
        ];
    }

    /**
     * @test
     *
     * @dataProvider pageSpecificGlobalsWithoutPageUidDataProvider
     */
    public function createFakeFrontWithWithoutPageUsesNoPagePageIdInUri(string $key, string $expected): void
    {
        $this->subject->createFakeFrontEnd();

        self::assertSame($expected, GeneralUtility::getIndpEnv($key));
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function pageSpecificGlobalsWithPageUidDataProvider(): array
    {
        return [
            'REQUEST_URI' => ['REQUEST_URI', '/%1s'],
            'TYPO3_REQUEST_URL' => ['TYPO3_REQUEST_URL', 'http://typo3-test.dev/%1s'],
            'TYPO3_SITE_SCRIPT' => ['TYPO3_SITE_SCRIPT', '%1s'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider pageSpecificGlobalsWithPageUidDataProvider
     */
    public function createFakeFrontWithWithPageUsesGivenPageInUri(string $key, string $expectedWithPlaceholder): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $expected = \sprintf($expectedWithPlaceholder, $pageUid);
        self::assertSame($expected, GeneralUtility::getIndpEnv($key));
    }

    /**
     * @test
     */
    public function fakeFrontEndAllowsCreatingTypoLinkToRootPage(): void
    {
        $rootPageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($rootPageUid);

        $typolinkUrl = $this->getFrontEndController()->cObj->typoLink_URL(['parameter' => $rootPageUid]);

        self::assertSame('/' . $rootPageUid, $typolinkUrl);
    }

    /**
     * @test
     */
    public function fakeFrontEndAllowsCreatingTypoLinkToSubpageOfRootPage(): void
    {
        $rootPageUid = $this->subject->createFrontEndPage();
        $subpageUid = $this->subject->createFrontEndPage($rootPageUid);
        $this->subject->createFakeFrontEnd($rootPageUid);

        $typolinkUrl = $this->getFrontEndController()->cObj->typoLink_URL(['parameter' => $subpageUid]);

        self::assertSame('/' . $subpageUid, $typolinkUrl);
    }

    /**
     * @test
     */
    public function fakeFrontEndAllowsLocationHeaderUrlWithLinkCreatedViaTypolink(): void
    {
        $rootPageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($rootPageUid);

        $typolinkUrl = $this->getFrontEndController()->cObj->typoLink_URL(['parameter' => $rootPageUid]);

        $expectedUrl = $this->subject->getFakeSiteUrl() . $rootPageUid;
        self::assertSame($expectedUrl, GeneralUtility::locationHeaderUrl($typolinkUrl));
    }

    /**
     * @test
     */
    public function createFakeFrontEndOverwritesCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();
        $this->subject->createFakeFrontEnd();

        self::assertNotSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function createFakeFrontSetsDummyGlobalHttpHost(): void
    {
        $expected = 'typo3-test.dev';
        $previous = $GLOBALS['_SERVER']['HTTP_HOST'];
        self::assertNotSame($expected, $previous);

        $this->subject->createFakeFrontEnd();

        self::assertSame($expected, $GLOBALS['_SERVER']['HTTP_HOST']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReplacesExistingGlobalRequest(): void
    {
        $previousRequest = $this->prophesize(ServerRequestInterface::class)->reveal();
        $GLOBALS['TYPO3_REQUEST'] = $previousRequest;

        $this->subject->createFakeFrontEnd();

        self::assertNotSame($previousRequest, $GLOBALS['TYPO3_REQUEST']);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReplacesExistingSystemEnvironmentVariables(): void
    {
        $GLOBALS['_SERVER']['QUERY_STRING'] = 'hello.php';
        $previous = GeneralUtility::getIndpEnv('QUERY_STRING');

        $this->subject->createFakeFrontEnd();

        self::assertNotSame($previous, GeneralUtility::getIndpEnv('QUERY_STRING'));
    }

    // Tests regarding user login and logout

    /**
     * @test
     */
    public function isLoggedInInitiallyIsFalse(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        self::assertFalse($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function logoutFrontEndUserAfterLoginSwitchesLoginManagerToNotLoggedIn(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->createAndLoginFrontEndUser();
        $this->subject->logoutFrontEndUser();

        self::assertFalse(FrontEndLoginManager::getInstance()->isLoggedIn());
    }

    /**
     * @test
     */
    public function logoutFrontEndUserSetsLoginUserToFalse(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->logoutFrontEndUser();

        $isLoggedIn = (bool)$this->getContext()->getPropertyFromAspect('frontend.user', 'isLoggedIn');

        self::assertFalse($isLoggedIn);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function logoutFrontEndUserCanBeCalledTwoTimes(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->logoutFrontEndUser();
        $this->subject->logoutFrontEndUser();
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserCreatesFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $this->subject->createAndLoginFrontEndUser();

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'fe_users')
        );
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
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
    public function createAndLoginFrontEndUserLogsInFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $this->subject->createAndLoginFrontEndUser();

        self::assertTrue($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
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
    public function createAndLoginFrontEndUserWithFrontEndUserGroupCreatesFrontEndUserWithGivenGroup(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
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
    public function createAndLoginFrontEndUserWithFrontEndUserGroupDoesNotCreateFrontEndUserGroup(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
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
    public function createAndLoginFrontEndUserWithFrontEndUserGroupLogsInFrontEndUser(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();
        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        self::assertTrue($this->subject->isLoggedIn());
    }

    /**
     * @test
     */
    public function getDummyColumnNameForExtensionTableReturnsDummyColumnName(): void
    {
        self::assertSame(
            'is_dummy_record',
            $this->subject->getDummyColumnName('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForSystemTableReturnsOelibPrefixedColumnName(): void
    {
        self::assertSame(
            'tx_oelib_is_dummy_record',
            $this->subject->getDummyColumnName('fe_users')
        );
    }

    /**
     * @test
     */
    public function getDummyColumnNameForThirdPartyExtensionTableReturnsPrefixedColumnName(): void
    {
        $testingFramework = new TestingFramework(
            'user_oelibtest',
            ['user_oelibtest2']
        );
        self::assertSame(
            'user_oelibtest_is_dummy_record',
            $testingFramework->getDummyColumnName('user_oelibtest2_test')
        );
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: class-string<AbstractBackend>}>
     */
    public function coreCachesVersion10DataProvider(): array
    {
        return [
            'assets' => ['assets', SimpleFileBackend::class],
            'core' => ['extbase', SimpleFileBackend::class],
            'extbase' => ['extbase', SimpleFileBackend::class],
            'fluid_template' => ['fluid_template', SimpleFileBackend::class],
            'hash' => ['hash', SimpleFileBackend::class],
            'imagesizes' => ['imagesizes', NullBackend::class],
            'l10n' => ['l10n', SimpleFileBackend::class],
            'pages' => ['pages', NullBackend::class],
            'pagesection' => ['pagesection', NullBackend::class],
            'rootline' => ['rootline', NullBackend::class],
            'runtime' => ['runtime', TransientMemoryBackend::class],
        ];
    }

    /**
     * @test
     *
     * @param class-string<AbstractBackend> $backend
     * @dataProvider coreCachesVersion10DataProvider
     */
    public function disableCoreCachesSetsAllCoreCachesForVersion10(string $identifier, string $backend): void
    {
        $this->subject->disableCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf($backend, $cache->getBackend());
    }
}
