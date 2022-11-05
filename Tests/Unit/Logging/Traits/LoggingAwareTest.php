<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Logging\Traits;

use OliverKlee\Oelib\Logging\Interfaces\LoggingAware;
use OliverKlee\Oelib\Tests\Unit\Logging\Fixtures\TestingLoggingAware;
use TYPO3\CMS\Core\Log\LogManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Logging\Traits\LoggingAware
 */
final class LoggingAwareTest extends UnitTestCase
{
    /**
     * @var TestingLoggingAware
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TestingLoggingAware();
    }

    /**
     * @test
     */
    public function implementsLoggingAwareInterface(): void
    {
        self::assertInstanceOf(LoggingAware::class, $this->subject);
    }

    /**
     * @test
     */
    public function injectLogManagerGetsLoggerForClass(): void
    {
        $logManagerMock = $this->createMock(LogManagerInterface::class);
        $logManagerMock->expects(self::atLeastOnce())->method('getLogger')->with(TestingLoggingAware::class);

        $this->subject->injectLogManager($logManagerMock);
    }
}
