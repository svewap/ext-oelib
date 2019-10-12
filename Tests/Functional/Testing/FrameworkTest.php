<?php

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use Doctrine\DBAL\Driver\Mysqli\MysqliStatement;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new \Tx_Oelib_TestingFramework('tx_oelib');
    }

    /**
     * @test
     */
    public function createRecordCanCreateHiddenRecord()
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['hidden' => 1]);

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8007000) {
            /** @var MysqliStatement $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'uid = ' . $uid);
            $count = $result->rowCount();
        } else {
            /** @var \mysqli_result $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'uid = ' . $uid);
            $count = $result->num_rows;
        }
        self::assertSame(1, $count);
    }

    /**
     * @test
     */
    public function createRecordCanCreateDeletedRecord()
    {
        $uid = $this->subject->createRecord('tx_oelib_test', ['deleted' => 1]);

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8007000) {
            /** @var MysqliStatement $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'uid = ' . $uid);
            $count = $result->rowCount();
        } else {
            /** @var \mysqli_result $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'uid = ' . $uid);
            $count = $result->num_rows;
        }
        self::assertSame(1, $count);
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
     */
    public function cleanUpWillCleanUpHiddenRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1, 'is_dummy_record' => 1]);
        $this->subject->markTableAsDirty('tx_oelib_test');

        $this->subject->cleanUp();

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8007000) {
            /** @var MysqliStatement $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'hidden = 1');
            $count = $result->rowCount();
        } else {
            /** @var \mysqli_result $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'hidden = 1');
            $count = $result->num_rows;
        }
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

        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8007000) {
            /** @var MysqliStatement $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'deleted = 1');
            $count = $result->rowCount();
        } else {
            /** @var \mysqli_result $result */
            $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'deleted = 1');
            $count = $result->num_rows;
        }
        self::assertSame(0, $count);
    }
}
