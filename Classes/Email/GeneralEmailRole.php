<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use OliverKlee\Oelib\Interfaces\MailRole;

/**
 * A general email subject.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GeneralEmailRole implements MailRole
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
    public function __construct(string $emailAddress, string $name = '')
    {
        $this->emailAddress = $emailAddress;
        $this->name = $name;
    }

    /**
     * Returns the e-mail address of the e-mail role.
     *
     * @return string the e-mail address of the e-mail role, might be empty
     */
    public function getEmailAddress(): string
    {
        return $this->emailAddress;
    }

    /**
     * Returns the real name of the e-mail role.
     *
     * @return string the real name of the e-mail role, might be empty
     */
    public function getName(): string
    {
        return $this->name;
    }
}
