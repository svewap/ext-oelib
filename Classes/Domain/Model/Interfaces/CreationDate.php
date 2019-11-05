<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Domain\Model\Interfaces;

/**
 * Interface for domain models with a creation date.
 *
 * The corresponding trait is the default implementation.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
interface CreationDate
{
    /**
     * @return \DateTime|null
     */
    public function getCreationDate();

    /**
     * @param \DateTime $creationDate
     *
     * @return void
     */
    public function setCreationDate(\DateTime $creationDate);
}
