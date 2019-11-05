<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Email;

/**
 * A general email subject.
 *
 * @deprecated will be removed in oelib 4.0
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GeneralEmailRole implements \Tx_Oelib_Interface_MailRole
{
    /**
     * @var string
     */
    protected $emailAddress = '';

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @param string $emailAddress
     * @param string $name
     */
    public function __construct($emailAddress, $name = '')
    {
        $this->emailAddress = $emailAddress;
        $this->name = $name;
    }

    /**
     * Returns the e-mail address of the e-mail role.
     *
     * @return string the e-mail address of the e-mail role, might be empty
     */
    public function getEmailAddress()
    {
        return $this->emailAddress;
    }

    /**
     * Returns the real name of the e-mail role.
     *
     * @return string the real name of the e-mail role, might be empty
     */
    public function getName()
    {
        return $this->name;
    }
}
