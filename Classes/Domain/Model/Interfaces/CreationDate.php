<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Interfaces;

/**
 * Interface for domain models with a creation date.
 *
 * The corresponding trait is the default implementation.
 *
 * @deprecated will be removed in oelib 6.0
 */
interface CreationDate
{
    public function getCreationDate(): ?\DateTimeImmutable;

    public function setCreationDate(\DateTimeImmutable $creationDate): void;
}
