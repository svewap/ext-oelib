<?php

use OliverKlee\PhpUnit\TestCase;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_DbTest extends TestCase
{
    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework;

    protected function setUp()
    {
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) > 9000000) {
            self::markTestSkipped('These tests cannot be run in TYPO3 version 9.');
        }

        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
    }

    protected function tearDown()
    {
        if ($this->testingFramework !== null) {
            $this->testingFramework->cleanUp();
        }
    }

    /*
     * Utility functions
     */

    /**
     * Explodes a comma-separated list of integer values and sorts them
     * numerically.
     *
     * @param string $valueList
     *        comma-separated list of values, may be empty
     *
     * @return int[] the separate values, sorted numerically, may be empty
     */
    private function sortExplode($valueList)
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
    public function enableFieldsCanBeDifferentForDifferentIgnores()
    {
        self::assertNotSame(
            \Tx_Oelib_Db::enableFields('tx_oelib_test', 0, []),
            \Tx_Oelib_Db::enableFields(
                'tx_oelib_test',
                0,
                ['endtime' => true]
            )
        );
    }

    /**
     * @test
     */
    public function enableFieldsWithHiddenNotAllowedFindsDefaultRecord()
    {
        $this->testingFramework->createRecord('tx_oelib_test');

        $result = \Tx_Oelib_Db::selectMultiple(
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
        $this->testingFramework->createRecord('tx_oelib_test');

        $result = \Tx_Oelib_Db::selectMultiple(
            '*',
            'tx_oelib_test',
            '1 = 1' . \Tx_Oelib_Db::enableFields('tx_oelib_test', 1)
        );

        self::assertCount(1, $result);
    }

    /**
     * @return int[][]
     */
    public function hiddenRecordDataProvider()
    {
        return [
            'hidden' => [['hidden' => 1]],
            'start time in future' => [['starttime' => $GLOBALS['SIM_EXEC_TIME'] + 1000]],
            'end time in past' => [['endtime' => $GLOBALS['SIM_EXEC_TIME'] - 1000]],
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
        $this->testingFramework->createRecord('tx_oelib_test', $recordData);

        $result = \Tx_Oelib_Db::selectMultiple(
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
        $this->testingFramework->createRecord('tx_oelib_test', $recordData);

        $result = \Tx_Oelib_Db::selectMultiple(
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
    public function createRecursivePageListDoesNotContainSubpagesForOnePageWithZeroRecursion()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createSystemFolder($uid);

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList((string)$uid, 0)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListDoesNotContainSubpagesForTwoPagesWithZeroRecursion()
    {
        $uid1 = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createSystemFolder($uid1);
        $uid2 = $this->testingFramework->createSystemFolder();

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
    public function createRecursivePageListDoesNotContainSubsubpagesForRecursionOfOne()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid = $this->testingFramework->createSystemFolder($uid);
        $this->testingFramework->createSystemFolder($subFolderUid);

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
        $uid = $this->testingFramework->createSystemFolder();
        $this->testingFramework->createSystemFolder();

        self::assertSame(
            (string)$uid,
            \Tx_Oelib_Db::createRecursivePageList($uid, 0)
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListCanContainTwoSubpagesOfOnePage()
    {
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid1 = $this->testingFramework->createSystemFolder($uid);
        $subFolderUid2 = $this->testingFramework->createSystemFolder($uid);

        self::assertSame(
            $this->sortExplode($uid . ',' . $subFolderUid1 . ',' . $subFolderUid2),
            $this->sortExplode(\Tx_Oelib_Db::createRecursivePageList($uid, 1))
        );
    }

    /**
     * @test
     */
    public function createRecursivePageListCanContainSubpagesOfTwoPages()
    {
        $uid1 = $this->testingFramework->createSystemFolder();
        $uid2 = $this->testingFramework->createSystemFolder();
        $subFolderUid1 = $this->testingFramework->createSystemFolder($uid1);
        $subFolderUid2 = $this->testingFramework->createSystemFolder($uid2);

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
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid = $this->testingFramework->createSystemFolder($uid);

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
        $uid = $this->testingFramework->createSystemFolder();
        $subFolderUid = $this->testingFramework->createSystemFolder($uid);

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
     * Tests concerning getColumnDefinition
     */

    /**
     * @test
     */
    public function getColumnDefinitionForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::getColumnDefinition('', 'uid');
    }

    /**
     * @test
     */
    public function getColumnDefinitionReturnsArrayThatContainsFieldName()
    {
        $definition = \Tx_Oelib_Db::getColumnDefinition('tx_oelib_test', 'title');

        self::assertSame(
            'title',
            $definition['Field']
        );
    }

    /*
     * Tests regarding tableHasColumnUid()
     */

    /**
     * @test
     */
    public function tableHasColumnUidForEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::tableHasColumnUid('');
    }

    /**
     * @test
     */
    public function tableHasColumnUidIsTrueOnTableWithColumnUid()
    {
        self::assertTrue(
            \Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test')
        );
    }

    /**
     * @test
     */
    public function tableHasColumnUidIsFalseOnTableWithoutColumnUid()
    {
        self::assertFalse(
            \Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test_article_mm')
        );
    }

    /**
     * @test
     */
    public function tableHasColumnUidCanReturnDifferentResultsForDifferentTables()
    {
        self::assertNotSame(
            \Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test'),
            \Tx_Oelib_Db::tableHasColumnUid('tx_oelib_test_article_mm')
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
        $uid = $this->testingFramework->createRecord('tx_oelib_test');

        \Tx_Oelib_Db::delete(
            'tx_oelib_test',
            'uid = ' . $uid
        );

        self::assertFalse(
            $this->testingFramework->existsRecordWithUid(
                'tx_oelib_test',
                $uid
            )
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
        $uid = $this->testingFramework->createRecord('tx_oelib_test');

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
        $uid1 = $this->testingFramework->createRecord('tx_oelib_test');
        $uid2 = $this->testingFramework->createRecord('tx_oelib_test');

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
        $uid = $this->testingFramework->createRecord('tx_oelib_test');

        \Tx_Oelib_Db::update(
            'tx_oelib_test',
            'uid = ' . $uid,
            ['title' => 'foo']
        );

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_oelib_test',
                'title = "foo"'
            )
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
        $uid = $this->testingFramework->createRecord('tx_oelib_test');

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
        $uid1 = $this->testingFramework->createRecord('tx_oelib_test');
        $uid2 = $this->testingFramework->createRecord('tx_oelib_test');

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
        $this->testingFramework->markTableAsDirty('tx_oelib_test');

        self::assertTrue(
            $this->testingFramework->existsRecord(
                'tx_oelib_test',
                'title = "foo"'
            )
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
        $this->testingFramework->markTableAsDirty('tx_oelib_test');

        self::assertTrue(
            $this->testingFramework->existsRecordWithUid(
                'tx_oelib_test',
                $uid
            )
        );
    }

    /**
     * @test
     */
    public function insertForTableWithoutUidReturnsZero()
    {
        $this->testingFramework->markTableAsDirty('tx_oelib_test_article_mm');

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
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::select('*', '');
    }

    /**
     * @test
     */
    public function selectForEmptyFieldListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::select('', 'tx_oelib_test');
    }

    /**
     * @test
     */
    public function selectReturnsMySqliResult()
    {
        self::assertInstanceOf(
            \mysqli_result::class,
            \Tx_Oelib_Db::select('title', 'tx_phpunit_test')
        );
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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test'
        );

        self::assertSame(
            ['uid' => (string)$uid],
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
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'Title A']
        );
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'Title B']
        );

        self::assertSame(
            ['uid' => (string)$uid],
            \Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', '', '', 'title DESC')
        );
    }

    /**
     * @test
     */
    public function selectSingleCanUseOffset()
    {
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'Title A']
        );
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'Title B']
        );

        self::assertSame(
            ['uid' => (string)$uid],
            \Tx_Oelib_Db::selectSingle('uid', 'tx_oelib_test', '', '', 'title', 1)
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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test'
        );

        self::assertSame(
            [['uid' => (string)$uid]],
            \Tx_Oelib_Db::selectMultiple('uid', 'tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function selectMultipleCanFindTwoRows()
    {
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

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
        $uid1 = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $uid2 = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'bar']
        );

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
     * Tests concerning getAllTableNames
     */

    /**
     * @test
     */
    public function getAllTableNamesContainsExistingTable()
    {
        self::assertContains(
            'tx_oelib_test',
            \Tx_Oelib_Db::getAllTableNames()
        );
    }

    /**
     * @test
     */
    public function getAllTableNamesNotContainsInexistentTable()
    {
        self::assertNotContains(
            'tx_oelib_doesnotexist',
            \Tx_Oelib_Db::getAllTableNames()
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
     * Tests concerning getTcaForTable
     */

    /**
     * @test
     */
    public function getTcaForTableReturnsValidTcaArray()
    {
        $tca = \Tx_Oelib_Db::getTcaForTable('tx_oelib_test');

        self::assertInternalType('array', $tca['ctrl']);
        self::assertInternalType('array', $tca['interface']);
        self::assertInternalType('array', $tca['columns']);
        self::assertInternalType('array', $tca['types']);
        self::assertInternalType('array', $tca['palettes']);
    }

    /**
     * @test
     */
    public function getTcaForTableWithEmptyTableNameThrowsExceptionTca()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::getTcaForTable('');
    }

    /**
     * @test
     */
    public function getTcaForTableThrowsExceptionOnTableWithoutTca()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::getTcaForTable('tx_oelib_test_article_mm');
    }

    /**
     * @test
     */
    public function getTcaForTableCanLoadFieldsAddedByExtensions()
    {
        $tca = \Tx_Oelib_Db::getTcaForTable('fe_users');

        self::assertTrue(
            isset($tca['columns']['tx_oelib_is_dummy_record'])
        );
    }

    /*
     * Tests concerning count
     */

    /**
     * @test
     */
    public function countCanBeCalledWithEmptyWhereClause()
    {
        \Tx_Oelib_Db::count('tx_oelib_test', '');
    }

    /**
     * @test
     */
    public function countCanBeCalledWithMissingWhereClause()
    {
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
        self::assertSame(
            1,
            \Tx_Oelib_Db::count(
                'tx_oelib_test',
                'uid = ' . $this->testingFramework->createRecord('tx_oelib_test')
            )
        );
    }

    /**
     * @test
     */
    public function countForTwoMatchesReturnsTwo()
    {
        $uid1 = $this->testingFramework->createRecord('tx_oelib_test');
        $uid2 = $this->testingFramework->createRecord('tx_oelib_test');

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
     */
    public function countCanBeCalledForTableWithoutUid()
    {
        \Tx_Oelib_Db::count('tx_oelib_test_article_mm');
    }

    /**
     * @test
     */
    public function countCanBeCalledWithMultipleTables()
    {
        \Tx_Oelib_Db::count('tx_oelib_test, tx_oelib_testchild');
    }

    /**
     * @test
     */
    public function countWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::count('tx_oelib_doesnotexist', 'uid = 42');
    }

    /**
     * @test
     */
    public function countCanBeCalledWithJoinedTables()
    {
        \Tx_Oelib_Db::count('tx_oelib_test JOIN tx_oelib_testchild');
    }

    /**
     * @test
     */
    public function countDoesNotAllowJoinWithoutTables()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::count('JOIN');
    }

    /**
     * @test
     */
    public function countDoesNotAllowJoinWithOnlyOneTableOnTheLeft()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::count('tx_oelib_test JOIN ');
    }

    /**
     * @test
     */
    public function countDoesNotAllowJoinWithOnlyOneTableOnTheRight()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::count('JOIN tx_oelib_test');
    }

    /*
     * Tests regarding existsRecord
     */

    /**
     * @test
     */
    public function existsRecordWithEmptyWhereClauseIsAllowed()
    {
        \Tx_Oelib_Db::existsRecord('tx_oelib_test', '');
    }

    /**
     * @test
     */
    public function existsRecordWithMissingWhereClauseIsAllowed()
    {
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
    public function existsRecordWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::existsRecord('tx_oelib_doesnotexist');
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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test'
        );

        self::assertTrue(
            \Tx_Oelib_Db::existsRecord('tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordForTwoMatchesReturnsTrue()
    {
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertTrue(
            \Tx_Oelib_Db::existsRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /*
     * Tests regarding existsExactlyOneRecord
     */

    /**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyWhereClauseIsAllowed()
    {
        \Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', '');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithMissingWhereClauseIsAllowed()
    {
        \Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::existsExactlyOneRecord('');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_doesnotexist');
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForNoMatchesReturnsFalse()
    {
        self::assertFalse(
            \Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', 'uid = 42')
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForOneMatchReturnsTrue()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test'
        );

        self::assertTrue(
            \Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', 'uid = ' . $uid)
        );
    }

    /**
     * @test
     */
    public function existsExactlyOneRecordForTwoMatchesReturnsFalse()
    {
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        self::assertFalse(
            \Tx_Oelib_Db::existsExactlyOneRecord('tx_oelib_test', 'title = "foo"')
        );
    }

    /*
     * Tests regarding existsRecordWithUid
     */

    /**
     * @test
     */
    public function existsRecordWithUidWithZeroUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', 0);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithNegativeUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', -1);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        \Tx_Oelib_Db::existsRecordWithUid('', 42);
    }

    /**
     * @test
     */
    public function existsRecordWithUidWithInvalidTableNameThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        \Tx_Oelib_Db::existsRecordWithUid('tx_oelib_doesnotexist', 42);
    }

    /**
     * @test
     */
    public function existsRecordWithUidForNoMatchReturnsFalse()
    {
        self::assertFalse(
            \Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', 42)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidForMatchReturnsTrue()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test'
        );

        self::assertTrue(
            \Tx_Oelib_Db::existsRecordWithUid('tx_oelib_test', $uid)
        );
    }

    /**
     * @test
     */
    public function existsRecordWithUidUsesAdditionalNonEmptyWhereClause()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['deleted' => 1]
        );

        self::assertFalse(
            \Tx_Oelib_Db::existsRecordWithUid(
                'tx_oelib_test',
                $uid,
                ' AND deleted = 0'
            )
        );
    }

    /**
     * @test
     */
    public function getDatabaseConnectionReturnsGlobalsDatabaseConnection()
    {
        self::assertSame(
            $GLOBALS['TYPO3_DB'],
            \Tx_Oelib_Db::getDatabaseConnection()
        );
    }
}
