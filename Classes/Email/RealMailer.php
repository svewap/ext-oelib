<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use TYPO3\CMS\Core\Mail\MailMessage;

/**
 * This class sends e-mails.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class RealMailer extends AbstractMailer
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
