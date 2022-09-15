<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use OliverKlee\Oelib\Interfaces\MailRole;

/**
 * This is the default implementation of the `ConvertableToMimeAddress` interface.
 *
 * @mixin MailRole
 */
trait ConvertableToMimeAddressTrait
{
    /**
     * Converts this address to a Symfony MIME address.
     */
    public function toMimeAddress(): \Symfony\Component\Mime\Address
    {
        return new \Symfony\Component\Mime\Address($this->getEmailAddress(), $this->getName());
    }
}
