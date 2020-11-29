<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mail;

use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class returns either an instance of the RealMailer which sends
 * e-mails or an instance of the EmailCollector.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class MailerFactory implements SingletonInterface
{
    /**
     * @var bool whether the test mode is set
     */
    private $isTestMode = false;

    /**
     * @var AbstractMailer the mailer
     */
    private $mailer = null;

    /**
     * Cleans up (if necessary).
     *
     * @return void
     */
    public function cleanUp()
    {
        if ($this->mailer !== null) {
            $this->mailer->cleanUp();
        }
    }

    /**
     * Retrieves the singleton mailer instance. Depending on the mode, this
     * instance is either an e-mail collector or a real mailer.
     *
     * @return AbstractMailer|RealMailer|EmailCollector the singleton mailer object
     */
    public function getMailer()
    {
        if ($this->isTestMode) {
            $className = EmailCollector::class;
        } else {
            $className = RealMailer::class;
        }

        if (!is_object($this->mailer) || (get_class($this->mailer) !== $className)) {
            $this->mailer = GeneralUtility::makeInstance($className);
        }

        return $this->mailer;
    }

    /**
     * Enables the test mode.
     *
     * @return void
     */
    public function enableTestMode()
    {
        $this->isTestMode = true;
    }
}
