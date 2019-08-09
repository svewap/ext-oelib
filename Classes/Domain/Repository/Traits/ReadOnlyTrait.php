<?php

namespace OliverKlee\Oelib\Domain\Repository\Traits;

/**
 * This trait marks repositories as read-only.
 *
 * @deprecated Will be removed in oelib 3.0. Use ReadOnly (without the "Trait" suffix) instead.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
trait ReadOnlyTrait
{
    /**
     * Adds an object to this repository.
     *
     * @param object $object The object to add
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function add($object)
    {
        $this->preventWriteOperation();
    }

    /**
     * Removes an object from this repository.
     *
     * @param object $object The object to remove
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function remove($object)
    {
        $this->preventWriteOperation();
    }

    /**
     * Replaces an existing object with the same identifier by the given object.
     *
     * @param object $modifiedObject The modified object
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function update($modifiedObject)
    {
        $this->preventWriteOperation();
    }

    /**
     * Removes all objects of this repository as if remove() was called for all of them.
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function removeAll()
    {
        $this->preventWriteOperation();
    }

    /**
     * @return void
     *
     * @throws \BadMethodCallException
     */
    private function preventWriteOperation()
    {
        throw new \BadMethodCallException(
            'This is a read-only repository in which the removeAll method must not be called.',
            1537544385
        );
    }
}
