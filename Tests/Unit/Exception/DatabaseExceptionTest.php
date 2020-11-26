<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Exception;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Exception\DatabaseException;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DatabaseExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isException()
    {
        self::assertInstanceOf(\RuntimeException::class, new DatabaseException());
    }
}
