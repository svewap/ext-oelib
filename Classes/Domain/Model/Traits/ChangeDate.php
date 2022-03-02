<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Trait for domain models with a change date.
 *
 * This is the default implementation of the corresponding interface.
 *
 * Any models that use this mode will still need to map the "tstamp" column using "mapOnProperty".
 *
 * @mixin AbstractEntity
 */
trait ChangeDate
{
    /**
     * @var \DateTimeImmutable|null
     */
    protected $creationDate = null;

    public function getChangeDate(): ?\DateTimeImmutable
    {
        return $this->creationDate;
    }

    public function setChangeDate(\DateTimeImmutable $creationDate): void
    {
        $this->creationDate = $creationDate;
    }
}
