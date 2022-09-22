<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Exception;

use OliverKlee\Oelib\Exception\EmptyQueryResultException;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Exception\EmptyQueryResultException
 */
final class EmptyQueryResultExceptionTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isException(): void
    {
        self::assertInstanceOf(\RuntimeException::class, new EmptyQueryResultException());
    }
}
