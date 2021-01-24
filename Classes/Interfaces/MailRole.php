<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interfaces represents an e-mail role, e.g. a sender or a recipient.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
interface MailRole
{
    /**
     * Returns the real name of the e-mail role.
     *
     * @return string the real name of the e-mail role, might be empty
     */
    public function getName();

    /**
     * Returns the e-mail address of the e-mail role.
     *
     * @return string the e-mail address of the e-mail role, might be empty
     */
    public function getEmailAddress();
}
