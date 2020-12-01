<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MailUtility;

/**
 * This class builds email subjects with the email data from the install tool.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class SystemEmailFromBuilder
{
    /**
     * Checks whether a valid email address has been set as defaultMailFromAddress.
     *
     * @return bool
     */
    public function canBuild(): bool
    {
        $configuration = $this->getEmailConfiguration();
        $emailAddress = (string)$configuration['defaultMailFromAddress'];

        return \filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return array
     */
    protected function getEmailConfiguration(): array
    {
        return $GLOBALS['TYPO3_CONF_VARS']['MAIL'];
    }

    /**
     * @return GeneralEmailRole
     *
     * @throws \UnexpectedValueException
     *
     * @deprecated will be removed in oelib 4.0
     */
    public function build(): GeneralEmailRole
    {
        if (!$this->canBuild()) {
            throw new \UnexpectedValueException(
                'Please set a TYPO3_CONF_VARS/MAIL/defaultMailFromAddress configuration first.',
                1542793620
            );
        }

        return GeneralUtility::makeInstance(
            GeneralEmailRole::class,
            (string)MailUtility::getSystemFromAddress(),
            (string)MailUtility::getSystemFromName()
        );
    }
}
