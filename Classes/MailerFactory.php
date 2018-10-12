<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class returns either an instance of the \Tx_Oelib_RealMailer which sends
 * e-mails or an instance of the \Tx_Oelib_EmailCollector.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_MailerFactory implements \TYPO3\CMS\Core\SingletonInterface
{
    /**
     * @var bool whether the test mode is set
     */
    private $isTestMode = false;

    /**
     * @var \Tx_Oelib_AbstractMailer the mailer
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
     * @return \Tx_Oelib_AbstractMailer|\Tx_Oelib_RealMailer|\Tx_Oelib_EmailCollector the singleton mailer object
     */
    public function getMailer()
    {
        if ($this->isTestMode) {
            $className = \Tx_Oelib_EmailCollector::class;
        } else {
            $className = \Tx_Oelib_RealMailer::class;
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
