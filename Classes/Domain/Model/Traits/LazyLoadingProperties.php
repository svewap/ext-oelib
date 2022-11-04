<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * This provides a method for loading lazy n:1 and 1:1 properties.
 *
 * @deprecated Will be removed in oelib 6.0.
 *
 * @mixin AbstractEntity
 */
trait LazyLoadingProperties
{
    private function loadLazyProperty(string $propertyName): void
    {
        // @phpstan-ignore-next-line This variable property access is okay.
        $propertyValue = $this->{$propertyName};
        if ($propertyValue instanceof LazyLoadingProxy) {
            // @phpstan-ignore-next-line This variable property access is okay.
            $this->{$propertyName} = $propertyValue->_loadRealInstance();
        }
    }
}
