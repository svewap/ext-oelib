<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

/**
 * Trait for domain models with a creation date.
 *
 * This is the default implementation of the corresponding interface.
 *
 * Any models that use this mode will still need to map the "crdate" column using "mapOnProperty".
 */
trait CreationDate
{
    /**
     * @var \DateTimeImmutable|null
     */
    protected $creationDate = null;

    public function getCreationDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setCreationDate(\DateTimeImmutable $creationDate): void
    {
        $this->creationDate = $creationDate;
    }
}
