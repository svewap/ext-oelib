<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

use OliverKlee\Oelib\Interfaces\Sortable;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a domain model for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingChildModel extends AbstractModel implements Sortable
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
     * @return TestingModel|null
     */
    public function getParent()
    {
        /** @var TestingModel|null $model */
        $model = $this->getAsModel('parent');

        return $model;
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
        /** @var TestingModel|null $model */
        $model = $this->getAsModel('tx_oelib_parent2');

        return $model;
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
    public function getSorting(): int
    {
        return $this->getAsInteger('sorting');
    }

    /**
     * @param int $sorting
     *
     * @return void
     */
    public function setSorting(int $sorting)
    {
        $this->setAsInteger('sorting', $sorting);
    }
}
