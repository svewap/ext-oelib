<?php

use TYPO3\CMS\Core\Database\DatabaseConnection;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Exception_DatabaseTest extends \Tx_Phpunit_TestCase
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
        $this->savedDebugOutput = $GLOBALS['TYPO3_DB']->debugOutput;
        $this->savedStoreLastBuildQuery = $GLOBALS['TYPO3_DB']->store_lastBuiltQuery;

        $GLOBALS['TYPO3_DB']->debugOutput = false;
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = true;
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_DB']->debugOutput = $this->savedDebugOutput;
        $GLOBALS['TYPO3_DB']->store_lastBuiltQuery = $this->savedStoreLastBuildQuery;
    }

    /**
     * @test
     */
    public function messageForInvalidQueryContainsErrorMessageFromDatabase()
    {
        /** @var DatabaseConnection $databaseAdapter */
        $databaseAdapter = $GLOBALS['TYPO3_DB'];
        $databaseAdapter->exec_SELECTquery('asdf', 'tx_oelib_test', '');
        $subject = new \Tx_Oelib_Exception_Database();

        self::assertContains(
            'asdf',
            $subject->getMessage()
        );
    }

    /**
     * @test
     */
    public function messageForInvalidQueryWithLastQueryEnabledContainsLastQuery()
    {
        /** @var DatabaseConnection $databaseAdapter */
        $databaseAdapter = $GLOBALS['TYPO3_DB'];
        $databaseAdapter->exec_SELECTquery('asdf', 'tx_oelib_test', '');
        $subject = new \Tx_Oelib_Exception_Database();

        self::assertContains(
            'SELECT',
            $subject->getMessage()
        );
    }
}
