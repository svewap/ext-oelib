<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Database;

use OliverKlee\Oelib\Exception\DatabaseException;
use OliverKlee\Oelib\Exception\EmptyQueryResultException;
use OliverKlee\Oelib\System\Typo3Version;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Page\PageRepository;

/**
 * This class provides some static database-related functions.
 *
 * @deprecated will be removed in oelib 4.0; use the ConnectionPool instead for TYPO3 >= 8.4
 */
class DatabaseService
{
    /**
     * page object which we will use to call enableFields on
     *
     * @var PageRepository|null
     */
    private static $pageForEnableFields = null;

    /**
     * cached results for the enableFields function
     *
     * @var array<string, array<int, array<string, string>>>
     */
    private static $enableFieldsCache = [];

    /**
     * @var array<string, array> cache for the results of existsTable with the table names
     *            as keys and the table SHOW STATUS information (in an array)
     *            as values
     */
    private static $tableNameCache = [];

    /**
     * cache for the results of hasTableColumn with the column names as keys and
     * the SHOW COLUMNS field information (in an array) as values
     *
     * @var array<string, array<string>>
     */
    private static $tableColumnCache = [];

    /**
     * Wrapper function for PageRepository::enableFields() since it is no
     * longer accessible statically.
     *
     * Returns a part of a WHERE clause which will filter out records with
     * start/end times or deleted/hidden/fe_groups fields set to values that
     * should de-select them according to the current time, preview settings or
     * user login.
     * Is using the $TCA arrays "ctrl" part where the key "enablefields"
     * determines for each table which of these features applies to that table.
     *
     * @param string $table
     *        table name found in the $TCA array
     * @param int $showHidden
     *        If $showHidden is set (0/1), any hidden-fields in records are ignored.
     *        NOTICE: If you call this function, consider what to do with the show_hidden parameter.
     *        Maybe it should be set? See ContentObjectRenderer->enableFields
     *        where it's implemented correctly.
     *
     * @return string the WHERE clause starting like " AND ...=... AND ...=..."
     */
    public static function enableFields(string $table, int $showHidden = -1): string
    {
        if (!in_array($showHidden, [-1, 0, 1], true)) {
            throw new \InvalidArgumentException(
                '$showHidden may only be -1, 0 or 1, but actually is ' . $showHidden,
                1331319963
            );
        }

        // maps $showHidden (-1..1) to (0..2) which ensures valid array keys
        $showHiddenKey = $showHidden + 1;
        if ($showHidden > 0) {
            $enrichedIgnores = ['starttime' => true, 'endtime' => true, 'fe_group' => true];
        } else {
            $enrichedIgnores = [];
        }

        /** @var string $ignoresKey */
        $ignoresKey = \json_encode($enrichedIgnores);
        if (!isset(self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey])) {
            self::retrievePageForEnableFields();
            self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey]
                = self::$pageForEnableFields->enableFields($table, $showHidden, $enrichedIgnores);
        }

        return self::$enableFieldsCache[$table][$showHiddenKey][$ignoresKey];
    }

    /**
     * Makes sure that self::$pageForEnableFields is a page object.
     *
     * @return void
     */
    private static function retrievePageForEnableFields()
    {
        if (self::$pageForEnableFields instanceof PageRepository) {
            return;
        }

        $controller = self::getFrontEndController();
        if ($controller instanceof TypoScriptFrontendController && $controller->sys_page instanceof PageRepository) {
            self::$pageForEnableFields = $controller->sys_page;
        } else {
            self::$pageForEnableFields = GeneralUtility::makeInstance(PageRepository::class);
        }
    }

    /**
     * Recursively creates a comma-separated list of subpage UIDs from
     * a list of pages. The result also includes the original pages.
     * The maximum level of recursion can be limited:
     * 0 = no recursion (the default value, will return $startPages),
     * 1 = only direct child pages,
     * ...,
     * 250 = all descendants for all sane cases
     *
     * Note: The returned page list will _not_ be sorted.
     *
     * @deprecated will be removed in oelib 4.0. Use PageRepository::findWithinParentPages instead.
     *
     * @param string|int $concatenatedStartPages
     *        comma-separated list of page UIDs to start from, must only contain numbers and commas, may be empty
     * @param int $recursionDepth maximum depth of recursion, must be >= 0
     *
     * @return string comma-separated list of subpage UIDs including the
     *                UIDs provided in $startPages, will be empty if
     *                $startPages is empty
     *
     * @throws \InvalidArgumentException
     */
    public static function createRecursivePageList($concatenatedStartPages, int $recursionDepth = 0): string
    {
        if ($recursionDepth < 0) {
            throw new \InvalidArgumentException('$recursionDepth must be >= 0.', 1331319974);
        }

        $trimmedStartPages = \trim((string)$concatenatedStartPages);
        if ($recursionDepth === 0) {
            return $trimmedStartPages;
        }
        if ($trimmedStartPages === '') {
            return '';
        }

        $query = self::getQueryBuilderForTable('pages')->select('uid')->from('pages');
        $query->andWhere($query->expr()->in('pid', GeneralUtility::intExplode(',', $trimmedStartPages)));

        $subPages = [];
        foreach ($query->execute()->fetchAll() as $row) {
            $subPages[] = (int)$row['uid'];
        }

        if (!empty($subPages)) {
            $result = $trimmedStartPages . ',' .
                self::createRecursivePageList(\implode(',', $subPages), $recursionDepth - 1);
        } else {
            $result = $trimmedStartPages;
        }

        return $result;
    }

    // Wrappers for common queries

    /**
     * Executes a DELETE query.
     *
     * @param string $tableName
     *        the name of the table from which to delete, must not be empty
     * @param string $whereClause
     *        the WHERE clause to select the records, may be empty
     *
     * @return int the number of affected rows, might be 0
     *
     * @throws \InvalidArgumentException
     */
    public static function delete(string $tableName, string $whereClause): int
    {
        if ($tableName === '') {
            throw new \InvalidArgumentException('The table name must not be empty.', 1331488193);
        }

        $connection = self::getConnectionForTable($tableName);
        $sql = 'DELETE FROM ' . $connection->quoteIdentifier($tableName);
        $sql .= $whereClause !== '' ? ' WHERE ' . $whereClause : '';

        return $connection->query($sql)->rowCount();
    }

    /**
     * Executes an UPDATE query.
     *
     * @param string $table
     *        the name of the table to change, must not be empty
     * @param string $whereClause
     *        the WHERE clause to select the records, may be empty
     * @param array $data
     *        key/value pairs of the fields to change, may be empty
     *
     * @return int the number of affected rows, might be 0
     *
     * @throws \InvalidArgumentException
     */
    public static function update(string $table, string $whereClause, array $data): int
    {
        if ($table === '') {
            throw new \InvalidArgumentException('The table name must not be empty.', 1331488204);
        }

        $set = [];
        $paramValues = [];
        foreach (self::normalizeDatabaseRow($data) as $columnName => $value) {
            $set[] = $columnName . ' = ?';
            $paramValues[] = $value;
        }

        $connection = self::getConnectionForTable($table);
        $sql = 'UPDATE ' . $connection->quoteIdentifier($table) . ' SET ' . \implode(', ', $set);
        $sql .= $whereClause !== '' ? ' WHERE ' . $whereClause : '';

        return $connection->executeUpdate($sql, $paramValues);
    }

    /**
     * Executes an INSERT query for a single record.
     *
     * For TYPO3 < 9LTS, the insert ID will also be available in $GLOBALS['TYPO3_DB']->sql_insert_id.
     *
     * @param string $tableName
     *        the name of the table in which the record should be created, must not be empty
     * @param array $recordData
     *        key/value pairs of the record to insert, must not be empty
     *
     * @return int the UID of the created record, will be 0 if the table has no UID column
     *
     * @throws \InvalidArgumentException
     * @throws DatabaseException if an error has occurred
     */
    public static function insert(string $tableName, array $recordData): int
    {
        if ($tableName === '') {
            throw new \InvalidArgumentException('The table name must not be empty.', 1331488220);
        }
        if (empty($recordData)) {
            throw new \InvalidArgumentException('$recordData must not be empty.', 1331488230);
        }

        if (Typo3Version::isAtLeast(9)) {
            self::getConnectionForTable($tableName)->insert($tableName, self::normalizeDatabaseRow($recordData));
            $uid = (int)self::getConnectionForTable($tableName)->lastInsertId($tableName);
        } else {
            $connection = self::getDatabaseConnection();
            // @phpstan-ignore-next-line We run the PHPStan checks with TYPO3 9LTS, and this code is for 8 only.
            $dbResult = $connection->exec_INSERTquery($tableName, $recordData);
            if ($dbResult === false) {
                throw new DatabaseException('Database error.', 1573836507);
            }
            // @phpstan-ignore-next-line We run the PHPStan checks with TYPO3 9LTS, and this code is for 8 only.
            $uid = $connection->sql_insert_id();
        }

        return $uid;
    }

    /**
     * Executes a SELECT query.
     *
     * @param string $fields list of fields to select, may be "*", must not be empty
     * @param string $tableNames comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause WHERE clause, may be empty
     * @param string $groupBy GROUP BY field(s), may be empty
     * @param string $orderBy ORDER BY field(s), may be empty
     * @param string|int $limit LIMIT value ([begin,]max), may be empty
     *
     * @return \mysqli_result MySQLi result object
     *
     * @throws \InvalidArgumentException
     * @throws DatabaseException if an error has occurred
     *
     * @deprecated This method will not work in TYPO3 >= 9LTS.
     */
    public static function select(
        string $fields,
        string $tableNames,
        string $whereClause = '',
        string $groupBy = '',
        string $orderBy = '',
        $limit = ''
    ): \mysqli_result {
        if ($tableNames === '') {
            throw new \InvalidArgumentException('The table names must not be empty.', 1331488261);
        }
        if ($fields === '') {
            throw new \InvalidArgumentException('$fields must not be empty.', 1331488270);
        }

        // @phpstan-ignore-next-line We run the PHPStan checks with TYPO3 9LTS, and this code is for 8 only.
        $dbResult = self::getDatabaseConnection()->exec_SELECTquery(
            $fields,
            $tableNames,
            $whereClause,
            $groupBy,
            $orderBy,
            $limit
        );
        if (!$dbResult) {
            throw new DatabaseException('Database error.', 1573836521);
        }

        return $dbResult;
    }

    /**
     * Executes a SELECT query and returns the single result row as an
     * associative array.
     *
     * If there is more than one matching record, only one will be returned.
     *
     * @param string $fields list of fields to select, may be "*", must not be empty
     * @param string $tableNames
     *        comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause WHERE clause, may be empty
     * @param string $groupBy GROUP BY field(s), may be empty
     * @param string $orderBy ORDER BY field(s), may be empty
     *
     * @return array<string, string> the single result row, will not be empty
     *
     * @throws EmptyQueryResultException if there is no matching record
     */
    public static function selectSingle(
        string $fields,
        string $tableNames,
        string $whereClause = '',
        string $groupBy = '',
        string $orderBy = ''
    ): array {
        $result = self::selectMultiple($fields, $tableNames, $whereClause, $groupBy, $orderBy, 1);
        if (empty($result)) {
            throw new EmptyQueryResultException('Database error.', 1573836525);
        }

        return $result[0];
    }

    /**
     * Executes a SELECT query and returns the result rows as a two-dimensional associative array.
     *
     * @param string $fields list of fields to select, may be "*", must not be empty
     * @param string $tables comma-separated list of tables from which to select, must not be empty
     * @param string $where WHERE clause, may be empty
     * @param string $groupBy GROUP BY field(s), may be empty
     * @param string $orderBy ORDER BY field(s), may be empty
     * @param string|int $limit LIMIT value ([begin,]max), may be empty
     *
     * @return array<int, array<string, string>> the query result rows, will be empty if there are no matching records
     *
     * @throws \InvalidArgumentException
     */
    public static function selectMultiple(
        string $fields,
        string $tables,
        string $where = '',
        string $groupBy = '',
        string $orderBy = '',
        $limit = ''
    ): array {
        if ($tables === '') {
            throw new \InvalidArgumentException('The table names must not be empty.', 1573061293);
        }
        if ($fields === '') {
            throw new \InvalidArgumentException('$fields must not be empty.', 1573061298);
        }

        $sql = 'SELECT ' . $fields . ' FROM ' . $tables;
        $sql .= $where !== '' ? ' WHERE ' . $where : '';
        $sql .= $groupBy !== '' ? ' GROUP BY ' . $groupBy : '';
        $sql .= $orderBy !== '' ? ' ORDER BY ' . $orderBy : '';
        $sql .= (string)$limit !== '' ? ' LIMIT ' . $limit : '';

        return self::getConnectionForTable($tables)->query($sql)->fetchAll();
    }

    /**
     * Executes a SELECT query and returns one column from the result rows as a
     * one-dimensional numeric array.
     *
     * If there is more than one matching record, only one will be returned.
     *
     * @param string $fieldName name of the field to select, must not be empty
     * @param string $tableNames comma-separated list of tables from which to select, must not be empty
     * @param string $whereClause WHERE clause, may be empty
     * @param string $groupBy GROUP BY field(s), may be empty
     * @param string $orderBy ORDER BY field(s), may be empty
     *
     * @return array<string, string> one column from the query result rows,
     *         will be empty if there are no matching records
     */
    public static function selectColumnForMultiple(
        string $fieldName,
        string $tableNames,
        string $whereClause = '',
        string $groupBy = '',
        string $orderBy = ''
    ): array {
        /** @var array<string, string> $result */
        $result = [];
        foreach (self::selectMultiple($fieldName, $tableNames, $whereClause, $groupBy, $orderBy) as $row) {
            $result[] = $row[$fieldName];
        }

        return $result;
    }

    /**
     * Counts the number of matching records in the database for a particular
     * WHERE clause.
     *
     * @param string $tableNames
     *        comma-separated list of existing tables from which to count, can
     *        also be a JOIN, must not be empty
     * @param string $whereClause WHERE clause, may be empty
     *
     * @return int the number of matching records, will be >= 0
     *
     * @throws DatabaseException if an error has occurred
     */
    public static function count(string $tableNames, string $whereClause = ''): int
    {
        $result = self::selectSingle('COUNT(*) AS oelib_counter', $tableNames, $whereClause);

        return (int)$result['oelib_counter'];
    }

    /**
     * Checks whether there are any records in the table given by the first
     * parameter $table that match a given WHERE clause.
     *
     * @param string $table the name of the table to query, must not be empty
     * @param string $whereClause
     *        the WHERE part of the query, may be empty (all records will be
     *        counted in that case)
     *
     * @return bool TRUE if there is at least one matching record,
     *                 FALSE otherwise
     */
    public static function existsRecord(string $table, string $whereClause = ''): bool
    {
        return self::count($table, $whereClause) > 0;
    }

    /**
     * Checks whether there is a record in the table given by the first
     * parameter $table that has the given UID.
     *
     * Important: This function also returns TRUE if there is a deleted or
     * hidden record with that particular UID.
     *
     * @param string $table the name of the table to query, must not be empty
     * @param int $uid the UID of the record to look up, must be > 0
     * @param string $additionalWhereClause
     *        additional WHERE clause to append, must either start with " AND"
     *        or be completely empty
     *
     * @return bool TRUE if there is a matching record, FALSE otherwise
     */
    public static function existsRecordWithUid(string $table, int $uid, string $additionalWhereClause = ''): bool
    {
        if ($uid <= 0) {
            throw new \InvalidArgumentException('$uid must be > 0.', 1331488284);
        }

        return self::count($table, 'uid = ' . $uid . $additionalWhereClause) > 0;
    }

    /////////////////////////////////////
    // Functions concerning table names
    /////////////////////////////////////

    /**
     * Retrieves the table names of the current DB and stores them in self::$tableNameCache.
     *
     * This function does nothing if the table names already have been retrieved.
     *
     * @return void
     */
    private static function retrieveTableNames()
    {
        if (!empty(self::$tableNameCache)) {
            return;
        }

        $connection = self::getConnectionPool()->getConnectionByName('Default');
        $queryResult = $connection->query(
            'SHOW TABLE STATUS FROM ' . $connection->quoteIdentifier($connection->getDatabase())
        );
        $tableNames = [];
        foreach ($queryResult->fetchAll() as $tableInformation) {
            $tableNames[$tableInformation['Name']] = $tableInformation;
        }

        self::$tableNameCache = $tableNames;
    }

    /**
     * Checks whether a database table exists.
     *
     * @param string $tableName the name of the table to check for, must not be empty
     *
     * @return bool TRUE if the table $tableName exists, FALSE otherwise
     */
    public static function existsTable(string $tableName): bool
    {
        if ($tableName === '') {
            throw new \InvalidArgumentException('The table name must not be empty.', 1331488301);
        }

        self::retrieveTableNames();

        return isset(self::$tableNameCache[$tableName]);
    }

    ////////////////////////////////////////////////
    // Functions concerning the columns of a table
    ////////////////////////////////////////////////

    /**
     * Gets the column data for a table.
     *
     * @param string $table
     *        the name of the table for which the column names should be retrieved, must not be empty
     *
     * @return array
     *         the column data for the table $table with the column names as keys and the SHOW COLUMNS field
     *     information (in an array) as values
     */
    public static function getColumnsInTable(string $table): array
    {
        self::retrieveColumnsForTable($table);

        return self::$tableColumnCache[$table];
    }

    /**
     * Retrieves and caches the column data for the table $table.
     *
     * If the column data for that table already is cached, this function does
     * nothing.
     *
     * @param string $table
     *        the name of the table for which the column names should be retrieved, must not be empty
     *
     * @return void
     */
    private static function retrieveColumnsForTable(string $table)
    {
        if (isset(self::$tableColumnCache[$table])) {
            return;
        }
        if (!self::existsTable($table)) {
            throw new \BadMethodCallException('The table "' . $table . '" does not exist.', 1331488327);
        }

        $connection = self::getConnectionForTable($table);
        $columns = [];
        $queryResult = $connection->query('SHOW FULL COLUMNS FROM ' . $connection->quoteIdentifier($table));
        foreach ($queryResult->fetchAll() as $row) {
            $columns[$row['Field']] = $row;
        }

        self::$tableColumnCache[$table] = $columns;
    }

    /**
     * Checks whether a table has a column with a particular name.
     *
     * To get a boolean TRUE as result, the table must contain a column with the
     * given name.
     *
     * @param string $table the name of the table to check, must not be empty
     * @param string $column the column name to check, must not be empty
     *
     * @return bool TRUE if the column with the provided name exists, FALSE
     *                 otherwise
     */
    public static function tableHasColumn(string $table, string $column): bool
    {
        if ($column === '') {
            return false;
        }

        self::retrieveColumnsForTable($table);

        return isset(self::$tableColumnCache[$table][$column]);
    }

    /////////////////////////////////
    // Functions concerning the TCA
    /////////////////////////////////

    /**
     * Returns $GLOBALS['TYPO3_DB'].
     *
     * @return DatabaseConnection
     *
     * @deprecated will be removed in oelib 4.0
     *
     * @phpstan-ignore-next-line We run the PHPStan checks with TYPO3 9LTS, and this code is for 8 only.
     */
    public static function getDatabaseConnection(): DatabaseConnection
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param string $tableName
     *
     * @return Connection
     */
    private static function getConnectionForTable(string $tableName): Connection
    {
        return self::getConnectionPool()->getConnectionForTable($tableName);
    }

    /**
     * @return ConnectionPool
     */
    private static function getConnectionPool(): ConnectionPool
    {
        /** @var ConnectionPool $pool */
        $pool = GeneralUtility::makeInstance(ConnectionPool::class);

        return $pool;
    }

    /**
     * @param string $tableName
     *
     * @return QueryBuilder
     */
    private static function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return self::getConnectionPool()->getQueryBuilderForTable($tableName);
    }

    /**
     * Normalizes the types in the given data so that the data con be inserted into a DB.
     *
     * @param array $rawData
     *
     * @return array
     */
    private static function normalizeDatabaseRow(array $rawData): array
    {
        $dataToInsert = [];
        foreach ($rawData as $key => $value) {
            $dataToInsert[$key] = \is_bool($value) ? (int)$value : $value;
        }

        return $dataToInsert;
    }

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController|null
     */
    private static function getFrontEndController()
    {
        return $GLOBALS['TSFE'] ?? null;
    }
}
