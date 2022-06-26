<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

use OliverKlee\Oelib\DataStructures\AbstractObjectWithAccessors;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Interfaces\Identity;

/**
 * This class represents a general domain model which is capable of lazy loading (using ghosts).
 *
 * A model can have one of the following states: dead, ghost, loading, loaded, virgin.
 */
abstract class AbstractModel extends AbstractObjectWithAccessors implements Identity
{
    /**
     * @var int a status indicating that this model has neither data nor UID yet
     */
    public const STATUS_VIRGIN = 0;

    /**
     * @var int a status indicating that this model's data has not been
     *              loaded yet (lazily), but that the model already has a UID
     */
    public const STATUS_GHOST = 1;

    /**
     * @var int a status indicating that this model's data currently is being loaded
     */
    public const STATUS_LOADING = 2;

    /**
     * @var int a status indicating that this model's data has already been loaded (with or without UID)
     */
    public const STATUS_LOADED = 3;

    /**
     * @var int a status indicating that this model's data could not be retrieved from the DB
     */
    public const STATUS_DEAD = 4;

    /**
     * @var bool whether this model is read-only
     */
    protected $readOnly = false;

    /**
     * @var int this model's UID, will be 0 if this model has been created in memory
     */
    private $uid = 0;

    /**
     * data for this object (without the UID)
     *
     * @var array<string, string|int|float|bool|object|null> $data
     */
    private $data = [];

    /**
     * @var self::STATUS_*
     */
    private $loadStatus = self::STATUS_VIRGIN;

    /**
     * @var bool whether this model's initial data has changed
     */
    private $isDirty = false;

    /**
     * @var \Closure|null the callback function that fills this model with data
     */
    private $loadCallback = null;

    /**
     * Clone.
     *
     * @throws \BadMethodCallException
     */
    public function __clone()
    {
        if ($this->isReadOnly()) {
            throw new \BadMethodCallException('Read-only models cannot be cloned.', 1436453245);
        }
        if ($this->isDead()) {
            throw new \BadMethodCallException('Deleted models cannot be cloned.', 1436453107);
        }
        if ($this->isLoading()) {
            throw new \BadMethodCallException('Models cannot be cloned while they are loading.', 1436453245);
        }
        if ($this->isGhost()) {
            $this->load();
        }

        $this->resetUid();

        /** @var int|string|bool|float|Collection<AbstractModel>|AbstractModel|null $dataItem */
        foreach ($this->data as $key => $dataItem) {
            if ($dataItem instanceof Collection) {
                /** Collection $dataItem */
                if ($dataItem->isRelationOwnedByParent()) {
                    $newDataItem = new Collection();
                    $newDataItem->markAsOwnedByParent();
                    /** @var AbstractModel $childModel */
                    foreach ($dataItem as $childModel) {
                        $newDataItem->add(clone $childModel);
                    }
                } else {
                    $newDataItem = clone $dataItem;
                }
                $newDataItem->setParentModel($this);
                $this->set($key, $newDataItem);
            }
        }

        $this->markAsDirty();
    }

    /**
     * Sets the complete data for this model.
     *
     * The data which is set via this function is considered to be the initial
     * data. Fields with relations must already be filled with the constituted
     * models/lists, not just with the UIDs (unlike the format that
     * AbstractDataMapper::getLoadedTestingModel takes).
     *
     * This function should be called directly after instantiation and must only
     * be called once. Usually, this function is called on only a few occasions:
     *
     * 1. when the data mapper loads a model
     * 2. when a new model is created in some unit tests
     * 3. before a new model should be saved to the database
     *
     * @param array<string, string|int|float|bool|object|null> $data
     */
    public function setData(array $data): void
    {
        if ($this->isLoaded()) {
            throw new \BadMethodCallException('setData must only be called once per model instance.', 1331489244);
        }

        $this->resetData($data);
    }

    /**
     * Sets the complete data for this model.
     *
     * This function may be called more than once.
     *
     * @param array<string, string|int|float|bool|object|null> $data
     */
    public function resetData(array $data): void
    {
        $this->data = $data;
        if ($this->existsKey('uid')) {
            if (!$this->hasUid()) {
                $rawUid = $this->data['uid'];
                $uid = (\is_int($rawUid) || \is_string($rawUid)) ? (int)$rawUid : 0;
                $this->setUid($uid);
            }
            unset($this->data['uid']);
        }

        $this->markAsLoaded();
        if ($this->hasUid()) {
            $this->markAsClean();
        } else {
            $this->markAsDirty();
        }
    }

    /**
     * Returns the complete data for this model.
     *
     * This function may only be called by the mapper.
     *
     * @return array<string, string|int|float|bool|object|null> $data the model data, might be empty
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Marks this model as "loaded", i.e., that it has some real data.
     */
    protected function markAsLoaded(): void
    {
        $this->setLoadStatus(self::STATUS_LOADED);
    }

    /**
     * Marks this model as "dead", i.e., that retrieving its data from the DB has failed.
     */
    public function markAsDead(): void
    {
        $this->setLoadStatus(self::STATUS_DEAD);
        $this->markAsClean();
    }

    /**
     * Marks this model as loading.
     */
    private function markAsLoading(): void
    {
        $this->setLoadStatus(self::STATUS_LOADING);
    }

    /**
     * Sets this model's UID.
     *
     * This function may only be called on models that do not have a UID yet.
     *
     * If this function is called on an empty model, the model state is changed
     * to ghost.
     *
     * @param int $uid the UID to set, must be > 0
     */
    public function setUid(int $uid): void
    {
        if ($this->hasUid()) {
            throw new \BadMethodCallException('The UID of a model cannot be set a second time.', 1331489260);
        }
        if ($this->isVirgin()) {
            $this->setLoadStatus(self::STATUS_GHOST);
        }

        $this->uid = $uid;
    }

    /**
     * Resets the UID to 0, i.e., this model has no UID anymore.
     */
    private function resetUid(): void
    {
        $this->uid = 0;
    }

    /**
     * Sets the value of the data item for the key $key.
     *
     * @param string $key the key of the data item to get, must not be empty
     * @param Collection<AbstractModel>|AbstractModel|string|int|float|bool|null $value the data for the given key
     */
    protected function set(string $key, $value): void
    {
        if ($key === 'deleted') {
            throw new \InvalidArgumentException(
                '$key must not be "deleted". Please use setToDeleted() instead.',
                1331489276
            );
        }
        if ($this->isReadOnly()) {
            throw new \BadMethodCallException('set() must not be called on a read-only model.', 1331489292);
        }

        if ($this->isGhost()) {
            $this->load();
        }
        $this->data[$key] = $value;

        $this->markAsLoaded();
        $this->markAsDirty();
    }

    /**
     * Gets the value of the data item for the key $key.
     *
     * Before this function may be called, `setData()` or `set()` must have been called once.
     *
     * @param string $key the key of the data item to get, must not be empty
     *
     * @return string|int|float|bool|object|null the data for the key $key,
     *         will be an empty string if the key has not been set yet
     *
     * @throws NotFoundException if this model is dead
     */
    protected function get(string $key)
    {
        if ($key === 'uid') {
            throw new \InvalidArgumentException(
                'The UID column needs to be accessed using the getUid function.',
                1331489310
            );
        }

        $this->load();
        if ($this->isDead()) {
            throw new NotFoundException(
                'The ' . get_class($this) . ' with the UID ' . $this->getUid() .
                ' either has been deleted (or has never existed), but still is accessed.',
                1332446332
            );
        }

        if (!$this->existsKey($key)) {
            return '';
        }

        return $this->data[$key];
    }

    /**
     * Checks whether a data item with a certain key exists.
     *
     * @param string $key the key of the data item to check, must not be empty
     *
     * @return bool TRUE if a data item with the key $key exists, FALSE
     *                 otherwise
     */
    protected function existsKey(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    /**
     * Gets the value stored in under the key $key as a model.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @throws \UnexpectedValueException if there is a data item stored for the key $key that is not a model instance
     */
    protected function getAsModel(string $key): ?AbstractModel
    {
        $this->checkForNonEmptyKey($key);

        $result = $this->get($key);
        if (($result === null) || !$this->existsKey($key)) {
            return null;
        }

        if (!$result instanceof self) {
            throw new \UnexpectedValueException(
                'The data item for the key "' . $key . '" is no model instance.',
                1331489359
            );
        }

        return $result;
    }

    /**
     * Gets the value stored in under the key $key as a collection of models.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return Collection<AbstractModel> the data item for the given key
     *
     * @throws \UnexpectedValueException if there is a data item stored for the key $key that is not a collection
     *         or if that item has not been set yet
     */
    public function getAsCollection(string $key): Collection
    {
        $this->checkForNonEmptyKey($key);

        /** @var Collection<AbstractModel> $result */
        $result = $this->get($key);
        if (!$result instanceof Collection) {
            throw new \UnexpectedValueException(
                'The data item for the key "' . $key . '" is no collection.',
                1331489379
            );
        }

        return $result;
    }

    /**
     * Gets the value stored in under the key $key as a collection of models.
     *
     * @param string $key the key of the element to retrieve, must not be empty
     *
     * @return Collection<AbstractModel> the data item for the given key
     *
     * @throws \UnexpectedValueException if there is a data item stored for the key $key that is not a collection
     *         or if that item has not been set yet
     *
     * @deprecated will be removed in oelib 5.0 - use `getAsCollection` instead
     */
    public function getAsList(string $key): Collection
    {
        return $this->getAsCollection($key);
    }

    /**
     * Makes sure this model has some data by loading the data for ghost models.
     */
    private function load(): void
    {
        if ($this->isVirgin()) {
            throw new \BadMethodCallException(
                get_class($this) . '#' . $this->getUid()
                . ': Please call setData() directly after instantiation first.',
                1331489395
            );
        }

        if ($this->isGhost()) {
            if (!$this->loadCallback instanceof \Closure) {
                throw new \BadMethodCallException(
                    'Ghosts need a load callback function before their data can be accessed.',
                    1331489414
                );
            }

            $this->markAsLoading();
            $callback = $this->loadCallback;
            if (!\is_callable($callback)) {
                throw new \RuntimeException('Model load callback is not callable.', 1646325797);
            }
            $callback($this);
        }
    }

    /**
     * Gets this model's UID.
     *
     * @return int this model's UID, will be zero if this model does not have a UID yet
     */
    public function getUid(): int
    {
        return $this->uid;
    }

    /**
     * Checks whether this model has a UID.
     *
     * @return bool TRUE if this model has a non-zero UID, FALSE otherwise
     */
    public function hasUid(): bool
    {
        return $this->getUid() > 0;
    }

    /**
     * @return self::STATUS_*
     */
    protected function getLoadStatus(): int
    {
        return $this->loadStatus;
    }

    /**
     * @param self::STATUS_* $status
     */
    protected function setLoadStatus(int $status): void
    {
        $this->loadStatus = $status;
    }

    /**
     * Checks whether this is a virgin model (which has neither data nor UID).
     *
     * @return bool TRUE if this is a virgin model, FALSE otherwise
     */
    public function isVirgin(): bool
    {
        return $this->getLoadStatus() === self::STATUS_VIRGIN;
    }

    /**
     * Checks whether this model is a ghost (has a UID, but is not fully loaded
     * yet).
     *
     * @return bool TRUE if this model is a ghost, FALSE otherwise
     */
    public function isGhost(): bool
    {
        return $this->getLoadStatus() === self::STATUS_GHOST;
    }

    /**
     * Checks whether this model is currently loading.
     *
     * @return bool TRUE if this model is loading, FALSE otherwise
     */
    public function isLoading(): bool
    {
        return $this->getLoadStatus() === self::STATUS_LOADING;
    }

    /**
     * Checks whether this model is fully loaded (has data).
     *
     * @return bool TRUE if this model is fully loaded, FALSE otherwise
     */
    public function isLoaded(): bool
    {
        return $this->getLoadStatus() === self::STATUS_LOADED;
    }

    /**
     * Checks whether this model is dead (retrieving its data from the DB has
     * failed).
     *
     * @return bool TRUE if this model is dead, FALSE otherwise
     */
    public function isDead(): bool
    {
        return $this->getLoadStatus() === self::STATUS_DEAD;
    }

    /**
     * Checks whether this model is hidden.
     *
     * @return bool TRUE if this model is hidden, FALSE otherwise
     */
    public function isHidden(): bool
    {
        return $this->getAsBoolean('hidden');
    }

    /**
     * Marks this model as hidden.
     */
    public function markAsHidden(): void
    {
        $this->setAsBoolean('hidden', true);
    }

    /**
     * Marks this model as visible (= not hidden).
     */
    public function markAsVisible(): void
    {
        $this->setAsBoolean('hidden', false);
    }

    /**
     * Sets the callback function for loading this model with data.
     *
     * @param \Closure $callback the callback function for loading this model with data
     */
    public function setLoadCallback(\Closure $callback): void
    {
        $this->loadCallback = $callback;
    }

    /**
     * Checks whether this model has a callback function set for loading its data.
     */

    /**
     * Marks this model's data as clean.
     */
    public function markAsClean(): void
    {
        $this->isDirty = false;
    }

    /**
     * Marks this model's data as dirty.
     */
    public function markAsDirty(): void
    {
        $this->isDirty = true;
    }

    /**
     * Checks whether this model has been marked as dirty which means that this
     * model's data has changed compared to the initial state.
     *
     * @return bool TRUE if this model has been marked as dirty
     */
    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    /**
     * Sets the "deleted" property for the current model.
     *
     * Note: This function is intended to be called only by a data mapper.
     */
    public function setToDeleted(): void
    {
        if ($this->isLoaded()) {
            $this->data['deleted'] = true;
            $this->markAsDirty();
        } else {
            $this->markAsDead();
        }
    }

    /**
     * Checks whether this model is set to deleted.
     *
     * @return bool TRUE if this model is set to deleted, FALSE otherwise
     */
    public function isDeleted(): bool
    {
        return $this->getAsBoolean('deleted');
    }

    /**
     * Checks whether this model is read-only.
     *
     * @return bool TRUE if this model is read-only, FALSE if it is writable
     */
    public function isReadOnly(): bool
    {
        return $this->readOnly;
    }

    /**
     * @return int
     */
    public function getModificationDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('tstamp');
    }

    /**
     * Sets the modification date and time.
     */
    public function setTimestamp(): void
    {
        $this->setAsInteger('tstamp', $GLOBALS['SIM_EXEC_TIME']);
    }

    /**
     * @return int
     */
    public function getCreationDateAsUnixTimeStamp(): int
    {
        return $this->getAsInteger('crdate');
    }

    /**
     * Sets the creation date and time.
     */
    public function setCreationDate(): void
    {
        if ($this->hasUid()) {
            throw new \BadMethodCallException('Only new objects (without UID) may receive "crdate".', 1331489449);
        }

        $this->setAsInteger('crdate', $GLOBALS['SIM_EXEC_TIME']);
    }

    /**
     * Returns the page UID of this model.
     *
     * @return int the page UID of this model, will be >= 0
     */
    public function getPageUid(): int
    {
        return $this->getAsInteger('pid');
    }

    /**
     * Sets this model's page UID.
     *
     * @param int $pageUid the page to set, must be >= 0
     */
    public function setPageUid(int $pageUid): void
    {
        if ($pageUid < 0) {
            throw new \InvalidArgumentException('$pageUid must be >= 0.');
        }

        $this->setAsInteger('pid', $pageUid);
    }

    /**
     * Checks whether this model is empty.
     */
    public function isEmpty(): bool
    {
        if ($this->isGhost()) {
            $this->load();
            $this->markAsLoaded();
        }

        return $this->data === [];
    }
}
