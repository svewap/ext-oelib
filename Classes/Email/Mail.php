<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithAccessors;
use OliverKlee\Oelib\Interfaces\MailRole;
use Pelago\Emogrifier\CssInliner;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents an e-mail.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Mail extends AbstractObjectWithAccessors
{
    /**
     * @var MailRole the sender of the e-mail
     */
    private $sender = null;

    /**
     * @var MailRole
     */
    private $replyTo = null;

    /**
     * @var array<int, MailRole> the recipients of the e-mail
     */
    private $recipients = [];

    /**
     * @var array<string, mixed> the data of this object
     */
    private $data = [];

    /**
     * @var array<int, Attachment> attachments of the e-mail
     */
    private $attachments = [];

    /**
     * @var array<string, string> the CSS files which already have been read
     */
    private static $cssFileCache = [];

    /**
     * @var string the return path for the e-mails
     */
    private $returnPath = '';

    /**
     * Sets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to set, must not be empty
     * @param mixed $value the data for the key $key
     *
     * @return void
     */
    protected function set($key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Gets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
     *
     * @return mixed the data for the key $key, will be an empty string if the key has not been set yet
     */
    protected function get(string $key)
    {
        return $this->data[$key] ?? '';
    }

    /**
     * Sets the sender of the e-mail.
     *
     * @param MailRole $sender the sender of the e-mail
     *
     * @return void
     */
    public function setSender(MailRole $sender)
    {
        $this->sender = $sender;
    }

    /**
     * Returns the sender of the e-mail.
     *
     * @return MailRole|null
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Returns whether the e-mail has a sender.
     */
    public function hasSender(): bool
    {
        return $this->sender instanceof MailRole;
    }

    /**
     * @return MailRole|null
     */
    public function getReplyTo()
    {
        return $this->replyTo;
    }

    /**
     * @param MailRole $replyTo
     *
     * @return void
     */
    public function setReplyTo(MailRole $replyTo)
    {
        $this->replyTo = $replyTo;
    }

    /**
     * @return bool
     */
    public function hasReplyTo(): bool
    {
        return $this->replyTo instanceof MailRole;
    }

    /**
     * Adds a recipient for the e-mail.
     *
     * @param MailRole $recipient a recipient for the e-mail, must not be empty
     *
     * @return void
     */
    public function addRecipient(MailRole $recipient)
    {
        $this->recipients[] = $recipient;
    }

    /**
     * Returns the recipients of the e-mail.
     *
     * @return array<int, MailRole> the recipients of the e-mail, will be empty if no recipients have been set
     */
    public function getRecipients(): array
    {
        return $this->recipients;
    }

    /**
     * Sets the subject of the e-mail.
     *
     * @param string $subject the subject of the e-mail, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setSubject(string $subject)
    {
        if ($subject === '') {
            throw new \InvalidArgumentException('$subject must not be empty.', 1331488802);
        }

        if ((strpos($subject, CR) !== false) || (strpos($subject, LF) !== false)) {
            throw new \InvalidArgumentException(
                '$subject must not contain any line breaks or carriage returns.',
                1331488817
            );
        }

        $this->setAsString('subject', $subject);
    }

    /**
     * Returns the subject of the e-mail.
     *
     * @return string the subject of the e-mail, will be empty if the subject has not been set
     */
    public function getSubject(): string
    {
        return $this->getAsString('subject');
    }

    /**
     * Sets the message of the e-mail.
     *
     * @param string $message the message of the e-mail, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setMessage(string $message)
    {
        if ($message === '') {
            throw new \InvalidArgumentException('$message must not be empty.', 1331488834);
        }

        $this->setAsString('message', $message);
    }

    /**
     * Returns the message of the e-mail.
     *
     * @return string the message of the e-mail, will be empty if the message has not been set
     */
    public function getMessage(): string
    {
        return $this->getAsString('message');
    }

    /**
     * Returns whether the e-mail has a message.
     *
     * @return bool TRUE if the e-mail has a message, FALSE otherwise
     */
    public function hasMessage(): bool
    {
        return $this->hasString('message');
    }

    /**
     * Sets the HTML message of the e-mail.
     *
     * @param string $message the HTML message of the e-mail, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setHTMLMessage(string $message)
    {
        if ($message === '') {
            throw new \InvalidArgumentException('$message must not be empty.', 1331488845);
        }

        if ($this->hasCssFile()) {
            $this->loadCssInliner();
            $messageToStore = CssInliner::fromHtml($message)->inlineCss($this->getCssFile())->render();
        } else {
            $messageToStore = $message;
        }

        $this->setAsString('html_message', $messageToStore);
    }

    /**
     * Loads the CssInliner class.
     *
     * @return void
     */
    protected function loadCssInliner()
    {
        if (!class_exists(CssInliner::class)) {
            require_once __DIR__ . '/../../Resources/Private/Php/vendor/autoload.php';
        }
    }

    /**
     * Returns the HTML message of the e-mail.
     *
     * @return string the HTML message of the e-mail, will be empty if the message has not been set
     */
    public function getHTMLMessage(): string
    {
        return $this->getAsString('html_message');
    }

    /**
     * Returns whether the e-mail has an HTML message.
     *
     * @return bool
     */
    public function hasHTMLMessage(): bool
    {
        return $this->hasString('html_message');
    }

    /**
     * Adds an attachment to the e-mail.
     *
     * @param Attachment $attachment the attachment to add
     *
     * @return void
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;
    }

    /**
     * Returns the attachments of the e-mail.
     *
     * @return array<int, Attachment> the attachments of the e-mail, might be empty
     */
    public function getAttachments(): array
    {
        return $this->attachments;
    }

    /**
     * Sets the CSS file for sending an e-mail.
     *
     * @param string $cssFile the complete path to a valid CSS file, may be empty
     *
     * @return void
     */
    public function setCssFile(string $cssFile)
    {
        if (!$this->cssFileIsCached($cssFile)) {
            $absoluteFileName = GeneralUtility::getFileAbsFileName($cssFile);
            if (
                ($cssFile !== '') && is_readable($absoluteFileName)
            ) {
                self::$cssFileCache[$cssFile] = file_get_contents($absoluteFileName);
            } else {
                self::$cssFileCache[$cssFile] = '';
            }
        }

        $this->setAsString('cssFile', self::$cssFileCache[$cssFile]);
    }

    /**
     * Returns whether e-mail has a CSS file.
     *
     * @return bool TRUE if a CSS file has been set, FALSE otherwise
     */
    public function hasCssFile(): bool
    {
        return $this->hasString('cssFile');
    }

    /**
     * Returns the stored content of the CSS file.
     *
     * @return string the file contents of the CSS file, will be empty if no CSS file was stored
     */
    public function getCssFile(): string
    {
        return $this->getAsString('cssFile');
    }

    /**
     * Checks whether the given CSS file has already been read.
     *
     * @param string $cssFile the absolute path to the CSS file, must not be empty
     *
     * @return bool TRUE when the CSS file was read earlier, FALSE otherwise
     */
    private function cssFileIsCached(string $cssFile): bool
    {
        return isset(self::$cssFileCache[$cssFile]);
    }

    /**
     * Sets the return path (and errors-to) of the e-mail.
     *
     * The return path is stored in a way that the MIME mail class can read it.
     * If a return path has already been set, it will be overridden by the new value.
     * If an empty string is given this function is a no-op.
     *
     * @param string $returnPath the e-mail address for the return path, may be empty
     *
     * @return void
     */
    public function setReturnPath(string $returnPath)
    {
        $this->returnPath = $returnPath;
    }

    /**
     * Returns the return path set via setReturnPath
     *
     * @return string the return path, will be an empty string if nothing has been stored
     */
    public function getReturnPath(): string
    {
        return $this->returnPath;
    }
}
