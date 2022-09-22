<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Http;

use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Http\RealHeaderProxy;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Http\HeaderCollector
 * @covers \OliverKlee\Oelib\Http\HeaderProxyFactory
 */
final class HeaderProxyFactoryTest extends UnitTestCase
{
    /**
     * @var HeaderCollector
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Only the instance with an enabled test mode can be tested as in the
        // non-test mode added headers are not accessible.
        HeaderProxyFactory::getInstance()->enableTestMode();
        /** @var HeaderCollector $subject */
        $subject = HeaderProxyFactory::getInstance()->getHeaderProxy();
        $this->subject = $subject;
    }

    protected function tearDown(): void
    {
        HeaderProxyFactory::purgeInstance();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getHeaderProxyInTestModeReturnsHeaderCollector(): void
    {
        HeaderProxyFactory::purgeInstance();
        HeaderProxyFactory::getInstance()->enableTestMode();

        $result = HeaderProxyFactory::getInstance()->getHeaderProxy();

        self::assertInstanceOf(HeaderCollector::class, $result);
    }

    /**
     * @test
     */
    public function getHeaderProxyInNonTestModeReturnsRealHeaderProxy(): void
    {
        // new instances always have a disabled test mode
        HeaderProxyFactory::purgeInstance();

        $result = HeaderProxyFactory::getInstance()->getHeaderProxy();

        self::assertInstanceOf(RealHeaderProxy::class, $result);
    }

    /**
     * @test
     */
    public function getHeaderProxyInSameModeAfterPurgeInstanceReturnsNewInstance(): void
    {
        HeaderProxyFactory::purgeInstance();
        $instance = HeaderProxyFactory::getInstance()->getHeaderProxy();
        HeaderProxyFactory::purgeInstance();

        self::assertNotSame(
            $instance,
            HeaderProxyFactory::getInstance()->getHeaderProxy()
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode(): void
    {
        self::assertSame(
            $this->subject,
            HeaderProxyFactory::getInstance()->getHeaderProxy()
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode(): void
    {
        // new instances always have a disabled test mode
        HeaderProxyFactory::purgeInstance();

        self::assertNotSame(
            $this->subject,
            HeaderProxyFactory::getInstance()->getHeaderProxy()
        );
    }

    /**
     * @test
     */
    public function addHeaderAndGetIt(): void
    {
        $this->subject->addHeader('123: foo.');

        self::assertSame(
            '123: foo.',
            $this->subject->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function addTwoHeadersAndGetTheLast(): void
    {
        $this->subject->addHeader('123: foo.');
        $this->subject->addHeader('123: bar.');

        self::assertSame(
            '123: bar.',
            $this->subject->getLastAddedHeader()
        );
    }

    /**
     * @test
     */
    public function addTwoHeadersAndGetBoth(): void
    {
        $this->subject->addHeader('123: foo.');
        $this->subject->addHeader('123: bar.');

        self::assertSame(
            ['123: foo.', '123: bar.'],
            $this->subject->getAllAddedHeaders()
        );
    }

    /**
     * @test
     */
    public function getHeaderCollectorInNonTestModeThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1630827563);
        $this->expectExceptionMessage('getHeaderCollector() may only be called in test mode.');

        // new instances always have a disabled test mode
        HeaderProxyFactory::purgeInstance();

        HeaderProxyFactory::getInstance()->getHeaderCollector();
    }

    /**
     * @test
     */
    public function getHeaderCollectorInTestModeReturnsHeaderCollector(): void
    {
        HeaderProxyFactory::getInstance()->enableTestMode();

        $result = HeaderProxyFactory::getInstance()->getHeaderCollector();

        self::assertInstanceOf(HeaderCollector::class, $result);
    }
}
