<?php

use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * This class stores all parameters which were meant to be sent as an e-mail and
 * provides various functions to get them for testing purposes.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_EmailCollector extends Tx_Oelib_AbstractMailer
{
    /**
     * Two-dimensional array of e-mail data.
     * Each e-mail is stored in one element. So the number of elements in the
     * first dimension depends on how many e-mails are currently stored. One
     * stored e-mail is always an associative array with four elements named
     * 'recipient', 'subject', 'message' and 'headers'.
     *
     * @var array[]
     */
    private $emailData = [];

    /**
     * @var MailMessage[]
     */
    protected $sentEmails = [];

    /**
     * The destructor.
     */
    public function __destruct()
    {
        $this->cleanUp();
    }

    /**
     * Cleans up (if necessary).
     *
     * @return void
     */
    public function cleanUp()
    {
        $this->sentEmails = [];
    }

    /**
     * Sends a Swift e-mail.
     *
     * @param MailMessage $email the e-mail to send.
     *
     * @return void
     */
    protected function sendSwiftMail(MailMessage $email)
    {
        $this->sentEmails[] = $email;
    }

    /**
     * Returns the last e-mail or an empty array if there is none.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @return array e-mail address, subject, message and headers of the last e-mail in an array, will be empty if there is
     *               no e-mail
     */
    public function getLastEmail()
    {
        if (empty($this->emailData)) {
            return [];
        }

        return end($this->emailData);
    }

    /**
     * Returns all e-mails sent with this instance or an empty array if there is none.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @return array[] two-dimensional array with one element for each e-mail, each inner array has four elements
     *               'recipient', 'subject', 'message' and 'headers', will be empty if there are no e-mails
     *
     * @see emailData
     */
    public function getAllEmail()
    {
        return $this->emailData;
    }

    /**
     * Returns the last e-mail's recipient.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @return string recipient of the last sent e-mail or an empty string if there is none
     */
    public function getLastRecipient()
    {
        return $this->getElementFromLastEmail('recipient');
    }

    /**
     * Returns the last e-mail's subject.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @return string subject of the last sent e-mail or an empty string if there is none
     */
    public function getLastSubject()
    {
        return $this->getElementFromLastEmail('subject');
    }

    /**
     * Returns the last e-mail's body.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @return string body of the last sent e-mail or an empty string if there is none
     */
    public function getLastBody()
    {
        return $this->getElementFromLastEmail('message');
    }

    /**
     * Returns the last e-mail's additional headers.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @return string headers of the last sent e-mail or an empty string if there are none
     */
    public function getLastHeaders()
    {
        return $this->getElementFromLastEmail('headers');
    }

    /**
     * Returns an element from the array with the last e-mail.
     *
     * @deprecated will be removed in oelib 2.0.0, use getSentEmails instead
     *
     * @param string $key key of the element to return, must be "recipient", "subject", "message" or "headers"
     *
     * @return string value of the element, will be an empty string if there was none
     *
     * @throws InvalidArgumentException
     */
    private function getElementFromLastEmail($key)
    {
        if (!in_array($key, ['recipient', 'subject', 'message', 'headers'], true)) {
            throw new InvalidArgumentException(
                'The key "' . $key . '" is invalid. It must be "recipient", "subject", "message" or "headers".',
                1331488710
            );
        }
        if (empty($this->emailData)) {
            return '';
        }

        $lastEmail = $this->getLastEmail();

        return $lastEmail[$key];
    }

    /**
     * Returns the e-mails that would have been sent via the send method.
     *
     * @return MailMessage[]
     */
    public function getSentEmails()
    {
        return $this->sentEmails;
    }

    /**
     * Returns the number of e-mails that would have been sent via the send method.
     *
     * @return int the number of send e-mails, will be >= 0
     */
    public function getNumberOfSentEmails()
    {
        return count($this->getSentEmails());
    }

    /**
     * Returns the first sent-email or NULL if none has been sent.
     *
     * @return MailMessage|null
     */
    public function getFirstSentEmail()
    {
        if ($this->getNumberOfSentEmails() === 0) {
            return null;
        }

        $sendEmails = $this->getSentEmails();

        return $sendEmails[0];
    }
}
