<?php

namespace OliverKlee\Oelib\Tests\Unit\Exception;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DatabaseTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isException()
    {
        self::assertInstanceOf(\Exception::class, new \Tx_Oelib_Exception_Database());
    }
}
