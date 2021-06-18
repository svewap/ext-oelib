<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Exception;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Exception\EmptyQueryResultException;

/**
 * Test case.
 */
class EmptyQueryResultExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isException()
    {
        self::assertInstanceOf(\RuntimeException::class, new EmptyQueryResultException());
    }
}
