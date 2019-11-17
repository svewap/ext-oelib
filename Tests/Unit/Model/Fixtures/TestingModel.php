<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

/**
 * Testing model.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingModel extends \Tx_Oelib_Model
{
    /*
     * normal getters and setters
     */

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
    public function setTitle($value)
    {
        $this->setAsString('title', $value);
    }

    /**
     * @return string
     */
    public function getHeader(): string
    {
        return $this->getAsString('header');
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setHeader($value)
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
     * @return \Tx_Oelib_Model_FrontEndUser
     */
    public function getOwner(): \Tx_Oelib_Model_FrontEndUser
    {
        /** @var \Tx_Oelib_Model_FrontEndUser $model */
        $model = $this->getAsModel('owner');

        return $model;
    }

    /**
     * Gets the "children" data item. This is a 1:n relation.
     *
     * @return \Tx_Oelib_List<TestingModel>
     */
    public function getChildren(): \Tx_Oelib_List
    {
        return $this->getAsList('children');
    }

    /**
     * Gets the "related_records" data item. This is an m:n relation.
     *
     * @return \Tx_Oelib_List<TestingModel>
     */
    public function getRelatedRecords(): \Tx_Oelib_List
    {
        return $this->getAsList('related_records');
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
     * @return \Tx_Oelib_List<TestingModel>
     */
    public function getBidirectional(): \Tx_Oelib_List
    {
        return $this->getAsList('bidirectional');
    }

    /**
     * Gets the "composition" data item. This is an 1:n relation.
     *
     * @return \Tx_Oelib_List<TestingChildModel>
     */
    public function getComposition(): \Tx_Oelib_List
    {
        return $this->getAsList('composition');
    }

    /**
     * Sets the "composition" data item. This is an 1:n relation.
     *
     * @param \Tx_Oelib_List $components <TestingChildModel>
     *
     * @return void
     */
    public function setComposition(\Tx_Oelib_List $components)
    {
        $this->set('composition', $components);
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
     * @return \Tx_Oelib_List<\Tx_Oelib_Model> the "composition2" data item, will be empty (but
     *                       not NULL) if this model has no composition2
     */
    public function getComposition2(): \Tx_Oelib_List
    {
        return $this->getAsList('composition2');
    }

    /**
     * Gets the "composition2" data item. This is an 1:n relation without sorting.
     *
     * @return \Tx_Oelib_List<\Tx_Oelib_Model>
     */
    public function getCompositionWithoutSorting(): \Tx_Oelib_List
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

    /*
     * proxy methods
     */

    /**
     * @param string $key
     *
     * @return bool
     */
    public function existsKey($key): bool
    {
        return parent::existsKey($key);
    }

    /**
     * @param string $key
     *
     * @return \Tx_Oelib_Model|null
     *
     * @throws \UnexpectedValueException
     */
    public function getAsModel($key)
    {
        return parent::getAsModel($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function getAsBoolean($key): bool
    {
        return parent::getAsBoolean($key);
    }

    /**
     * @param string $key
     *
     * @return int
     */
    public function getAsInteger($key): int
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
