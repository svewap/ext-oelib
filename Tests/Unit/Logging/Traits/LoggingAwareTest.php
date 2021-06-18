<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Logging\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Logging\Interfaces\LoggingAware;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\ReadOnlyRepository;
use OliverKlee\Oelib\Tests\Unit\Logging\Fixtures\TestingLoggingAware;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Core\Log\LogManagerInterface;

class LoggingAwareTest extends UnitTestCase
{
    /**
     * @var ReadOnlyRepository
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingLoggingAware();
    }

    /**
     * @test
     */
    public function implementsLoggingAwareInterface()
    {
        self::assertInstanceOf(LoggingAware::class, $this->subject);
    }

    /**
     * @test
     */
    public function injectLogManagerGetsLoggerForClass()
    {
        /** @var LogManagerInterface|ObjectProphecy $logManagerProphecy */
        $logManagerProphecy = $this->prophesize(LogManagerInterface::class);
        $logManagerProphecy->getLogger(TestingLoggingAware::class)->shouldBeCalled();

        /** @var LogManagerInterface|ProphecySubjectInterface $logManager */
        $logManager = $logManagerProphecy->reveal();

        $this->subject->injectLogManager($logManager);
    }
}
