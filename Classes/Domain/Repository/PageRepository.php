<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository;

use Doctrine\DBAL\Connection;
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
        $result = $this->cleanUids($pageUids);
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
     * Filters and int-casts the given UIDs and returns only positive integers, discarding the rest.
     *
     * @param array <array-key, int|string> $uids
     *
     * @return array<int, positive-int> sorted, filtered UIDs
     */
    private function cleanUids(array $uids): array
    {
        $cleanUids = [];
        foreach ($uids as $uid) {
            $intUid = (int)$uid;
            if ($intUid > 0) {
                $cleanUids[] = $intUid;
            }
        }
        \sort($cleanUids, SORT_NUMERIC);

        return $cleanUids;
    }

    /**
     * @param array<int, positive-int> $pageUids
     *
     * @return array<int, positive-int>
     */
    private function findDirectSubpages(array $pageUids): array
    {
        $queryBuilder = $this->getQueryBuilderForTable('pages');
        $query = $queryBuilder->select('uid')->from('pages');
        $query->andWhere(
            $query->expr()->in('pid', $queryBuilder->createNamedParameter($pageUids, Connection::PARAM_INT_ARRAY))
        );

        $subpageUids = [];
        $queryResult = $query->execute();
        if ($queryResult instanceof ResultStatement) {
            if (\method_exists($queryResult, 'fetchAllAssociative')) {
                foreach ($queryResult->fetchAllAssociative() as $row) {
                    /** @var positive-int $uid */
                    $uid = (int)$row['uid'];
                    $subpageUids[] = $uid;
                }
            } else {
                foreach ($queryResult->fetchAll() as $row) {
                    /** @var positive-int $uid */
                    $uid = (int)$row['uid'];
                    $subpageUids[] = $uid;
                }
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
