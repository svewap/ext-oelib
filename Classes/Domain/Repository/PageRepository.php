<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository;

use Doctrine\DBAL\Driver\ResultStatement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Repository for finding page records by UID.
 */
class PageRepository implements SingletonInterface
{
    /**
     * Recursively finds all pages within the given page, and returns them as a sorted list (including the provided
     * parent pages).
     *
     * @param array<array-key, positive-int> $pageUids
     * @param int<0, max> $recursion
     *
     * @return array<int, positive-int>
     *
     * @throws \InvalidArgumentException
     */
    public function findWithinParentPages(array $pageUids, int $recursion = 0): array
    {
        // @phpstan-ignore-next-line We are explicitly checking for contract violations here.
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
     * @param array<array-key, positive-int> $pageUids
     *
     * @return array<int, positive-int>
     */
    private function findDirectSubpages(array $pageUids): array
    {
        $query = $this->getQueryBuilderForTable('pages')->select('uid')->from('pages');
        $query->andWhere($query->expr()->in('pid', $pageUids));

        $subpageUids = [];
        $queryResult = $query->execute();
        if ($queryResult instanceof ResultStatement) {
            foreach ($queryResult->fetchAll() as $row) {
                /** @var positive-int $uid */
                $uid = (int)$row['uid'];
                $subpageUids[] = $uid;
            }
        }
        return $subpageUids;
    }

    /**
     * @param non-empty-string $tableName
     */
    private function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($tableName);
    }

    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
