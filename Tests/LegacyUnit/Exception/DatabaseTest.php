<?php

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Exception_DatabaseTest extends TestCase
{
    /**
     * @test
     */
    public function isException()
    {
        self::assertInstanceOf(\Exception::class, new \Tx_Oelib_Exception_Database());
    }
}
