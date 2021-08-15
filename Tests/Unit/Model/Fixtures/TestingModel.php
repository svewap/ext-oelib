<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * Testing model.
 */
class TestingModel extends AbstractModel
{
    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setTitle(string $value)
    {
        $this->setAsString('title', $value);
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setHeader(string $value)
    {
        $this->setAsString('header', $value);
    }

    /**
     * Sets the deleted property via set().
     *
     * Note: This function is expected to fail.
     *
     * @return void
     */
    public function setDeletedPropertyUsingSet()
    {
        $this->setAsBoolean('deleted', true);
    }

    /**
     * Sets the dummy column to TRUE.
     *
     * @return void
     */
    public function markAsDummyModel()
    {
        $this->set('is_dummy_record', true);
    }

    /**
     * Marks this model as read-only.
     *
     * @return void
     */
    public function markAsReadOnly()
    {
        $this->readOnly = true;
    }

    /**
     * Gets the "friend" data item. This is an n:1 relation.
     *
     * @return TestingModel|null
     */
    public function getFriend()
    {
        /** @var TestingModel|null $model */
        $model = $this->getAsModel('friend');

        return $model;
    }

    /**
     * Sets the "friend" data item. This is an n:1 relation.
     *
     * @param TestingModel $friend
     *
     * @return void
     */
    public function setFriend(TestingModel $friend)
    {
        $this->set('friend', $friend);
    }

    /**
     * Gets the "owner" data item. This is an n:1 relation.
     *
     * @return FrontEndUser
     */
    public function getOwner(): FrontEndUser
    {
        /** @var FrontEndUser $model */
        $model = $this->getAsModel('owner');

        return $model;
    }

    /**
     * Gets the "children" data item. This is a 1:n relation.
     *
     * @return Collection<TestingModel>
     */
    public function getChildren(): Collection
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->getAsList('children');

        return $models;
    }

    /**
     * Gets the "related_records" data item. This is an m:n relation.
     *
     * @return Collection<TestingModel>
     */
    public function getRelatedRecords(): Collection
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->getAsList('related_records');

        return $models;
    }

    /**
     * Adds a related record.
     *
     * @param TestingModel $record
     *
     * @return void
     */
    public function addRelatedRecord(TestingModel $record)
    {
        $this->getRelatedRecords()->add($record);
    }

    /**
     * Gets the "bidirectional" data item. This is an m:n relation.
     *
     * @return Collection<TestingModel>
     */
    public function getBidirectional(): Collection
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->getAsList('bidirectional');

        return $models;
    }

    /**
     * Gets the "composition" data item. This is an 1:n relation.
     *
     * @return Collection<TestingChildModel>
     */
    public function getComposition(): Collection
    {
        /** @var Collection<TestingChildModel> $models */
        $models = $this->getAsList('composition');

        return $models;
    }

    /**
     * Adds $model to the "composition" relation.
     *
     * @param TestingChildModel $model
     *
     * @return void
     */
    public function addCompositionRecord(TestingChildModel $model)
    {
        $this->getComposition()->add($model);
    }

    /**
     * Gets the "composition2" data item. This is an 1:n relation.
     *
     * @return Collection<AbstractModel> the "composition2" data item, will be empty (but
     *                       not NULL) if this model has no composition2
     */
    public function getComposition2(): Collection
    {
        return $this->getAsList('composition2');
    }

    /**
     * Gets the "composition2" data item. This is an 1:n relation without sorting.
     *
     * @return Collection<AbstractModel>
     */
    public function getCompositionWithoutSorting(): Collection
    {
        return $this->getAsList('composition_without_sorting');
    }

    /**
     * Gets the data from the "float_data" column.
     *
     * @return float the data from the "float_data" column
     */
    public function getFloatFromFloatData(): float
    {
        return $this->getAsFloat('float_data');
    }

    /**
     * Gets the data from the "decimal_data" column.
     *
     * @return float the data from the "decimal_data" column
     */
    public function getFloatFromDecimalData(): float
    {
        return $this->getAsFloat('decimal_data');
    }

    /**
     * Gets the data from the "string_data" column.
     *
     * @return float the data from the "string_data" column
     */
    public function getFloatFromStringData(): float
    {
        return $this->getAsFloat('string_data');
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function existsKey(string $key): bool
    {
        return parent::existsKey($key);
    }

    /**
     * @param string $key
     *
     * @return AbstractModel|null
     *
     * @throws \UnexpectedValueException
     */
    public function getAsModel(string $key)
    {
        return parent::getAsModel($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function getAsBoolean(string $key): bool
    {
        return parent::getAsBoolean($key);
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function getAsInteger(string $key): int
    {
        return parent::getAsInteger($key);
    }

    /**
     * @param int $status
     *
     * @return void
     */
    public function setLoadStatus(int $status)
    {
        parent::setLoadStatus($status);
    }
}
