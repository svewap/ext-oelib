<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\Testing\TestingFramework;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Testing\TestingFramework
 * @covers \OliverKlee\Oelib\Testing\TestingFrameworkCleanup
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
final class TestingFrameworkTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingFramework
     */
    private $subject = null;

    protected function setUp(): void
    {
        $GLOBALS['TSFE'] = null;
        parent::setUp();

        $this->subject = new TestingFramework('tx_oelib');
    }

    protected function tearDown(): void
    {
        $this->subject->cleanUpWithoutDatabase();
        parent::tearDown();
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
     * the first parameter `$uidLocal` and the foreign UID given by the second
     * parameter `$uidForeign`.
     */
    private function getSortingOfRelation(int $uidLocal, int $uidForeign): int
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_oelib_test_article_mm');
        $result = $connection
            ->select(['*'], 'tx_oelib_test_article_mm', ['uid_local' => $uidLocal, 'uid_foreign' => $uidForeign]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $data */
            $data = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $data */
            $data = $result->fetch();
        }
        self::assertIsArray($data);

        return (int)$data['sorting'];
    }

    // Tests regarding markTableAsDirty()

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpNonSystemTable(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['is_dummy_record' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->markTableAsDirty('tx_oelib_test');
        $this->subject->cleanUp();

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['uid' => $uid])
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyWillCleanUpSystemTable(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        $connection->insert('pages', ['tx_oelib_is_dummy_record' => 1]);
        $uid = (int)$connection->lastInsertId('pages');

        $this->subject->markTableAsDirty('pages');
        $this->subject->cleanUp();

        self::assertSame(
            0,
            $connection->count('*', 'pages', ['uid' => $uid])
        );
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsOnInexistentTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');
        $this->subject->markTableAsDirty('tx_oelib_DOESNOTEXIST');
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsOnNotAllowedSystemTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "sys_domain" is not allowed.');
        $this->subject->markTableAsDirty('sys_domain');
    }

    /**
     * @test
     */
    public function markTableAsDirtyFailsOnForeignTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_seminars_seminars" is not allowed.');
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame($title, $row['title']);
    }

    /**
     * @test
     */
    public function createRecordOnInvalidTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');
        $this->subject->createRecord('tx_oelib_DOESNOTEXIST', []);
    }

    /**
     * @test
     */
    public function createRecordWithEmptyTableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "" is not allowed.');

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
     */
    public function createRecordCanCreateHiddenRecord(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['hidden' => 1]);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE uid = :uid AND hidden = :hidden';
        $queryResult = $connection->executeQuery($query, ['uid' => $uid, 'hidden' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
    }

    /**
     * @test
     */
    public function createRecordCanCreateDeletedRecord(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['deleted' => 1]);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $connection->executeQuery($query, ['uid' => $uid, 'deleted' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
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

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['bool_data1' => (int)$value])
        );
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('bar', $row['title']);
    }

    /**
     * @test
     */
    public function changeRecordFailsOnForeignTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_seminars_seminars" is not allowed.');
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');
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

        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(
            1,
            $connection->count('*', 'pages', ['uid' => $pid, 'title' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsOnOtherSystemTable(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "sys_domain" is not allowed.');
        $this->subject->changeRecord(
            'sys_domain',
            1,
            ['title' => 'bar']
        );
    }

    /**
     * @test
     */
    public function changeRecordFailsWithUidZero(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $uid must not be zero.');
        $this->subject->changeRecord('tx_oelib_test', 0, ['title' => 'foo']);
    }

    /**
     * @test
     */
    public function changeRecordFailsWithEmptyData(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The array with the new record data must not be empty.');
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The parameter $recordData must not contain changes to the UID of a record.');
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
        $this->expectException(\InvalidArgumentException::class);
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
        $this->expectExceptionMessage('There is no record with UID ' . ($uid + 1) . ' on table "tx_oelib_test".');

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

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $count = $connection->count('*', 'tx_oelib_test', ['bool_data1' => (int)$value]);

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
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $count = $relationConnection->count(
            '*',
            'tx_oelib_test_article_mm',
            ['uid_local' => $uidLocal, 'uid_foreign' => $uidForeign]
        );
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function createRelationWithInvalidTable(): void
    {
        $table = 'tx_oelib_test_DOESNOTEXIST_mm';
        $uidLocal = 99999;
        $uidForeign = 199999;

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "' . $table . '" is not allowed.');
        $this->subject->createRelation($table, $uidLocal, $uidForeign);
    }

    /**
     * @test
     */
    public function createRelationWithEmptyTableName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "" is not allowed.');
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $firstRecordUid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['related_records']);
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $firstRecordUid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(2, (int)$row['related_records']);
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

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $count = $relationConnection->count('*', 'tx_oelib_test_article_mm', ['uid_local' => $firstRecordUid]);
        self::assertSame(1, $count);
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $firstRecordUid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['bidirectional']);
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('tx_oelib_test');
        $result = $connection->select(['*'], 'tx_oelib_test', ['uid' => $secondRecordUid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['related_records']);
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

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $count = $relationConnection->count(
            '*',
            'tx_oelib_test_article_mm',
            ['uid_local' => $secondRecordUid, 'uid_foreign' => $firstRecordUid]
        );
        self::assertSame(1, $count);
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
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test_article_mm', ['is_dummy_record' => 1]);

        // Runs a regular clean up. This should now delete only the first record
        // which was created through the testing framework and thus that table
        // is on the list of dirty tables. The second record was directly put
        // into the database and it's table is not on this list and will not be
        // removed by a regular clean up run.
        $this->subject->cleanUp();

        // Checks whether the first dummy record is deleted.
        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', []),
            'Some test records were not deleted from table "tx_oelib_test"'
        );

        // Checks whether the second dummy record still exists.
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(1, $relationConnection->count('*', 'tx_oelib_test_article_mm', []));

        // Runs a deep cleanup to delete all dummy records.
        $this->subject->cleanUp(true);
    }

    /**
     * @test
     */
    public function cleanUpWillCleanUpHiddenRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);
        $this->subject->markTableAsDirty('tx_oelib_test');

        $this->subject->cleanUp();

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE hidden = :hidden';
        $queryResult = $connection->executeQuery($query, ['hidden' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(0, $row['count']);
    }

    /**
     * @test
     */
    public function cleanUpWillCleanUpDeletedRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['deleted' => 1, 'is_dummy_record' => 1]);
        $this->subject->markTableAsDirty('tx_oelib_test');

        $this->subject->cleanUp();

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE deleted = :deleted';
        $queryResult = $connection->executeQuery($query, ['deleted' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(0, $row['count']);
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
        $previous = $GLOBALS['_SERVER']['HTTP_HOST'] ?? null;
        $this->subject->createFakeFrontEnd();

        $this->subject->cleanUp();

        self::assertSame($previous, $GLOBALS['_SERVER']['HTTP_HOST'] ?? null);
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
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['is_dummy_record' => 1]);

        // Runs a regular clean up. This should now delete only the first record
        // which was created through the testing framework and thus that table
        // is on the list of dirty tables. The second record was directly put
        // into the database and it's table is not on this list and will not be
        // removed by a regular clean up run.
        $this->subject->cleanUpWithoutDatabase();

        // Checks whether the first dummy record is deleted.
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', []),
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
        $previous = $GLOBALS['_SERVER']['HTTP_HOST'] ?? null;
        $this->subject->createFakeFrontEnd();

        $this->subject->cleanUpWithoutDatabase();

        self::assertSame($previous, $GLOBALS['_SERVER']['HTTP_HOST'] ?? null);
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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "sys_domains" is not allowed.');
        $this->subject->getAutoIncrement('sys_domains');
    }

    /**
     * @test
     */
    public function getAutoIncrementForSysCategoryRecordMmReturnsNull(): void
    {
        self::assertNull($this->subject->getAutoIncrement('sys_category_record_mm'));
    }

    /**
     * @test
     */
    public function getAutoIncrementWithEmptyTableNameFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "" is not allowed.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->getAutoIncrement('');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithForeignTableFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_seminars_seminars" is not allowed.');

        $this->subject->getAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithInexistentTableFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');
        $this->subject->getAutoIncrement('tx_oelib_DOESNOTEXIST');
    }

    /**
     * @test
     */
    public function getAutoIncrementWithTableWithoutUidReturnsNull(): void
    {
        self::assertNull($this->subject->getAutoIncrement('tx_oelib_test_article_mm'));
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
        $this->expectExceptionMessage('The table "" is not allowed.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->count('');
    }

    /**
     * @test
     */
    public function countWithInvalidTableNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "foo_bar" is not allowed.');

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
        $this->expectExceptionMessage('The table "sys_domain" is not allowed.');

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
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

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
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);

        self::assertSame(1, $this->subject->count('tx_oelib_test'));
    }

    /**
     * @test
     */
    public function countCanFindDeletedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['deleted' => 1, 'is_dummy_record' => 1]);

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
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bool_data1' => (int)$value, 'is_dummy_record' => 1]);

        $result = $this->subject->count('tx_oelib_test', ['bool_data1' => $value]);

        self::assertSame(1, $result);
    }

    // Tests regarding existsRecordWithUid()

    /**
     * @test
     */
    public function existsRecordWithUidWithZeroUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uid must be > 0.');

        $this->subject->existsRecordWithUid('tx_oelib_test', 0);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithNegativeUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$uid must be > 0.');

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
        $this->expectExceptionMessage('The table "foo_bar" is not allowed.');

        $table = 'foo_bar';
        $this->subject->existsRecordWithUid($table, 1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidForNoMatchReturnsFalse(): void
    {
        $uid = $this->subject->createRecord('tx_oelib_test');
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->delete('tx_oelib_test', ['uid' => $uid]);

        self::assertFalse($this->subject->existsRecordWithUid('tx_oelib_test', $uid));
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
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $testResult = $this->subject->existsRecordWithUid('tx_oelib_test', $uid);

        self::assertFalse($testResult);
    }

    // Tests regarding resetAutoIncrement()

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "sys_domains" is not allowed.');

        $this->subject->resetAutoIncrement('sys_domains');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithEmptyTableNameFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "" is not allowed.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->resetAutoIncrement('');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithForeignTableFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_seminars_seminars" is not allowed.');

        $this->subject->resetAutoIncrement('tx_seminars_seminars');
    }

    /**
     * @test
     */
    public function resetAutoIncrementWithInexistentTableFails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The table "tx_oelib_DOESNOTEXIST" is not allowed.');

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$threshold must be > 0.');

        $this->subject->setResetAutoIncrementThreshold(0);
    }

    /**
     * @test
     */
    public function setResetAutoIncrementThresholdForMinus1Fails(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$threshold must be > 0.');

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
        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(1, $connection->count('*', 'pages', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function createFrontEndPageByDefaultPopulatesSlugWithPageUid(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame($slug, $row['slug']);
    }

    /**
     * @test
     */
    public function createFrontEndPageSetsPageDocumentType(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(1, (int)$row['doktype']);
    }

    /**
     * @test
     */
    public function createFrontEndPageByDefaultCreatesPageOnRootPage(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(0, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function createFrontEndPageCanCreatePageOnOtherPage(): void
    {
        $parentUid = $this->subject->createFrontEndPage();
        $uid = $this->subject->createFrontEndPage($parentUid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame($parentUid, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function frontEndPageWillBeCleanedUp(): void
    {
        $uid = $this->subject->createFrontEndPage();
        self::assertNotSame(0, $uid);

        $this->subject->cleanUp();

        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(0, $connection->count('*', 'pages', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndPageHasNoTitleByDefault(): void
    {
        $uid = $this->subject->createFrontEndPage();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('', $row['title']);
    }

    // Tests regarding createSystemFolder()

    /**
     * @test
     */
    public function systemFolderCanBeCreated(): void
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(1, $connection->count('*', 'pages', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function createSystemFolderSetsCorrectDocumentType(): void
    {
        $uid = $this->subject->createSystemFolder();
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(254, (int)$row['doktype']);
    }

    /**
     * @test
     */
    public function systemFolderWillBeCreatedOnRootPage(): void
    {
        $uid = $this->subject->createSystemFolder();

        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame(0, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function systemFolderCanBeCreatedOnOtherPage(): void
    {
        $parent = $this->subject->createSystemFolder();
        $uid = $this->subject->createSystemFolder($parent);
        self::assertNotSame(0, $uid);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame($parent, (int)$row['pid']);
    }

    /**
     * @test
     */
    public function systemFolderWillBeCleanedUp(): void
    {
        $uid = $this->subject->createSystemFolder();
        self::assertNotSame(0, $uid);

        $this->subject->cleanUp();

        $connection = $this->getConnectionPool()->getConnectionForTable('pages');
        self::assertSame(0, $connection->count('*', 'pages', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function systemFolderHasNoTitleByDefault(): void
    {
        $uid = $this->subject->createSystemFolder();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('pages');
        $result = $connection->select(['*'], 'pages', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('', $row['title']);
    }

    // Tests regarding createTemplate()

    /**
     * @test
     */
    public function templateCanBeCreatedOnNonRootPage(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        self::assertNotSame(0, $uid);
        $connection = $this->getConnectionPool()->getConnectionForTable('sys_template');
        self::assertSame(1, $connection->count('*', 'sys_template', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function templateCannotBeCreatedOnRootPage(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$pageId must be > 0.');

        $this->subject->createTemplate(0);
    }

    /**
     * @test
     */
    public function templateCannotBeCreatedWithNegativePageNumber(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$pageId must be > 0.');

        $this->subject->createTemplate(-1);
    }

    /**
     * @test
     */
    public function templateWillBeCleanedUp(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);
        self::assertNotSame(0, $uid);

        $this->subject->cleanUp();

        $connection = $this->getConnectionPool()->getConnectionForTable('sys_template');
        self::assertSame(0, $connection->count('*', 'sys_template', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function templateInitiallyHasNoConfig(): void
    {
        $pageId = $this->subject->createFrontEndPage();
        $uid = $this->subject->createTemplate($pageId);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertFalse(isset($row['config']));
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertFalse(isset($row['constants']));
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('sys_template');
        $result = $connection->select(['*'], 'sys_template', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('plugin.tx_oelib.test = 1', $row['constants']);
    }

    // Tests regarding createFrontEndUserGroup()

    /**
     * @test
     */
    public function frontEndUserGroupCanBeCreated(): void
    {
        $uid = $this->subject->createFrontEndUserGroup();

        self::assertNotSame(0, $uid);
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(1, $connection->count('*', 'fe_groups', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserGroupTableWillBeCleanedUp(): void
    {
        $uid = $this->subject->createFrontEndUserGroup();
        self::assertNotSame(0, $uid);

        $this->subject->cleanUp();
        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(0, $connection->count('*', 'fe_groups', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserGroupHasNoTitleByDefault(): void
    {
        $uid = $this->subject->createFrontEndUserGroup();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_groups');
        $result = $connection->select(['*'], 'fe_groups', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('', $row['title']);
    }

    /**
     * @test
     */
    public function frontEndUserGroupCanHaveTitle(): void
    {
        $uid = $this->subject->createFrontEndUserGroup(['title' => 'Test title']);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_groups');
        $result = $connection->select(['*'], 'fe_groups', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('Test title', $row['title']);
    }

    // Tests regarding createFrontEndUser()

    /**
     * @test
     */
    public function frontEndUserCanBeCreated(): void
    {
        $uid = $this->subject->createFrontEndUser();
        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserTableWillBeCleanedUp(): void
    {
        $uid = $this->subject->createFrontEndUser();
        self::assertNotSame(0, $uid);

        $this->subject->cleanUp();

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(0, $connection->count('*', 'fe_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserHasNoUserNameByDefault(): void
    {
        $uid = $this->subject->createFrontEndUser();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $result = $connection->select(['*'], 'fe_users', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('', $row['username']);
    }

    /**
     * @test
     */
    public function frontEndUserCanHaveUserName(): void
    {
        $uid = $this->subject->createFrontEndUser('', ['username' => 'Test name']);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $result = $connection->select(['*'], 'fe_users', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('Test name', $row['username']);
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
        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "uid" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['uid' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoNonZeroUid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "uid" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['uid' => 99999]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupInTheDataArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "usergroup" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['usergroup' => 0]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoNonZeroUserGroupInTheDataArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "usergroup" must not be set in $recordData.');

        $this->subject->createFrontEndUser('', ['usergroup' => 99999]);
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoUserGroupListInTheDataArray(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The column "usergroup" must not be set in $recordData.');

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

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(1, $connection->count('*', 'fe_groups', []));
    }

    /**
     * @test
     */
    public function frontEndUserMustHaveNoZeroUserGroupEvenIfSeveralGroupsAreProvided(): void
    {
        $this->expectException(\InvalidArgumentException::class);
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
        $this->expectException(\InvalidArgumentException::class);
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
        $uid = $this->subject->createBackEndUser();
        self::assertNotSame(0, $uid);

        $connection = $this->getConnectionPool()->getConnectionForTable('be_users');
        self::assertSame(1, $connection->count('*', 'be_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function cleanUpCleansUpDirtyBackEndUserTable(): void
    {
        $uid = $this->subject->createBackEndUser();

        $this->subject->cleanUp();

        $connection = $this->getConnectionPool()->getConnectionForTable('be_users');
        self::assertSame(0, $connection->count('*', 'be_users', ['uid' => $uid]));
    }

    /**
     * @test
     */
    public function createBackEndUserCreatesRecordWithoutUserNameByDefault(): void
    {
        $uid = $this->subject->createBackEndUser();

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('be_users');
        $result = $connection->select(['*'], 'be_users', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('', $row['username']);
    }

    /**
     * @test
     */
    public function createBackEndUserForUserNameProvidedCreatesRecordWithUserName(): void
    {
        $uid = $this->subject->createBackEndUser(['username' => 'Test name']);

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('be_users');
        $result = $connection->select(['*'], 'be_users', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame('Test name', $row['username']);
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
        $previous = $GLOBALS['_SERVER']['HTTP_HOST'] ?? null;
        self::assertNotSame($expected, $previous);

        $this->subject->createFakeFrontEnd();

        self::assertSame($expected, $GLOBALS['_SERVER']['HTTP_HOST'] ?? null);
    }

    /**
     * @test
     */
    public function createFakeFrontEndReplacesExistingGlobalRequest(): void
    {
        $previousRequest = $this->prophesize(ServerRequestInterface::class)->reveal();
        $GLOBALS['TYPO3_REQUEST'] = $previousRequest;

        $this->subject->createFakeFrontEnd();

        self::assertNotSame($previousRequest, $GLOBALS['TYPO3_REQUEST'] ?? null);
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

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', []));
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithRecordDataCreatesFrontEndUserWithThatData(): void
    {
        $name = 'John Doe';
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);

        $this->subject->createAndLoginFrontEndUser('', ['name' => $name]);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', ['name' => $name]));
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

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        self::assertSame(1, $connection->count('*', 'fe_users', []));
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

        $connection = GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable('fe_users');
        $result = $connection->select(['*'], 'fe_users', ['uid' => $frontEndUserUid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $row */
            $row = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $row */
            $row = $result->fetch();
        }

        self::assertIsArray($row);
        self::assertSame($frontEndUserGroupUid, (int)$row['usergroup']);
    }

    /**
     * @test
     */
    public function createAndLoginFrontEndUserWithFrontEndUserGroupDoesNotCreateFrontEndUserGroup(): void
    {
        $pageUid = $this->subject->createFrontEndPage();
        $this->subject->createFakeFrontEnd($pageUid);
        $frontEndUserGroupUid = $this->subject->createFrontEndUserGroup();

        $this->subject->createAndLoginFrontEndUser($frontEndUserGroupUid);

        $connection = $this->getConnectionPool()->getConnectionForTable('fe_groups');
        self::assertSame(1, $connection->count('*', 'fe_groups', []));
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
}
