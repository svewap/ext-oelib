<?php

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
    public function getTitle()
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
     * Marks this model as read-only.
     *
     * @return void
     */
    public function markAsReadOnly()
    {
        $this->readOnly = true;
    }

    /*
     * proxy methods
     */

    /**
     * @param string $key
     *
     * @return bool
     */
    public function existsKey($key)
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
     * @param int $status
     *
     * @return void
     */
    public function setLoadStatus($status)
    {
        parent::setLoadStatus($status);
    }
}
