<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for pages.
 */
class PageRepository implements SingletonInterface
{
    /**
     * Recursively finds all pages within the given page, and returns them as a sorted list (including the provided
     * parent pages).
     *
     * @param int[] $pageUids
     * @param int $recursion
     *
     * @return int[]
     *
     * @throws \InvalidArgumentException
     */
    public function findWithinParentPages(array $pageUids, int $recursion = 0): array
    {
        if ($recursion < 0) {
            throw new \InvalidArgumentException('$recursion must be >= 0, but actually is: ' . $recursion, 1608389744);
        }
        $result = $pageUids;
        \sort($result, SORT_NUMERIC);
        if ($result === [] || $recursion === 0) {
            return $result;
        }

        $result = \array_merge(
            $result,
            $this->findWithinParentPages($this->findDirectSubpages($result), $recursion - 1)
        );
        \sort($result, SORT_NUMERIC);

        return $result;
    }

    /**
     * @param int[] $pageUids
     *
     * @return int[]
     */
    private function findDirectSubpages(array $pageUids): array
    {
        $query = $this->getQueryBuilderForTable('pages')->select('uid')->from('pages');
        $query->andWhere($query->expr()->in('pid', $pageUids));

        /** @var int[] $subpageUids */
        $subpageUids = [];
        foreach ($query->execute()->fetchAll() as $row) {
            $subpageUids[] = (int)$row['uid'];
        }
        return $subpageUids;
    }

    private function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($tableName);
    }

    private function getConnectionPool(): ConnectionPool
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $pool;
    }
}
