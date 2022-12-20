<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

use Symfony\Component\Mime\Address as MimeAddress;

/**
 * This interfaces represents an e-mail role, e.g. a sender or a recipient.
 *
 * The default implementation of this interface is `ConvertableToMimeAddressTrait`.
 *
 * @mixin MailRole
 *
 * @deprecated will be removed in oelib 6.0
 */
interface ConvertableToMimeAddress
{
    /**
     * Converts this address to a Symfony MIME address.
     */
    public function toMimeAddress(): MimeAddress;
}
