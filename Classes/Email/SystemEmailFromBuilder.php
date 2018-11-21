<?php

namespace OliverKlee\Oelib\Email;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class builds email subjects with the email data from the install tool.
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
    public function canBuild()
    {
        $configuration = $this->getEmailConfiguration();
        $emailAddress = (string)$configuration['defaultMailFromAddress'];

        return \filter_var($emailAddress, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * @return GeneralEmailRole
     *
     * @throws \UnexpectedValueException
     */
    public function build()
    {
        if (!$this->canBuild()) {
            throw new \UnexpectedValueException(
                'Please set a TYPO3_CONF_VARS/MAIL/defaultMailFromAddress configuration first.',
                1542793620
            );
        }

        $configuration = $this->getEmailConfiguration();
        $result = GeneralUtility::makeInstance(
            GeneralEmailRole::class,
            (string)$configuration['defaultMailFromAddress'],
            (string)$configuration['defaultMailFromName']
        );

        return $result;
    }

    /**
     * @return array
     */
    protected function getEmailConfiguration()
    {
        return $GLOBALS['TYPO3_CONF_VARS']['MAIL'];
    }
}
