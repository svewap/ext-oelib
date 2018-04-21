<?php

use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * This class sends e-mails.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_RealMailer extends \Tx_Oelib_AbstractMailer
{
    /**
     * Sends a Swift e-mail.
     *
     * @param MailMessage $email the e-mail to send.
     *
     * @return void
     */
    protected function sendSwiftMail(MailMessage $email)
    {
        $email->send();
    }
}
