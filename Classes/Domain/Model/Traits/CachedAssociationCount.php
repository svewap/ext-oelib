<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

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
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
trait CachedAssociationCount
{
    /**
     * @var int[]
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
        if (array_key_exists($propertyName, $this->cachedRelationCountsCount)) {
            return $this->cachedRelationCountsCount[$propertyName];
        }

        $this->cachedRelationCountsCount[$propertyName] = $this->getUncachedRelationCount($propertyName);

        return $this->cachedRelationCountsCount[$propertyName];
    }

    /**
     * Flushes the internal relation count cache for the given property.
     *
     * @param string $propertyName the name of the relation (plural, lower camelCase)
     *
     * @return void
     */
    protected function flushRelationCountCache($propertyName)
    {
        unset($this->cachedRelationCountsCount[$propertyName]);
    }

    /**
     * Retrieves the relation count for the given property.
     *
     * This methods tries to avoid database accesses by using the relation counter cache if the relation is still
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
        if ($this->$propertyName instanceof LazyObjectStorage) {
            $reflectionClass = new \ReflectionClass(LazyObjectStorage::class);
            $reflectionProperty = $reflectionClass->getProperty('fieldValue');
            $reflectionProperty->setAccessible(true);
            $count = (int)$reflectionProperty->getValue($this->$propertyName);
        } else {
            $count = $this->$propertyName->count();
        }

        return $count;
    }
}
