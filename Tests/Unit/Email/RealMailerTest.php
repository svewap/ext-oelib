<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Email\Fixtures\TestingMailRole;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class RealMailerTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_RealMailer
     */
    private $subject = null;

    /**
     * @var MailMessage|\PHPUnit_Framework_MockObject_MockObject
     */
    private $message = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_RealMailer();

        $this->message = $this->getMockBuilder(MailMessage::class)->setMethods(['send'])->getMock();
        GeneralUtility::addInstance(MailMessage::class, $this->message);
    }

    /**
     * @test
     */
    public function sendSendsEmail()
    {
        $senderAndRecipient = new TestingMailRole('John Doe', 'john@example.com');
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($senderAndRecipient);
        $eMail->addRecipient($senderAndRecipient);
        $eMail->setSubject('Hello world!');
        $eMail->setMessage('Welcome!');

        $this->message->expects(self::once())->method('send');

        $this->subject->send($eMail);
    }
}
