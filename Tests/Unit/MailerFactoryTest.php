<?php

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_MailerFactoryTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_MailerFactory
     */
    protected $subject = null;

    /**
     * @var bool
     */
    protected $deprecationLogEnabledBackup = false;

    protected function setUp()
    {
        $this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = false;

        $this->subject = Tx_Oelib_MailerFactory::getInstance();
    }

    protected function tearDown()
    {
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
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
    public function callingGetInstanceTwoTimesReturnsTheSameInstance()
    {
        self::assertSame(
            $this->subject,
            Tx_Oelib_MailerFactory::getInstance()
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
