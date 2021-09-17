<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository\Traits;

use TYPO3\CMS\Extbase\Persistence\RepositoryInterface;

/**
 * This trait marks repositories as read-only.
 *
 * @mixin RepositoryInterface
 */
trait ReadOnlyRepository
{
    /**
     * Adds an object to this repository.
     *
     * @param object $object
     *
     * @throws \BadMethodCallException
     */
    public function add($object): void
    {
        $this->preventWriteOperation();
    }

    /**
     * Removes an object from this repository.
     *
     * @param object $object
     *
     * @throws \BadMethodCallException
     */
    public function remove($object): void
    {
        $this->preventWriteOperation();
    }

    /**
     * Replaces an existing object with the same identifier by the given object.
     *
     * @param object $modifiedObject
     *
     * @throws \BadMethodCallException
     */
    public function update($modifiedObject): void
    {
        $this->preventWriteOperation();
    }

    /**
     * Removes all objects of this repository as if remove() was called for all of them.
     *
     * @throws \BadMethodCallException
     */
    public function removeAll(): void
    {
        $this->preventWriteOperation();
    }

    /**
     * @throws \BadMethodCallException
     */
    private function preventWriteOperation(): void
    {
        throw new \BadMethodCallException(
            'This is a read-only repository in which the removeAll method must not be called.',
            1537544385
        );
    }
}
