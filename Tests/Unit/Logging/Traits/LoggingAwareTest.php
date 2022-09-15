<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Logging\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Logging\Interfaces\LoggingAware;
use OliverKlee\Oelib\Tests\Unit\Logging\Fixtures\TestingLoggingAware;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Log\LogManagerInterface;

/**
 * @covers \OliverKlee\Oelib\Logging\Traits\LoggingAware
 */
final class LoggingAwareTest extends UnitTestCase
{
    /**
     * @var TestingLoggingAware
     */
    private $subject = null;

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
        /** @var ObjectProphecy<LogManagerInterface> $logManagerProphecy */
        $logManagerProphecy = $this->prophesize(LogManagerInterface::class);
        $logManagerProphecy->getLogger(TestingLoggingAware::class)->shouldBeCalled();

        $logManager = $logManagerProphecy->reveal();

        $this->subject->injectLogManager($logManager);
    }
}
