<?php

namespace OliverKlee\Oelib\Tests\Functional\Email;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Unit\Email\Fixtures\TestingMailRole;
use TYPO3\CMS\Core\Mail\MailMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AbstractMailerTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

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
        parent::setUp();

        $this->subject = new \Tx_Oelib_EmailCollector();

        $this->message1 = $this->getMock(MailMessage::class, ['send']);
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
    public function sendCanAddOneAttachmentFromFile()
    {
        $attachment = new \Tx_Oelib_Attachment();
        $attachment->setFileName(__DIR__ . '/Fixtures/test.txt');
        $attachment->setContentType('text/plain');

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

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
        $attachment = new \Tx_Oelib_Attachment();
        $attachment->setContent($content);
        $attachment->setContentType('text/html');

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

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
        $attachment = new \Tx_Oelib_Attachment();
        $attachment->setContent($content);
        $attachment->setFileName($fileName);
        $attachment->setContentType('text/html');

        $sender = new TestingMailRole('', 'any-sender@email-address.org');
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

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
        $recipient = new TestingMailRole('John Doe', $this->email['recipient']);
        $eMail = new \Tx_Oelib_Mail();
        $eMail->setSender($sender);
        $eMail->addRecipient($recipient);
        $eMail->setSubject($this->email['subject']);
        $eMail->setMessage($this->email['message']);

        $attachment1 = new \Tx_Oelib_Attachment();
        $attachment1->setFileName(__DIR__ . '/Fixtures/test.txt');
        $attachment1->setContentType('text/plain');
        $eMail->addAttachment($attachment1);
        $attachment2 = new \Tx_Oelib_Attachment();
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
