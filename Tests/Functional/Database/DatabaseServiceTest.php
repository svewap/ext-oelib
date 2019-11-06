<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Functional\Database;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DatabaseServiceTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var int
     */
    private $now = 1572370121;

    protected function setUp()
    {
        parent::setUp();

        $GLOBALS['SIM_EXEC_TIME'] = $this->now;
    }

    /*
     * Utility functions
     */

    /**
     * Explodes a comma-separated list of integer values and sorts them numerically.
     *
     * @param string $valueList
     *        comma-separated list of values, may be empty
     *
     * @return int[] the separate values, sorted numerically, may be empty
     */
    private function sortExplode($valueList): array
    {
        if ($valueList === '') {
            return [];
        }

        $numbers = GeneralUtility::intExplode(',', $valueList);
        sort($numbers, SORT_NUMERIC);

        return $numbers;
    }

    /*
     * Tests for the utility functions
     */

    /**
     * @test
     */
    public function sortExplodeWithEmptyStringReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->sortExplode('')
        );
    }

    /**
     * @test
     */
    public function sortExplodeWithOneNumberReturnsArrayWithNumber()
    {
        self::assertSame(
            [42],
            $this->sortExplode('42')
        );
    }

    /**
     * @test
     */
    public function sortExplodeWithTwoAscendingNumbersReturnsArrayWithBothNumbers()
    {
        self::assertSame(
            [1, 2],
            $this->sortExplode('1,2')
        );
    }

    /**
     * @test
     */
    public function sortExplodeWithTwoDescendingNumbersReturnsSortedArrayWithBothNumbers()
    {
        self::assertSame(
            [1, 2],
            $this->sortExplode('2,1')
        );
    }

    /*
     * Tests for enableFields
     */

    /**
     * @test
     */
    public function enableFieldsThrowsExceptionForTooSmallShowHidden()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::enableFields('tx_oelib_test', -2);
    }

    /**
     * @test
     */
    public function enableFieldsThrowsExceptionForTooBigShowHidden()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::enableFields('tx_oelib_test', 2);
    }

    /**
     * @test
     */
    public function enableFieldsIsDifferentForDifferentTables()
    {
        self::assertNotSame(
            \Tx_Oelib_Db::enableFields('tx_oelib_test'),
            \Tx_Oelib_Db::enableFields('pages')
        );
    }

    /**
     * @test
     */
    public function enableFieldsCanBeDifferentForShowHiddenZeroAndOne()
    {
        self::assertNotSame(
            \Tx_Oelib_Db::enableFields('tx_oelib_test', 0),
            \Tx_Oelib_Db::enableFields('tx_oelib_test', 1)
        );
    }

    /**
     * @test
     */
    public function enableFieldsAreTheSameForShowHiddenZeroAndMinusOne()
    {
        self::assertSame(
            \Tx_Oelib_Db::enableFields('tx_oelib_test', 0),
            \Tx_Oelib_Db::enableFields('tx_oelib_test', -1)
        );
    }

    /**
     * @test
     */
    public function enableFieldsCanBeDifferentForShowHiddenOneAndMinusOne()
    {
        self::assertNotSame(
            \Tx_Oelib_Db::enableFields('tx_oelib_test', 1),
            \Tx_Oelib_Db::enableFields('tx_oelib_test', -1)
        );
    }

    /**
     * @test
     */
    public function enableFieldsWithHiddenNotAllowedFindsDefaultRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        $result = $this->getDatabaseConnection()->select(
            '*',
            'tx_oelib_test',
            '1 = 1' . \Tx_Oelib_Db::enableFields('tx_oelib_test')
        );

        self::assertCount(1, $result);
    }

    /**
     * @test
     */
    public function enableFieldsWithHiddenAllowedFindsDefaultRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        $result = $this->getDatabaseConnection()->select(
            '*',
            'tx_oelib_test',
            '1 = 1' . \Tx_Oelib_Db::enableFields('tx_oelib_test', 1)
        );

        self::assertCount(1, $result);
    }

    /**
     * @return int[][]
     */
    public function hiddenRecordDataProvider(): array
    {
        return [
            'hidden' => [['hidden' => 1]],
            'end time in past' => [['endtime' => $this->now - 1000]],
        ];
    }

    /**
     * @test
     *
     * @param array $recordData
     *
     * @dataProvider hiddenRecordDataProvider
     */
    public function enableFieldsWithHiddenNotAllowedIgnoresHiddenRecord(array $recordData)
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', $recordData);

        $result = $this->getDatabaseConnection()->select(
            '*',
            'tx_oelib_test',
            '1 = 1' . \Tx_Oelib_Db::enableFields('tx_oelib_test')
        );

        self::assertCount(0, $result);
    }

    /**
     * @test
     *
     * @param array $recordData
     *
     * @dataProvider hiddenRecordDataProvider
     */
    public function enableFieldsWithHiddenAllowedFindsHiddenRecord(array $recordData)
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', $recordData);

        $result = $this->getDatabaseConnection()->select(
            '*',
            'tx_oelib_test',
            '1 = 1' . \Tx_Oelib_Db::enableFields('tx_oelib_test', 1)
        );

        self::assertCount(1, $result);
    }

    /*
     * Tests concerning createRecursivePageList
     */

    /**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithDefaultRecursion()
    {
        self::assertSame(
            '',
            \Tx_Oelib_Db::createRecursivePageList('')
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithZeroRecursion()
    {
        self::assertSame(
            '',
            \Tx_Oelib_Db::createRecursivePageList('', 0)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListReturnsAnEmptyStringForNoPagesWithNonZeroRecursion()
    {
        self::assertSame(
            '',
            \Tx_Oelib_Db::createRecursivePageList('', 1)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListThrowsWithNegativeRecursion()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::createRecursivePageList('', -1);
    }

    /**
     * @test
     */
    public function createRecursivePageListForStringPageForRecursionWithoutSubPagesReturnsOnlyTheGivenPage()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList((string)$uid, 1)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListForIntPageForRecursionWithoutSubPagesReturnsOnlyTheGivenPage()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList($uid, 1)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListForStringPageWithoutRecursionWithoutSubPagesReturnsOnlyTheGivenPage()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList((string)$uid)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListForIntPageWithoutRecursionWithoutSubPagesReturnsOnlyTheGivenPage()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList($uid)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubPagesForOnePageWithZeroRecursion()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid]);

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList((string)$uid, 0)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubPagesForTwoPagesWithZeroRecursion()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid1]);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $this->sortExplode($uid1 . ',' . $uid2),
            $this->sortExplode(
                \Tx_Oelib_Db::createRecursivePageList($uid1 . ',' . $uid2, 0)
            )
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubSubPagesForRecursionOfOne()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid]);
        $subFolderUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $subFolderUid]);

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid),
            $this->sortExplode(\Tx_Oelib_Db::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainUnrelatedPages()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', []);

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList($uid, 0)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListCanContainTwoSubPagesOfOnePage()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid]);
        $subFolderUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid]);
        $subFolderUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid1 . ',' . $subFolderUid2),
            $this->sortExplode(\Tx_Oelib_Db::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListCanContainSubPagesOfTwoPages()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid1]);
        $subFolderUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid2]);
        $subFolderUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $this->sortExplode(
                $uid1 . ',' . $uid2 . ',' . $subFolderUid1 . ',' . $subFolderUid2
            ),
            $this->sortExplode(
                \Tx_Oelib_Db::createRecursivePageList($uid1 . ',' . $uid2, 1)
            )
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListHeedsIncreasingRecursionDepthOnSubsequentCalls()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid]);
        $subFolderUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList($uid, 0)
        );
        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid),
            $this->sortExplode(\Tx_Oelib_Db::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListHeedsDecreasingRecursionDepthOnSubsequentCalls()
    {
        $this->getDatabaseConnection()->insertArray('pages', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('pages', ['pid' => $uid]);
        $subFolderUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid),
            $this->sortExplode(\Tx_Oelib_Db::createRecursivePageList($uid, 1))
        );
        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList($uid, 0)
        );
    }

    /*
     * Tests concerning getColumnsInTable
     */

    /**
     * @test
     */
    public function getColumnsInTableForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::getColumnsInTable('');
    }

    /**
     * @test
     */
    public function getColumnsInTableForInexistentTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::getColumnsInTable('tx_oelib_doesnotexist');
    }

    /**
     * @test
     */
    public function getColumnsInTableReturnsArrayThatContainsExistingColumn()
    {
        $columns = \Tx_Oelib_Db::getColumnsInTable('tx_oelib_test');

        self::assertTrue(
            isset($columns['title'])
        );
    }

    /**
     * @test
     */
    public function getColumnsInTableReturnsArrayThatNotContainsInexistentColumn()
    {
        $columns = \Tx_Oelib_Db::getColumnsInTable('tx_oelib_test');

        self::assertFalse(
            isset($columns['does_not_exist'])
        );
    }

    /*
     * Tests regarding tableHasColumn()
     */

    /**
     * @test
     */
    public function tableHasColumnReturnsTrueOnTableWithColumn()
    {
        self::assertTrue(
            \Tx_Oelib_Db::tableHasColumn(
                'tx_oelib_test',
                'title'
            )
        );
    }

    /**
     * @test
     */
    public function tableHasColumnReturnsFalseOnTableWithoutColumn()
    {
        self::assertFalse(
            \Tx_Oelib_Db::tableHasColumn(
                'tx_oelib_test',
                'inexistent_column'
            )
        );
    }

    /**
     * @test
     */
    public function tableHasColumnThrowsExceptionOnEmptyTableName()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::tableHasColumn(
            '',
            'title'
        );
    }

    /**
     * @test
     */
    public function tableHasColumnReturnsFalseOnEmptyColumnName()
    {
        self::assertFalse(
            \Tx_Oelib_Db::tableHasColumn(
                'tx_oelib_test',
                ''
            )
        );
    }

    /*
     * Tests for delete
     */

    /**
     * @test
     */
    public function deleteForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::delete(
            '',
            'uid = 0'
        );
    }

    /**
     * @test
     */
    public function deleteDeletesRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'uid = ' . $uid
        );

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function deleteForNoDeletedRecordReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Oelib_Db::delete(
                'tx_oelib_test',
                'uid = 0'
            )
        );
    }

    /**
     * @test
     */
    public function deleteForOneDeletedRecordReturnsOne()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            1,
            \Tx_Oelib_Db::delete(
                'tx_oelib_test',
                'uid = ' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function deleteForTwoDeletedRecordsReturnsTwo()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            2,
            \Tx_Oelib_Db::delete(
                'tx_oelib_test',
                'uid IN(' . $uid1 . ',' . $uid2 . ')'
            )
        );
    }

    /*
     * Tests for update
     */

    /**
     * @test
     */
    public function updateForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::update(
            '',
            'uid = 0',
            []
        );
    }

    /**
     * @test
     */
    public function updateChangesRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        \Tx_Oelib_Db::update(
            'tx_oelib_test',
            'uid = ' . $uid,
            ['title' => 'foo']
        );

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     *
     * @param bool $value
     *
     * @dataProvider booleanDataProvider
     */
    public function updateCanUpdateRecordWithBooleanData($value)
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        \Tx_Oelib_Db::update('tx_oelib_test', 'uid = ' . $uid, ['bool_data1' => $value]);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'bool_data1 = ' . (int)$value)
        );
    }

    /**
     * @test
     */
    public function updateForNoChangedRecordReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Oelib_Db::update(
                'tx_oelib_test',
                'uid = 0',
                ['title' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function updateForOneChangedRecordReturnsOne()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            1,
            \Tx_Oelib_Db::update(
                'tx_oelib_test',
                'uid = ' . $uid,
                ['title' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function updateForTwoChangedRecordsReturnsTwo()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            2,
            \Tx_Oelib_Db::update(
                'tx_oelib_test',
                'uid IN(' . $uid1 . ',' . $uid2 . ')',
                ['title' => 'foo']
            )
        );
    }

    /*
     * Tests for insert
     */

    /**
     * @test
     */
    public function insertForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::insert(
            '',
            ['is_dummy_record' => 1]
        );
    }

    /**
     * @test
     */
    public function insertForEmptyRecordDataThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            []
        );
    }

    /**
     * @test
     */
    public function insertInsertsRecord()
    {
        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['title' => 'foo', 'is_dummy_record' => 1]
        );

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo"')
        );
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
    public function insertCanInsertRecordWithBooleanData($value)
    {
        \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['bool_data1' => $value, 'is_dummy_record' => 1]
        );

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'bool_data1 = ' . (int)$value)
        );
    }

    /**
     * @test
     */
    public function insertForTableWithUidReturnsUidOfCreatedRecord()
    {
        $uid = \Tx_Oelib_Db::insert(
            'tx_oelib_test',
            ['is_dummy_record' => 1]
        );

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function insertMakesUidAccessibleAsLastInsertUidOnConnection()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000) {
            self::markTestSkipped('These tests cannot be run in TYPO3 version 9.');
        }

        \Tx_Oelib_Db::insert('tx_oelib_test', ['is_dummy_record' => 1]);
        $uid = \Tx_Oelib_Db::getDatabaseConnection()->sql_insert_id();

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function insertForTableWithoutUidReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Oelib_Db::insert(
                'tx_oelib_test_article_mm',
                ['is_dummy_record' => 1]
            )
        );
    }

    /*
     * Tests concerning select, selectSingle, selectMultiple
     */

    /**
     * @test
     */
    public function selectForEmptyTableNameThrowsException()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000) {
            self::markTestSkipped('These tests cannot be run in TYPO3 version 9.');
        }

        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::select('*', '');
    }

    /**
     * @test
     */
    public function selectForEmptyFieldListThrowsException()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000) {
            self::markTestSkipped('These tests cannot be run in TYPO3 version 9.');
        }

        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::select('', 'tx_oelib_test');
    }

    /**
     * @test
     */
    public function selectSingleForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::selectSingle('*', '');
    }

    /**
     * @test
     */
    public function selectSingleForEmptyFieldListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::selectSingle('', 'tx_oelib_test');
    }

    /**
     * @test
     */
    public function selectSingleCanFindOneRow()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            ['uid' => $uid],
            \Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function selectSingleForNoResultsThrowsEmptyQueryResultException()
    {
        $this->expectException(\Tx_Oelib_Exception_EmptyQueryResult::class);

        \Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', 'title = "nothing"');
    }

    /**
     * @test
     */
    public function selectSingleCanOrderTheResults()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'Title A']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'Title B']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            ['uid' => $uid],
            \Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', '', '', 'title DESC')
        );
    }

    /**
     * @test
     */
    public function selectMultipleForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::selectMultiple('*', '');
    }

    /**
     * @test
     */
    public function selectMultipleForEmptyFieldListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::selectMultiple('', 'tx_oelib_test');
    }

    /**
     * @test
     */
    public function selectMultipleForNoResultsReturnsEmptyArray()
    {
        self::assertSame(
            [],
            \Tx_Oelib_Db::selectMultiple(
                'uid',
                'tx_oelib_test',
                'title = "nothing"'
            )
        );
    }

    /**
     * @test
     */
    public function selectMultipleCanFindOneRow()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            [['uid' => $uid]],
            \Tx_Oelib_Db::selectMultiple('uid', 'tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function selectMultipleCanFindTwoRows()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);

        self::assertSame(
            [
                ['title' => 'foo'],
                ['title' => 'foo'],
            ],
            \Tx_Oelib_Db::selectMultiple(
                'title',
                'tx_oelib_test',
                'title = "foo"'
            )
        );
    }

    /**
     * @test
     */
    public function selectColumnForMultipleForNoMatchesReturnsEmptyArray()
    {
        self::assertSame(
            [],
            \Tx_Oelib_Db::selectColumnForMultiple(
                'title',
                'tx_oelib_test',
                'title = "nothing"'
            )
        );
    }

    /**
     * @test
     */
    public function selectColumnForMultipleForOneMatchReturnsArrayWithColumnContent()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            ['foo'],
            \Tx_Oelib_Db::selectColumnForMultiple(
                'title',
                'tx_oelib_test',
                'uid = ' . $uid
            )
        );
    }

    /**
     * @test
     */
    public function selectColumnForMultipleForTwoMatchReturnsArrayWithColumnContents()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        $result = \Tx_Oelib_Db::selectColumnForMultiple(
            'title',
            'tx_oelib_test',
            'uid = ' . $uid1 . ' OR uid = ' . $uid2
        );
        sort($result);
        self::assertSame(
            ['bar', 'foo'],
            $result
        );
    }

    /*
     * Tests concerning existsTable
     */

    /**
     * @test
     */
    public function existsTableWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::existsTable('');
    }

    /**
     * @test
     */
    public function existsTableForExistingTableReturnsTrue()
    {
        self::assertTrue(
            \Tx_Oelib_Db::existsTable('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function existsTableForInexistentTableReturnsFalse()
    {
        self::assertFalse(
            \Tx_Oelib_Db::existsTable('tx_oelib_doesnotexist')
        );
    }

    /*
     * Tests concerning count
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countCanBeCalledWithEmptyOrMissingWhereClause()
    {
        \Tx_Oelib_Db::count('tx_oelib_test', '');
        \Tx_Oelib_Db::count('tx_oelib_test');
    }

    /**
     * @test
     */
    public function countForNoMatchesReturnsZero()
    {
        self::assertSame(
            0,
            \Tx_Oelib_Db::count(
                'tx_oelib_test',
                'uid = 42'
            )
        );
    }

    /**
     * @test
     */
    public function countForOneMatchReturnsOne()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            1,
            \Tx_Oelib_Db::count('tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function countForTwoMatchesReturnsTwo()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            2,
            \Tx_Oelib_Db::count(
                'tx_oelib_test',
                'uid IN(' . $uid1 . ',' . $uid2 . ')'
            )
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function countCanBeCalledForTableWithoutUidOrMultipleTablesOrJoins()
    {
        \Tx_Oelib_Db::count('tx_oelib_test_article_mm');
        \Tx_Oelib_Db::count('tx_oelib_test, tx_oelib_testchild');
        \Tx_Oelib_Db::count('tx_oelib_test JOIN tx_oelib_testchild');
    }

    /*
     * Tests regarding existsRecord
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function existsRecordWithEmptyOrMissingWhereClauseIsAllowed()
    {
        \Tx_Oelib_Db::existsRecord('tx_oelib_test', '');
        \Tx_Oelib_Db::existsRecord('tx_oelib_test');
    }

    /**
     * @test
     */
    public function existsRecordWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::existsRecord('');
    }

    /**
     * @test
     */
    public function existsRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            \Tx_Oelib_Db::existsRecord('tx_oelib_test', 'uid = 42')
        );
    }

    /**
     * @test
     */
    public function existsRecordForOneMatchReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertTrue(
            \Tx_Oelib_Db::existsRecord('tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordForTwoMatchesReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);

        self::assertTrue(
            \Tx_Oelib_Db::existsRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function getDatabaseConnectionReturnsGlobalsDatabaseConnection()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 9000000) {
            self::markTestSkipped('These tests cannot be run in TYPO3 version 9.');
        }

        self::assertSame($GLOBALS['TYPO3_DB'], \Tx_Oelib_Db::getDatabaseConnection());
    }
}
