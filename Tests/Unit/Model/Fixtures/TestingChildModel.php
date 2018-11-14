<?php

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

/**
 * This class represents a domain model for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingChildModel extends \Tx_Oelib_Model implements \Tx_Oelib_Interface_Sortable
{
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
     * @return TestingModel|null
     */
    public function getParent()
    {
        return $this->getAsModel('parent');
    }

    /**
     * @param TestingModel $parent
     *
     * @return void
     */
    public function setParent(TestingModel $parent)
    {
        $this->set('parent', $parent);
    }

    /**
     * Gets the "tx_oelib_parent2" data item.
     *
     * @return TestingModel|null
     */
    public function getParent2()
    {
        return $this->getAsModel('tx_oelib_parent2');
    }

    /**
     * Sets the "tx_oelib_parent2" data item.
     *
     * @param TestingModel $parent
     *
     * @return void
     */
    public function setParent2(TestingModel $parent)
    {
        $this->set('tx_oelib_parent2', $parent);
    }

    /**
     * Sets the dummy column to true.
     *
     * @return void
     */
    public function markAsDummyModel()
    {
        $this->set('is_dummy_record', true);
    }

    /**
     * @return int
     */
    public function getSorting()
    {
        return $this->getAsInteger('sorting');
    }

    /**
     * @param int $sorting
     *
     * @return void
     */
    public function setSorting($sorting)
    {
        $this->setAsInteger('sorting', $sorting);
    }
}
