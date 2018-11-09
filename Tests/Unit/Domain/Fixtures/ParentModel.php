<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Fixtures;

use OliverKlee\Oelib\Domain\Model\Traits\CachedAssociationCount;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * Testing model for 1:n associations.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ParentModel extends AbstractEntity
{
    use CachedAssociationCount;

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\ParentModel>
     * @lazy
     */
    protected $children = null;

    public function __construct()
    {
        $this->children = new ObjectStorage();
    }

    /**
     * @return ObjectStorage
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ObjectStorage $children
     *
     * @return void
     */
    public function setChildren(ObjectStorage $children)
    {
        $this->children = $children;
    }

    /**
     * @return int
     */
    public function getChildrenCount()
    {
        return $this->getCachedRelationCount('children');
    }
}
