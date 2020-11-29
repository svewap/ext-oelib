<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mail\Mail;
use OliverKlee\Oelib\Mail\RealMailer;
use OliverKlee\Oelib\Tests\Unit\Email\Fixtures\TestingMailRole;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var RealMailer
     */
    private $subject = null;

    /**
     * @var MailMessage|MockObject
     */
    private $message = null;

    protected function setUp()
    {
        $this->subject = new RealMailer();

        $this->message = $this->getMockBuilder(MailMessage::class)->setMethods(['send'])->getMock();
        GeneralUtility::addInstance(MailMessage::class, $this->message);
    }

    /**
     * @test
     */
    public function sendSendsEmail()
    {
        $senderAndRecipient = new TestingMailRole('John Doe', 'john@example.com');
        $eMail = new Mail();
        $eMail->setSender($senderAndRecipient);
        $eMail->addRecipient($senderAndRecipient);
        $eMail->setSubject('Hello world!');
        $eMail->setMessage('Welcome!');

        $this->message->expects(self::once())->method('send');

        $this->subject->send($eMail);
    }
}
