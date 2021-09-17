<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Interfaces;

/**
 * Interface for domain models with a creation date.
 *
 * The corresponding trait is the default implementation.
 */
interface CreationDate
{
    public function getCreationDate(): ?\DateTime;

    public function setCreationDate(\DateTime $creationDate): void;
}
