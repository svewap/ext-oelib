<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Email;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Email\Attachment;
use OliverKlee\Oelib\Email\EmailCollector;
use OliverKlee\Oelib\Email\Mail;
use OliverKlee\Oelib\Tests\Unit\Email\Fixtures\TestingMailRole;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 */
class AbstractMailerTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var EmailCollector
     */
    private $subject = null;

    /**
     * @var string[]
     */
    const EMAIL = [
        'recipient' => 'any-recipient@example.com',
        'subject' => 'any subject',
        'message' => 'any message',
        'headers' => '',
    ];

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new EmailCollector();

        $message = $this->getMockBuilder(MailMessage::class)->setMethods(['send'])->getMock();
        GeneralUtility::addInstance(MailMessage::class, $message);
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
    public function sendCanAddOneAttachmentFromFile()
    {
        $attachment = new Attachment();
        $attachment->setFileName(__DIR__ . '/Fixtures/test.txt');
        $attachment->setContentType('text/plain');

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', self::EMAIL['recipient']);
        $eMail = new Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject(self::EMAIL['subject']);
        $eMail->setMessage(self::EMAIL['message']);

        $eMail->addAttachment($attachment);

        $this->subject->send($eMail);
        $children = $this->subject->getFirstSentEmail()->getChildren();
        /** @var \Swift_Mime_Attachment $firstChild */
        $firstChild = $children[0];

        self::assertSame(
            'some text',
            $firstChild->getBody()
        );
        self::assertSame(
            'text/plain',
            $firstChild->getContentType()
        );
    }

    /**
     * @test
     */
    public function sendCanAddOneAttachmentFromContent()
    {
        $content = '<p>Hello world!</p>';
        $attachment = new Attachment();
        $attachment->setContent($content);
        $attachment->setContentType('text/html');

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', self::EMAIL['recipient']);
        $eMail = new Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject(self::EMAIL['subject']);
        $eMail->setMessage(self::EMAIL['message']);

        $eMail->addAttachment($attachment);

        $this->subject->send($eMail);
        $children = $this->subject->getFirstSentEmail()->getChildren();
        /** @var \Swift_Mime_Attachment $firstChild */
        $firstChild = $children[0];

        self::assertSame(
            $content,
            $firstChild->getBody()
        );
        self::assertSame(
            'text/html',
            $firstChild->getContentType()
        );
    }

    /**
     * @test
     */
    public function sendCanAddOneAttachmentWithFilenameFromContent()
    {
        $content = '<p>Hello world!</p>';
        $fileName = 'greetings.html';
        $attachment = new Attachment();
        $attachment->setContent($content);
        $attachment->setFileName($fileName);
        $attachment->setContentType('text/html');

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', self::EMAIL['recipient']);
        $eMail = new Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject(self::EMAIL['subject']);
        $eMail->setMessage(self::EMAIL['message']);

        $eMail->addAttachment($attachment);

        $this->subject->send($eMail);
        $children = $this->subject->getFirstSentEmail()->getChildren();
        /** @var \Swift_Mime_Attachment $firstChild */
        $firstChild = $children[0];

        self::assertSame(
            $content,
            $firstChild->getBody()
        );
        self::assertSame(
            $fileName,
            $firstChild->getFilename()
        );
        self::assertSame(
            'text/html',
            $firstChild->getContentType()
        );
    }

    /**
     * @test
     */
    public function sendCanAddTwoAttachments()
    {
        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', self::EMAIL['recipient']);
        $eMail = new Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject(self::EMAIL['subject']);
        $eMail->setMessage(self::EMAIL['message']);

        $attachment1 = new Attachment();
        $attachment1->setFileName(__DIR__ . '/Fixtures/test.txt');
        $attachment1->setContentType('text/plain');
        $eMail->addAttachment($attachment1);
        $attachment2 = new Attachment();
        $attachment2->setFileName(__DIR__ . '/Fixtures/test_2.css');
        $attachment2->setContentType('text/css');
        $eMail->addAttachment($attachment2);

        $this->subject->send($eMail);
        $children = $this->subject->getFirstSentEmail()->getChildren();

        self::assertCount(
            2,
            $children
        );
    }
}
