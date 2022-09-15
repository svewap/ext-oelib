<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Email;

use OliverKlee\Oelib\Interfaces\MailRole;
use Symfony\Component\Mime\Address as MimeAddress;

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
    public function toMimeAddress(): MimeAddress
    {
        return new MimeAddress($this->getEmailAddress(), $this->getName());
    }
}
