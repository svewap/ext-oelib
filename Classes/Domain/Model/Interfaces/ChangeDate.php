<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Interfaces;

/**
 * Interface for domain models with a change date.
 *
 * The corresponding trait is the default implementation.
 */
interface ChangeDate
{
    /**
     * @return \DateTime|null
     */
    public function getChangeDate();

    /**
     * @param \DateTime $creationDate
     *
     * @return void
     */
    public function setChangeDate(\DateTime $creationDate);
}
