<?php

use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * This class stores all parameters which were meant to be sent as an e-mail and
 * provides various functions to get them for testing purposes.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_EmailCollector extends \Tx_Oelib_AbstractMailer
{
    /**
     * @var MailMessage[]
     */
    protected $sentEmails = [];

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
