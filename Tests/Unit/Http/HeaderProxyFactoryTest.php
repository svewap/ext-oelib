<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Http;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Http\HeaderCollector;
use OliverKlee\Oelib\Http\HeaderProxyFactory;
use OliverKlee\Oelib\Http\RealHeaderProxy;

/**
 * @covers \OliverKlee\Oelib\Http\HeaderProxyFactory
 */
class HeaderProxyFactoryTest extends UnitTestCase
{
    /**
     * @var HeaderCollector
     */
    private $subject;

    protected function setUp()
    {
        // Only the instance with an enabled test mode can be tested as in the
        // non-test mode added headers are not accessible.
        HeaderProxyFactory::getInstance()->enableTestMode();
        /** @var HeaderCollector $subject */
        $subject = HeaderProxyFactory::getInstance()->getHeaderProxy();
        $this->subject = $subject;
    }

    protected function tearDown()
    {
        HeaderProxyFactory::purgeInstance();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getHeaderProxyInTestMode()
    {
        self::assertSame(
            HeaderCollector::class,
            \get_class($this->subject)
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyInNonTestMode()
    {
        // new instances always have a disabled test mode
        HeaderProxyFactory::purgeInstance();

        self::assertSame(
            RealHeaderProxy ::class,
            \get_class(HeaderProxyFactory::getInstance()->getHeaderProxy())
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyInSameModeAfterPurgeInstanceReturnsNewInstance()
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
    public function getHeaderProxyReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode()
    {
        self::assertSame(
            $this->subject,
            HeaderProxyFactory::getInstance()->getHeaderProxy()
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode()
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
    public function addHeaderAndGetIt()
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
    public function addTwoHeadersAndGetTheLast()
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
    public function addTwoHeadersAndGetBoth()
    {
        $this->subject->addHeader('123: foo.');
        $this->subject->addHeader('123: bar.');

        self::assertSame(
            ['123: foo.', '123: bar.'],
            $this->subject->getAllAddedHeaders()
        );
    }
}
