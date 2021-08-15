<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\DataStructures;

use OliverKlee\Oelib\Interfaces\Sortable;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a list of models.
 */
class Collection extends \SplObjectStorage
{
    /**
     * @var array<int, int> the UIDs in the list using the UIDs as both the keys and values
     */
    private $uids = [];

    /**
     * The model this List belongs to.
     *
     * This is used for modeling relations and will remain NULL in any other
     * context.
     *
     * @var AbstractModel
     */
    private $parentModel = null;

    /**
     * whether the parent model is the owner (which is the case for IRRE relations).
     *
     * @var bool
     */
    private $parentIsOwner = false;

    /**
     * whether there is at least one item without a UID
     *
     * @var bool
     */
    private $hasItemWithoutUid = false;

    /**
     * Adds a model to this list (as last element) if it is not already in the
     * list.
     *
     * The model to add need not necessarily have a UID.
     *
     * @param AbstractModel $model the model to add, need not have a UID
     *
     * @return void
     *
     * @throws \UnexpectedValueException
     */
    public function add(AbstractModel $model)
    {
        $this->attach($model);

        if ($model->hasUid()) {
            $uid = $model->getUid();
            // This should never happen, but still seems to happen sometimes.
            // This exception should help debugging the problem.
            if (!\is_array($this->uids)) {
                throw new \UnexpectedValueException(
                    '$this->uids was expected to be an array, but actually is: ' . \gettype($this->uids),
                    1440104082
                );
            }

            $this->uids[$uid] = $uid;
        } else {
            $this->hasItemWithoutUid = true;
        }

        // Initializes the Iterator.
        if ($this->count() === 1) {
            $this->rewind();
        }

        $this->markAsDirty();
    }

    /**
     * Checks whether this list is empty.
     *
     * @return bool TRUE if this list is empty, FALSE otherwise
     */
    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Returns the first item.
     *
     * Note: This method rewinds the iterator.
     *
     * @return AbstractModel|null
     */
    public function first()
    {
        $this->rewind();
        /** @var AbstractModel|null $current */
        $current = $this->current();

        return $current;
    }

    /**
     * Returns a comma-separted list of unique UIDs of the current items,
     * ordered by first insertion.
     *
     * @return string comma-separated list of UIDs, will be empty if the list is
     *                empty or no item has a UID
     */
    public function getUids(): string
    {
        $this->checkUidCache();
        return implode(',', $this->uids);
    }

    /**
     * Checks whether a model with a certain UID exists in this list
     *
     * @param int $uid UID to test, must be > 0
     *
     * @return bool TRUE if a model with the UID $uid exists in this list, FALSE otherwise
     */
    public function hasUid(int $uid): bool
    {
        $this->checkUidCache();
        return isset($this->uids[$uid]);
    }

    /**
     * Checks whether the UID list cache needs to be rebuild and does so if
     * necessary.
     *
     * @return void
     */
    private function checkUidCache()
    {
        if ($this->hasItemWithoutUid) {
            $this->rebuildUidCache();
        }
    }

    /**
     * Rebuilds the UID cache.
     *
     * @return void
     */
    private function rebuildUidCache()
    {
        $this->hasItemWithoutUid = false;

        /** @var AbstractModel $item */
        foreach ($this as $item) {
            if ($item->hasUid()) {
                $uid = $item->getUid();
                $this->uids[$uid] = $uid;
            } else {
                $this->hasItemWithoutUid = true;
            }
        }
    }

    /**
     * Sorts this list by using the given callback function.
     *
     * The callback function, must take 2 parameters and return -1, 0 or 1.
     * The return value -1 means that the first parameter is sorted before the
     * second one, 1 means that the second parameter is sorted before the first
     * one and 0 means the parameters stay in order.
     *
     * @param mixed $callbackFunction a callback function to use with the models stored in the list, must not be empty
     *
     * @return void
     */
    public function sort($callbackFunction)
    {
        $items = iterator_to_array($this, false);
        usort($items, $callbackFunction);

        /** @var AbstractModel $item */
        foreach ($items as $item) {
            $this->detach($item);
            $this->attach($item);
        }

        $this->markAsDirty();
    }

    /**
     * Appends the contents of $list to this list.
     *
     * Note: Since Collection extends \SplObjectStorage, this method is in most
     * cases an synonym to appendUnique() as \SplObjectStorage makes sure that
     * no object is added more than once to it.
     *
     * @param Collection<AbstractModel> $list the list to append, may be empty
     *
     * @return void
     */
    public function append(Collection $list)
    {
        /** @var AbstractModel $item */
        foreach ($list as $item) {
            $this->add($item);
        }
    }

    /**
     * Drops the current element from the list and sets the pointer to the
     * next element.
     *
     * If the pointer does not point to a valid element, this function is a
     * no-op.
     *
     * @return void
     */
    public function purgeCurrent()
    {
        if (!$this->valid()) {
            return;
        }

        if ($this->current()->hasUid()) {
            $uid = $this->current()->getUid();
            if (isset($this->uids[$uid])) {
                unset($this->uids[$uid]);
            }
        }

        $this->detach($this->current());

        $this->markAsDirty();
    }

    /**
     * Returns the model this list belongs to.
     *
     * @internal
     *
     * @return AbstractModel|null
     */
    public function getParentModel()
    {
        return $this->parentModel;
    }

    /**
     * Sets the model this list belongs to.
     *
     * @internal
     *
     * @param AbstractModel $model the model this list belongs to
     *
     * @return void
     */
    public function setParentModel(AbstractModel $model)
    {
        $this->parentModel = $model;
    }

    /**
     * Checks whether this relation is owner by the parent model.
     *
     * @internal
     *
     * @return bool
     */
    public function isRelationOwnedByParent(): bool
    {
        return $this->parentIsOwner;
    }

    /**
     * Marks this relation as owned by the parent model.
     *
     * @internal
     *
     * @return void
     */
    public function markAsOwnedByParent()
    {
        $this->parentIsOwner = true;
    }

    /**
     * Marks the parent model as dirty.
     *
     * @internal
     *
     * @return void
     */
    protected function markAsDirty()
    {
        if ($this->parentModel instanceof AbstractModel) {
            $this->parentModel->markAsDirty();
        }
    }

    /**
     * Sorts this list item in ascending order by their sorting.
     *
     * This function may only be used if all items in this list implement the
     * SortableInterface interface.
     *
     * @internal
     *
     * @return void
     */
    public function sortBySorting()
    {
        $this->sort([$this, 'compareSortings']);
    }

    /**
     * Internal callback function for sorting two sortable objects.
     *
     * This function is not intended to be used from the outside.
     *
     * @param Sortable $object1 the first object
     * @param Sortable $object2 the second object
     *
     * @return int a negative number if $model1 should be before $model2,
     *                 a positive number if $model1 should be after $model2,
     *                 zero if both are equal for sorting
     */
    public function compareSortings(
        Sortable $object1,
        Sortable $object2
    ): int {
        return $object1->getSorting() - $object2->getSorting();
    }

    /**
     * Returns $length elements from this list starting at position $start.
     *
     * If $start after this list's end, this function will return an empty list.
     *
     * If this list's end lies within the requested range, all elements up to
     * the list's end will be returned.
     *
     * @param int $start the zero-based start position, must be >= 0
     * @param int $length the number of elements to return, must be >= 0
     *
     * @return Collection<AbstractModel>
     *         the selected elements starting at $start
     */
    public function inRange(int $start, int $length): Collection
    {
        if ($start < 0) {
            throw new \InvalidArgumentException('$start must be >= 0.');
        }
        if ($length < 0) {
            throw new \InvalidArgumentException('$length must be >= 0.');
        }

        /** @var Collection<AbstractModel> $result */
        $result = new self();

        $lastPosition = $start + $length - 1;
        $currentIndex = 0;
        /** @var AbstractModel $item */
        foreach ($this as $item) {
            if ($currentIndex > $lastPosition) {
                break;
            }
            if ($currentIndex >= $start) {
                $result->add($item);
            }
            $currentIndex++;
        }

        return $result;
    }

    /**
     * Returns the model at position $position.
     *
     * @param int $position
     *        the zero-based position of the model to retrieve, must be >= 0
     *
     * @return AbstractModel|null
     */
    public function at(int $position)
    {
        return $this->inRange($position, 1)->first();
    }

    /**
     * Returns the elements of this list in an array.
     *
     * @return AbstractModel[]
     *         the elements of this list, might be empty
     */
    public function toArray(): array
    {
        $elements = [];
        /** @var AbstractModel $model */
        foreach ($this as $model) {
            $elements[] = $model;
        }

        return $elements;
    }
}
