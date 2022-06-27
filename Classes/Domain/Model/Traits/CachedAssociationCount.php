<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyObjectStorage;

/**
 * This trait allows accessing the count of a relation without doing any additional SQL queries.
 *
 * Usage: Add a separate count method for each storage property that needs to be counted, e.g. "children":
 * <code>
 * public function getChildrenCount(): int
 * {
 *     return $this->getCachedRelationCount('children');
 * }
 * </code>
 *
 * @mixin AbstractEntity
 */
trait CachedAssociationCount
{
    /**
     * @var array<string, int>
     */
    protected $cachedRelationCountsCount = [];

    /**
     * Retrieves and caches the relation count for the given property.
     *
     * @param string $propertyName the name of the relation (plural, lower camelCase)
     *
     * @return int
     */
    protected function getCachedRelationCount(string $propertyName): int
    {
        if (\array_key_exists($propertyName, $this->cachedRelationCountsCount)) {
            return $this->cachedRelationCountsCount[$propertyName];
        }

        $this->cachedRelationCountsCount[$propertyName] = $this->getUncachedRelationCount($propertyName);

        return $this->cachedRelationCountsCount[$propertyName];
    }

    /**
     * Flushes the internal relation count cache for the given property.
     *
     * @param string $propertyName the name of the relation (plural, lower camelCase)
     */
    protected function flushRelationCountCache(string $propertyName): void
    {
        unset($this->cachedRelationCountsCount[$propertyName]);
    }

    /**
     * Retrieves the relation count for the given property.
     *
     * This method tries to avoid database accesses by using the relation counter cache if the relation is still
     * a LazyObjectStorage. Otherwise, the normal COUNT query will be performed as a fallback.
     *
     * This method does not cache its results.
     *
     * @param string $propertyName the name of the relation (plural, lower camelCase)
     *
     * @return int
     *
     * @throws \ReflectionException
     */
    protected function getUncachedRelationCount(string $propertyName): int
    {
        // @phpstan-ignore-next-line This variable property access is okay.
        $propertyValue = $this->{$propertyName};
        if ($propertyValue instanceof LazyObjectStorage) {
            $reflectionProperty = (new \ReflectionClass(LazyObjectStorage::class))->getProperty('fieldValue');
            $reflectionProperty->setAccessible(true);
            // @phpstan-ignore-next-line This variable property access is okay.
            $count = (int)$reflectionProperty->getValue($propertyValue);
        } else {
            // @phpstan-ignore-next-line This variable property access is okay.
            $count = $propertyValue->count();
        }

        return $count;
    }
}
