<?php

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
class AbstractMailerTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_EmailCollector
     */
    private $subject = null;

    /**
     * @var MailMessage
     */
    private $message1 = null;

    /**
     * @var string[]
     */
    private $email = [
        'recipient' => 'any-recipient@email-address.org',
        'subject' => 'any subject',
        'message' => 'any message',
        'headers' => '',
    ];

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_EmailCollector();

        $this->message1 = $this->getMockBuilder(MailMessage::class)->setMethods(['send'])->getMock();
        GeneralUtility::addInstance(MailMessage::class, $this->message1);
    }

    protected function tearDown()
    {
        // Get any surplus instances added via \TYPO3\CMS\Core\Utility\GeneralUtility::addInstance.
        GeneralUtility::makeInstance(MailMessage::class);

        parent::tearDown();
    }

    /*
     * Tests concerning send
     */

    /**
     * @test
     */
    public function getSentEmailsWithoutAnyEmailReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getSentEmails()
        );
    }

    /**
     * @test
     */
    public function getNumberOfSentEmailsWithoutAnyEmailReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function getFirstSentEmailWithoutAnyEmailReturnsNull()
    {
        self::assertNull(
            $this->subject->getFirstSentEmail()
        );
    }

    /**
     * @test
     */
    public function sendWithoutSenderThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$email must have a sender set.');

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->addRecipient($emailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithoutRecipientThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The e-mail must have at least one recipient.');

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->setSender($emailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithoutSubjectThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The e-mail subject must not be empty.');

        $email = new \Tx_Oelib_Mail();
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->setSender($emailRole);
        $email->addRecipient($emailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithoutMessageThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The e-mail message must not be empty.');

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->setSender($emailRole);
        $email->addRecipient($emailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithAllValidEmailAddressesNotThrowsException()
    {
        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->setSender($emailRole);
        $email->addRecipient($emailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithAllValidLocalhostEmailAddressesNotThrowsException()
    {
        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@localhost');
        $email->setSender($emailRole);
        $email->addRecipient($emailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithEmptyFromAddressThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->addRecipient($emailRole);

        $emptyEmailRole = new TestingMailRole('John Doe', '');
        $email->setSender($emptyEmailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithInvalidFromAddressThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->addRecipient($emailRole);

        $invalidEmailRole =
            new TestingMailRole('John Doe', 'hkqwbeqwbasgrfa asdfa');
        $email->setSender($invalidEmailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithEmptyToAddressThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->setSender($emailRole);

        $emptyEmailRole = new TestingMailRole('John Doe', '');
        $email->addRecipient($emptyEmailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendWithInvalidToAddressThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $email = new \Tx_Oelib_Mail();
        $email->setSubject('Everybody is happy!');
        $email->setMessage('That is the way it is.');

        $emailRole = new TestingMailRole('John Doe', 'john@example.com');
        $email->setSender($emailRole);

        $invalidEmailRole =
            new TestingMailRole('John Doe', 'hkqwbeqwbasgrfa asdfa');
        $email->addRecipient($invalidEmailRole);

        $this->subject->send($email);
    }

    /**
     * @test
     */
    public function sendSetsSenderNameAndEmail()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        $sentEmail = $this->subject->getFirstSentEmail();
        self::assertSame(
            [$sender->getEmailAddress() => $sender->getName()],
            $sentEmail->getFrom()
        );
    }

    /**
     * @test
     */
    public function sendSetsRecipientNameAndEmail()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        $sentEmail = $this->subject->getFirstSentEmail();
        self::assertSame(
            [$recipient->getEmailAddress() => $recipient->getName()],
            $sentEmail->getTo()
        );
    }

    /**
     * @test
     */
    public function sendForTwoRecipientsSendsTwoEmails()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);
        $recipient1 = new TestingMailRole('John Doe', 'joe@example.com');
        $eMail->addRecipient($recipient1);
        $recipient2 = new TestingMailRole('Jane Doe', 'jane@example.com');
        $eMail->addRecipient($recipient2);

        $this->subject->send($eMail);

        self::assertSame(
            2,
            $this->subject->getNumberOfSentEmails()
        );
    }

    /**
     * @test
     */
    public function sendSetsSubject()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        $sentEmail = $this->subject->getFirstSentEmail();
        self::assertSame(
            $this->email['subject'],
            $sentEmail->getSubject()
        );
    }

    /**
     * @test
     */
    public function sendingPlainTextMailUsesDefaultCharacterSet()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        self::assertSame(
            'utf-8',
            $this->subject->getFirstSentEmail()->getCharset()
        );
    }

    /**
     * @test
     */
    public function sendSetsPlainTextBody()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        $sentEmail = $this->subject->getFirstSentEmail();
        self::assertSame(
            $this->email['message'],
            $sentEmail->getBody()
        );
    }

    /**
     * @test
     */
    public function sendingPlainTextMailUsesPlainTextEncoding()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        self::assertSame(
            'text/plain',
            $this->subject->getFirstSentEmail()->getContentType()
        );
    }

    /**
     * @test
     */
    public function sendingPlainTextMailByDefaultRemovesAnyCarriageReturnFromBody()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage(
            'one long line ...........................................' . CRLF .
            'now a blank line:' . LF . LF .
            'another long line .........................................' . LF .
            'and a line with umlauts: Hörbär saß früh.'
        );

        $this->subject->send($eMail);

        self::assertNotContains(
            CR,
            $this->subject->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendingPlainTextMailWithFormattingRemovesAnyCarriageReturnFromBody()
    {
        $this->subject->sendFormattedEmails(true);

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage(
            'one long line ...........................................' . CRLF .
            'now a blank line:' . LF . LF .
            'another long line .........................................' . LF .
            'and a line with umlauts: Hörbär saß früh.'
        );

        $this->subject->send($eMail);

        self::assertNotContains(
            CR,
            $this->subject->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendingPlainTextMailWithoutFormattingNotRemovesAnyCarriageReturnFromBody()
    {
        $this->subject->sendFormattedEmails(false);

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage(
            'one long line ...........................................' . CRLF .
            'now a blank line:' . LF . LF .
            'another long line .........................................' . LF .
            'and a line with umlauts: Hörbär saß früh.'
        );

        $this->subject->send($eMail);

        self::assertContains(
            CR,
            $this->subject->getFirstSentEmail()->getBody()
        );
    }

    /**
     * @test
     */
    public function sendSetsHtmlBody()
    {
        $htmlMessage = '<h1>Very cool HTML message</h1>' . LF . '<p>Great to have HTML e-mails in oelib.</p>';
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage('This is the plain text message.');
        $eMail->setHTMLMessage($htmlMessage);

        $this->subject->send($eMail);

        $children = $this->subject->getFirstSentEmail()->getChildren();
        /** @var \Swift_Mime_MimeEntity $firstChild */
        $firstChild = $children[0];
        self::assertSame(
            $htmlMessage,
            $firstChild->getBody()
        );
    }

    /**
     * @test
     */
    public function sendSetsHtmlBodyWithTextHtmlContentType()
    {
        $htmlMessage = '<h1>Very cool HTML message</h1>' . LF . '<p>Great to have HTML e-mails in oelib.</p>';
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage('This is the plain text message.');
        $eMail->setHTMLMessage($htmlMessage);

        $this->subject->send($eMail);

        $children = $this->subject->getFirstSentEmail()->getChildren();
        /** @var \Swift_Mime_MimeEntity $firstChild */
        $firstChild = $children[0];
        self::assertSame(
            'text/html',
            $firstChild->getContentType()
        );
    }

    /**
     * @test
     */
    public function sendWithReturnPathSetsReturnPath()
    {
        $returnPath = 'return@example.com';

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);
        $eMail->setReturnPath($returnPath);

        $this->subject->send($eMail);

        $sentEmail = $this->subject->getFirstSentEmail();
        self::assertSame(
            $returnPath,
            $sentEmail->getReturnPath()
        );
    }

    /**
     * @test
     */
    public function sendWithoutReturnPathNotSetsReturnPath()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $this->subject->send($eMail);

        $sentEmail = $this->subject->getFirstSentEmail();
        self::assertNull(
            $sentEmail->getReturnPath()
        );
    }
}
