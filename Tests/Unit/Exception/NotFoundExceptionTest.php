<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Exception;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;

/**
 * @covers \OliverKlee\Oelib\Exception\NotFoundException
 */
class NotFoundExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isException(): void
    {
        self::assertInstanceOf(\RuntimeException::class, new NotFoundException());
    }
}
