<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Email\Fixtures\TestingMailRole;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class MailTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Mail
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Mail();
    }

    /*
     * Tests regarding setting and getting the sender.
     */

    /**
     * @test
     */
    public function getSenderInitiallyReturnsNull()
    {
        self::assertNull(
            $this->subject->getSender()
        );
    }

    /**
     * @test
     */
    public function getSenderForNonEmptySenderReturnsSender()
    {
        $sender = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );

        $this->subject->setSender($sender);

        self::assertSame(
            $sender,
            $this->subject->getSender()
        );
    }

    /**
     * @test
     */
    public function hasSenderInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasSender()
        );
    }

    /**
     * @test
     */
    public function hasSenderWithSenderReturnsTrue()
    {
        $sender = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );

        $this->subject->setSender($sender);

        self::assertTrue(
            $this->subject->hasSender()
        );
    }

    /**
     * @test
     */
    public function getReplyToInitiallyReturnsNull()
    {
        self::assertNull($this->subject->getReplyTo());
    }

    /**
     * @test
     */
    public function getReplyToForNonEmptyReplyToReturnsReplyTo()
    {
        $sender = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );

        $this->subject->setReplyTo($sender);

        self::assertSame($sender, $this->subject->getReplyTo());
    }

    /**
     * @test
     */
    public function hasReplyToInitiallyReturnsFalse()
    {
        self::assertFalse($this->subject->hasReplyTo());
    }

    /**
     * @test
     */
    public function hasReplyToWithReplyToReturnsTrue()
    {
        $sender = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );

        $this->subject->setReplyTo($sender);

        self::assertTrue($this->subject->hasReplyTo());
    }

    /*
     * Tests regarding adding and getting the recipients.
     */

    /**
     * @test
     */
    public function getRecipientsInitiallyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getRecipients()
        );
    }

    /**
     * @test
     */
    public function getRecipientsWithOneRecipientReturnsOneRecipient()
    {
        $recipient = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );
        $this->subject->addRecipient($recipient);

        self::assertSame(
            [$recipient],
            $this->subject->getRecipients()
        );
    }

    /**
     * @test
     */
    public function getRecipientsWithTwoRecipientsReturnsTwoRecipients()
    {
        $recipient1 = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );
        $recipient2 = new TestingMailRole(
            'John Doe',
            'foo@bar.com'
        );
        $this->subject->addRecipient($recipient1);
        $this->subject->addRecipient($recipient2);

        self::assertSame(
            [$recipient1, $recipient2],
            $this->subject->getRecipients()
        );
    }

    /*
     * Tests regarding setting and getting the subject.
     */

    /**
     * @test
     */
    public function getSubjectInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getSubject()
        );
    }

    /**
     * @test
     */
    public function getSubjectWithNonEmptySubjectReturnsSubject()
    {
        $this->subject->setSubject('test subject');

        self::assertSame(
            'test subject',
            $this->subject->getSubject()
        );
    }

    /**
     * @test
     */
    public function setSubjectWithEmptySubjectThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$subject must not be empty.'
        );

        $this->subject->setSubject('');
    }

    /**
     * @test
     */
    public function setSubjectWithSubjectContainingCarriageReturnThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$subject must not contain any line breaks or carriage returns.'
        );

        $this->subject->setSubject('test ' . CR . ' subject');
    }

    /**
     * @test
     */
    public function setSubjectWithSubjectContainingLinefeedThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$subject must not contain any line breaks or carriage returns.'
        );

        $this->subject->setSubject('test ' . LF . ' subject');
    }

    /**
     * @test
     */
    public function setSubjectWithSubjectContainingCarriageReturnLinefeedThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$subject must not contain any line breaks or carriage returns.'
        );

        $this->subject->setSubject('test ' . CRLF . ' subject');
    }

    /*
     * Tests regarding setting and getting the message.
     */

    /**
     * @test
     */
    public function getMessageInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getMessage()
        );
    }

    /**
     * @test
     */
    public function getMessageWithNonEmptyMessageReturnsMessage()
    {
        $this->subject->setMessage('test message');

        self::assertSame(
            'test message',
            $this->subject->getMessage()
        );
    }

    /**
     * @test
     */
    public function setMessageWithEmptyMessageThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$message must not be empty.'
        );

        $this->subject->setMessage('');
    }

    /**
     * @test
     */
    public function hasMessageInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasMessage()
        );
    }

    /**
     * @test
     */
    public function hasMessageWithMessageReturnsTrue()
    {
        $this->subject->setMessage('test');

        self::assertTrue(
            $this->subject->hasMessage()
        );
    }

    /*
     * Tests regarding setting and getting the HTML message.
     */

    /**
     * @test
     */
    public function getHTMLMessageInitiallyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getHTMLMessage()
        );
    }

    /**
     * @test
     */
    public function getHTMLMessageWithNonEmptyMessageReturnsMessage()
    {
        $this->subject->setHTMLMessage('test message');

        self::assertSame(
            'test message',
            $this->subject->getHTMLMessage()
        );
    }

    /**
     * @test
     */
    public function setHTMLMessageWithEmptyMessageThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$message must not be empty.'
        );

        $this->subject->setHTMLMessage('');
    }

    /**
     * @test
     */
    public function hasHTMLMessageInitiallyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->hasHTMLMessage()
        );
    }

    /**
     * @test
     */
    public function hasHTMLMessageWithHTMLMessageReturnsTrue()
    {
        $this->subject->setHTMLMessage('<p>test</p>');

        self::assertTrue(
            $this->subject->hasHTMLMessage()
        );
    }

    /*
     * Tests regarding adding and getting attachments.
     */

    /**
     * @test
     */
    public function getAttachmentsInitiallyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getAttachments()
        );
    }

    /**
     * @test
     */
    public function getAttachmentsWithOneAttachmentReturnsOneAttachment()
    {
        $attachment = new \Tx_Oelib_Attachment();
        $attachment->setFileName('test.txt');
        $attachment->setContentType('text/plain');
        $attachment->setContent('Test');
        $this->subject->addAttachment($attachment);

        self::assertSame(
            [$attachment],
            $this->subject->getAttachments()
        );
    }

    /**
     * @test
     */
    public function getAttachmentsWithTwoAttachmentsReturnsTwoAttachments()
    {
        $attachment = new \Tx_Oelib_Attachment();
        $attachment->setFileName('test.txt');
        $attachment->setContentType('text/plain');
        $attachment->setContent('Test');
        $this->subject->addAttachment($attachment);

        $otherAttachment = new \Tx_Oelib_Attachment();
        $otherAttachment->setFileName('second_test.txt');
        $otherAttachment->setContentType('text/plain');
        $otherAttachment->setContent('Second Test');
        $this->subject->addAttachment($otherAttachment);

        self::assertSame(
            [$attachment, $otherAttachment],
            $this->subject->getAttachments()
        );
    }

    /*
     * Tests concerning the return path
     */

    /**
     * @test
     */
    public function getReturnPathInitiallyReturnsAnEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getReturnPath()
        );
    }

    /**
     * @test
     */
    public function setReturnPathSetsReturnPath()
    {
        $this->subject->setReturnPath('foo@bar.com');

        self::assertSame(
            'foo@bar.com',
            $this->subject->getReturnPath()
        );
    }
}
