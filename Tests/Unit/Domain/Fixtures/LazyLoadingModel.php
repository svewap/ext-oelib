<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Fixtures;

use OliverKlee\Oelib\Domain\Model\Traits\LazyLoadingProperties;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * Testing model for 1azily-loaded properties.
 */
final class LazyLoadingModel extends AbstractEntity
{
    use LazyLoadingProperties;

    /**
     * @var EmptyModel
     * @phpstan-var EmptyModel|LazyLoadingProxy
     */
    protected $lazyProperty = null;

    public function getLazyProperty(): EmptyModel
    {
        $this->loadLazyProperty('lazyProperty');
        /** @var EmptyModel $property */
        $property = $this->lazyProperty;

        return $property;
    }

    /**
     * @param EmptyModel|LazyLoadingProxy $property
     */
    public function setLazyProperty($property): void
    {
        $this->lazyProperty = $property;
    }
}
