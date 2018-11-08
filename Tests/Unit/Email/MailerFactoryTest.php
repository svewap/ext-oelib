<?php

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MailerFactoryTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_MailerFactory
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_MailerFactory();
    }

    /*
     * Tests concerning the basic functionality
     */

    /**
     * @test
     */
    public function factoryIsSingleton()
    {
        self::assertInstanceOf(
            SingletonInterface::class,
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getMailerInTestModeReturnsEmailCollector()
    {
        $this->subject->enableTestMode();
        self::assertInstanceOf(\Tx_Oelib_EmailCollector::class, $this->subject->getMailer());
    }

    /**
     * @test
     */
    public function getMailerReturnsTheSameObjectWhenTheInstanceWasNotDiscarded()
    {
        self::assertSame(
            $this->subject->getMailer(),
            $this->subject->getMailer()
        );
    }
}
