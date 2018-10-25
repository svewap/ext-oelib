<?php

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_LegacyUnit_HeaderProxyFactoryTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_HeaderCollector
     */
    private $subject;

    protected function setUp()
    {
        // Only the instance with an enabled test mode can be tested as in the
        // non-test mode added headers are not accessible.
        \Tx_Oelib_HeaderProxyFactory::getInstance()->enableTestMode();
        $this->subject = \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy();
    }

    protected function tearDown()
    {
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();
    }

    /**
     * @test
     */
    public function getHeaderProxyInTestMode()
    {
        self::assertSame(
            \Tx_Oelib_HeaderCollector::class,
            get_class($this->subject)
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyInNonTestMode()
    {
        // new instances always have a disabled test mode
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();

        self::assertSame(
            \Tx_Oelib_RealHeaderProxy::class,
            get_class(\Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy())
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyInSameModeAfterPurgeInstanceReturnsNewInstance()
    {
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();
        $instance = \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy();
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();

        self::assertNotSame(
            $instance,
            \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy()
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyReturnsTheSameObjectWhenCalledInTheSameClassInTheSameMode()
    {
        self::assertSame(
            $this->subject,
            \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy()
        );
    }

    /**
     * @test
     */
    public function getHeaderProxyNotReturnsTheSameObjectWhenCalledInTheSameClassInAnotherMode()
    {
        // new instances always have a disabled test mode
        \Tx_Oelib_HeaderProxyFactory::purgeInstance();

        self::assertNotSame(
            $this->subject,
            \Tx_Oelib_HeaderProxyFactory::getInstance()->getHeaderProxy()
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
