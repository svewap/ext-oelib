<?php

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Exception_EmptyQueryResultTest extends TestCase
{
    /**
     * @var bool the saved content of $GLOBALS['TYPO3_DB']->debugOutput
     */
    private $savedDebugOutput;

    /**
     * @var bool the saved content of
     *              $GLOBALS['TYPO3_DB']->store_lastBuiltQuery
     */
    private $savedStoreLastBuildQuery;

    protected function setUp()
    {
        $databaseConnection = \Tx_Oelib_Db::getDatabaseConnection();
        $this->savedDebugOutput = $databaseConnection->debugOutput;
        $this->savedStoreLastBuildQuery = $databaseConnection->store_lastBuiltQuery;

        $databaseConnection->debugOutput = false;
        $databaseConnection->store_lastBuiltQuery = true;
    }

    protected function tearDown()
    {
        $databaseConnection = \Tx_Oelib_Db::getDatabaseConnection();
        $databaseConnection->debugOutput = $this->savedDebugOutput;
        $databaseConnection->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
    }

    /**
     * @test
     */
    public function messageAfterQueryWithLastQueryEnabledContainsLastQuery()
    {
        \Tx_Oelib_Db::getDatabaseConnection()->exec_SELECTquery('title', 'tx_oelib_test', '');
        $subject = new \Tx_Oelib_Exception_EmptyQueryResult();

        self::assertContains(
            'SELECT',
            $subject->getMessage()
        );
    }
}
