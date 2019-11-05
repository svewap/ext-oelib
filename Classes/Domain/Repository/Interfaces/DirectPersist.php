<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Domain\Repository\Interfaces;

/**
 * Interface that adds a persistAll method to a repository. The idea is that users of this repository should not need to
 * care about the persistence manager.
 *
 * The corresponding trait is the default implementation.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
interface DirectPersist
{
    /**
     * Persists all added or updated models.
     *
     * @return void
     */
    public function persistAll();
}
