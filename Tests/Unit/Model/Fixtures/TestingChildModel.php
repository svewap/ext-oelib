<?php

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

/**
 * This class represents a domain model for testing purposes.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingChildModel extends \Tx_Oelib_Model
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
}
