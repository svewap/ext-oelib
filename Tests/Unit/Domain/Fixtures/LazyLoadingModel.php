<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Fixtures;

use OliverKlee\Oelib\Domain\Model\Traits\LazyLoadingProperties;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * Testing model for 1azily-loaded properties.
 */
class LazyLoadingModel extends AbstractEntity
{
    use LazyLoadingProperties;

    /**
     * @var EmptyModel|LazyLoadingProxy
     */
    protected $lazyProperty = null;

    /**
     * @return EmptyModel
     */
    public function getLazyProperty(): EmptyModel
    {
        $this->loadLazyProperty('lazyProperty');

        return $this->lazyProperty;
    }

    /**
     * @param EmptyModel|LazyLoadingProxy $property
     *
     * @return void
     */
    public function setLazyProperty($property)
    {
        $this->lazyProperty = $property;
    }
}
