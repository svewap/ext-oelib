<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Exception;

use OliverKlee\Oelib\Exception\NotFoundException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Exception\NotFoundException
 */
final class NotFoundExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isException(): void
    {
        self::assertInstanceOf(\RuntimeException::class, new NotFoundException());
    }
}
