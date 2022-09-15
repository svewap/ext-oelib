<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use Doctrine\DBAL\Driver\ResultStatement;
use OliverKlee\Oelib\Authentication\FrontEndLoginManager;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\FrontEnd\UserWithoutCookies;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUserGroup;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Exception\Page\PageNotFoundException;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Localization\Locales;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\TypoScript\TemplateService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * This class provides various functions to handle dummy records in unit tests.
 */
final class TestingFramework
{
    /**
     * @var positive-int
     */
    private const AUTO_INCREMENT_THRESHOLD_WITHOUT_ROOTLINE_CACHE = 100;

    /**
     * all system table names to which this instance of the testing framework
     * has access
     *
     * @var array<int, non-empty-string>
     */
    private const ALLOWED_SYSTEM_TABLES = [
        'be_users',
        'fe_groups',
        'fe_users',
        'pages',
        'sys_template',
        'tt_content',
        'be_groups',
        'sys_file',
        'sys_file_collection',
        'sys_file_reference',
        'sys_category',
        'sys_category_record_mm',
    ];

    /**
     * @var non-empty-string
     */
    private const FAKE_FRONTEND_DOMAIN_NAME = 'typo3-test.dev';

    /**
     * @var non-empty-string
     */
    private const SITE_IDENTIFIER = 'testing-framework';

    /**
     * cache for the results of hasTableColumn with the column names as keys and
     * the SHOW COLUMNS field information (in an array) as values
     *
     * @var array<string, array<string, array<string, string>>>
     */
    private static $tableColumnCache = [];

    /**
     * @var array<non-empty-string, array<string, string|int|null>> cache for the results of existsTable with the
     *      table names as keys and the table SHOW STATUS information (in an array) as values
     */
    private static $tableNameCache = [];

    /**
     * @var bool
     */
    private $databaseInitialized = false;

    /**
     * prefix of the extension for which this instance of the testing framework
     * was instantiated (e.g. "tx_seminars")
     *
     * @var non-empty-string
     */
    private $tablePrefix;

    /**
     * prefixes of additional extensions to which this instance of the testing
     * framework has access (e.g. "tx_seminars")
     *
     * @var string[]
     */
    private $additionalTablePrefixes;

    /**
     * all own DB table names to which this instance of the testing framework has access
     *
     * @var array<int, non-empty-string>
     */
    private $ownAllowedTables = [];

    /**
     * all additional DB table names to which this instance of the testing framework has access
     *
     * @var string[]
     */
    private $additionalAllowedTables = [];

    /**
     * all "dirty" non-system tables (i.e., all tables that were used for testing
     * and need to be cleaned up)
     *
     * @var array<string, non-empty-string>
     */
    private $dirtyTables = [];

    /**
     * all "dirty" system tables (i.e. all tables that were used for testing and
     * need to be cleaned up)
     *
     * @var array<string, non-empty-string>
     */
    private $dirtySystemTables = [];

    /**
     * sorting values of all relation tables
     *
     * @var array<string, array<int, int>>
     */
    private $relationSorting = [];

    /**
     * The number of unusable UIDs after the maximum UID in a table before the auto increment value will be reset by
     * resetAutoIncrementLazily.
     *
     * This value needs to be high enough so that no two page UIDs will be the same within on request as the local
     * root-line cache of TYPO3 CMS otherwise might create false cache hits, causing failures for unit tests relying on
     * the root line.
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=5011
     *
     * @var int
     */
    private $resetAutoIncrementThreshold = 0;

    /**
     * the names of the created dummy files relative to the upload folder of the extension to test
     *
     * @var array<string, non-empty-string>
     */
    private $dummyFiles = [];

    /**
     * the names of the created dummy folders relative to the upload folder of the extension to test
     *
     * @var array<string, non-empty-string>
     */
    private $dummyFolders = [];

    /**
     * the absolute path to the upload folder of the extension to test (with the trailing slash)
     *
     * @var non-empty-string
     */
    private $uploadFolderPath;

    /**
     * whether a fake front end has been created
     *
     * @var bool
     */
    private $hasFakeFrontEnd = false;

    /**
     * hook objects for this class
     *
     * @var array<int, object>
     */
    private static $hooks = [];

    /**
     * whether the hooks in self::hooks have been retrieved
     *
     * @var bool
     */
    private static $hooksHaveBeenRetrieved = false;

    /**
     * @var CacheNullifyer
     */
    private $cacheNullifyer;

    /**
     * @var array<string, string|bool|null>|null
     */
    private $serverVariablesBackup = null;

    /**
     * This testing framework can be instantiated for one extension at a time.
     * Example: In your testcase, you'll have something similar to this line of code:
     *
     * `$this->subject = new TestingFramework('tx_seminars');`
     *
     * The parameter you provide is the prefix of the table names of that particular
     * extension. Like this, we ensure that the testing framework creates and
     * deletes records only on table with this prefix.
     *
     * If you need dummy records on tables of multiple extensions, you will have to
     * instantiate the testing framework multiple times (once per extension).
     *
     * Instantiating this class sets all core caches in order to avoid errors about not registered caches.
     *
     * @param non-empty-string $tablePrefix table name prefix of the extension
     *        for this instance of the testing framework
     * @param array<int, string> $additionalTablePrefixes additional table name prefixes of the extensions for which
     *        this instance of the testing framework should be used, may be empty
     */
    public function __construct(string $tablePrefix, array $additionalTablePrefixes = [])
    {
        $this->tablePrefix = $tablePrefix;
        $this->additionalTablePrefixes = $additionalTablePrefixes;
        $this->uploadFolderPath = Environment::getPublicPath() . '/typo3temp/' . $this->tablePrefix . '/';

        $this->cacheNullifyer = new CacheNullifyer();
        $this->cacheNullifyer->setAllCoreCaches();
    }

    private function initializeDatabase(): void
    {
        if ($this->databaseInitialized) {
            return;
        }

        $this->createListOfOwnAllowedTables();
        $this->createListOfAdditionalAllowedTables();
        $this->determineAndSetAutoIncrementThreshold();

        $this->databaseInitialized = true;
    }

    /**
     * Determines a good value for the auto increment threshold and sets it.
     */
    private function determineAndSetAutoIncrementThreshold(): void
    {
        $this->setResetAutoIncrementThreshold(self::AUTO_INCREMENT_THRESHOLD_WITHOUT_ROOTLINE_CACHE);
    }

    /**
     * Creates a new dummy record for unit tests.
     *
     * If no record data for the new array is given, an empty record will be
     * created. It will only contain a valid UID and the "is_dummy_record" flag
     * will be set to 1.
     *
     * Should there be any problem creating the record (wrong table name or a
     * problem with the database), 0 instead of a valid UID will be returned.
     *
     * @param non-empty-string $table the name of the table on which the record should be created
     * @param array<string, string|int|bool> $recordData data to save in the new record, may be empty,
     *        but must not contain the key "uid"
     *
     * @return int the UID of the new record, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createRecord(string $table, array $recordData = []): int
    {
        $this->initializeDatabase();
        if (!$this->isNoneSystemTableNameAllowed($table)) {
            throw new \InvalidArgumentException('The table name "' . $table . '" is not allowed.', 1331489666);
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1331489678);
        }

        return $this->createRecordWithoutTableNameChecks($table, $recordData);
    }

    /**
     * Creates a new dummy record for unit tests without checks for the table name.
     *
     * If no record data for the new array is given, an empty record will be created.
     * It will only contain a valid UID and the "is_dummy_record" flag will be set to 1.
     *
     * Should there be any problem creating the record (wrong table name or a
     * problem with the database), 0 instead of a valid UID will be returned.
     *
     * @param non-empty-string $table the name of the table on which the record should be created
     * @param array<string, string|int|bool> $rawData data to save, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new record, will be > 0
     */
    private function createRecordWithoutTableNameChecks(string $table, array $rawData): int
    {
        $this->initializeDatabase();
        $dataToInsert = $this->normalizeDatabaseRow($rawData);
        $dummyColumnName = $this->getDummyColumnName($table);
        $dataToInsert[$dummyColumnName] = 1;

        $connection = $this->getConnectionForTable($table);
        $connection->insert($table, $dataToInsert);
        $this->markTableAsDirty($table);

        return (int)$connection->lastInsertId($table);
    }

    /**
     * Normalizes the types in the given data so that the data con be inserted into a DB.
     *
     * @param array<string, string|int|bool|float> $rawData
     *
     * @return array<string, string|int|float>
     */
    private function normalizeDatabaseRow(array $rawData): array
    {
        $dataToInsert = [];
        foreach ($rawData as $key => $value) {
            $dataToInsert[$key] = \is_bool($value) ? (int)$value : $value;
        }

        return $dataToInsert;
    }

    /**
     * @param non-empty-string $tableName
     *
     * @return Connection
     */
    private function getConnectionForTable(string $tableName): Connection
    {
        return $this->getConnectionPool()->getConnectionForTable($tableName);
    }

    /**
     * @param non-empty-string $tableName
     *
     * @return QueryBuilder
     */
    private function getQueryBuilderForTable(string $tableName): QueryBuilder
    {
        return $this->getConnectionPool()->getQueryBuilderForTable($tableName);
    }

    /**
     * @return ConnectionPool
     */
    private function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * Creates a front-end page on the given page, and provides it with the page UID as slug.
     *
     * @param array<string, string|int>|null $data
     *
     * @return int the UID of the new page, will be > 0
     */
    public function createFrontEndPage(int $parentPageUid = 0, ?array $data = null): int
    {
        $data = $data ?? [];
        $hasSlug = \array_key_exists('slug', $data);
        $uid = $this->createGeneralPageRecord(1, $parentPageUid, $data);
        if (!$hasSlug) {
            $this->changeRecord('pages', $uid, ['slug' => '/' . $uid]);
        }

        return $uid;
    }

    /**
     * Creates a system folder on the page with the UID given by the first
     * parameter $parentId.
     *
     * @param int $parentId
     *        UID of the page on which the system folder should be created
     *
     * @return int the UID of the new system folder, will be > 0
     */
    public function createSystemFolder(int $parentId = 0): int
    {
        return $this->createGeneralPageRecord(254, $parentId, []);
    }

    /**
     * Creates a page record with the document type given by the first parameter
     * $documentType.
     *
     * The record will be created on the page with the UID given by the second
     * parameter $parentId.
     *
     * @param int $documentType document type of the record to create, must be > 0
     * @param int $parentId UID of the page on which the record should be created
     * @param array<string, string|int|bool> $recordData data to save in the record, may be empty,
     *        but must not contain the keys "uid", "pid" or "doktype"
     *
     * @return int the UID of the new record, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    private function createGeneralPageRecord(int $documentType, int $parentId, array $recordData): int
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1331489697);
        }
        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1331489703);
        }
        if (isset($recordData['doktype'])) {
            throw new \InvalidArgumentException('The column "doktype" must not be set in $recordData.', 1331489708);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $parentId;
        $completeRecordData['doktype'] = $documentType;

        return $this->createRecordWithoutTableNameChecks('pages', $completeRecordData);
    }

    /**
     * Creates a template on the page with the UID given by the first parameter $pageId.
     *
     * @param int $pageId
     *        UID of the page on which the template should be created, must be > 0
     * @param array<string, string|int|bool> $recordData data to save, may be empty,
     *        but must not contain the keys "uid" or "pid"
     *
     * @return int the UID of the new template, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createTemplate(int $pageId, array $recordData = []): int
    {
        if ($pageId <= 0) {
            throw new \InvalidArgumentException('$pageId must be > 0.', 1331489774);
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1331489769);
        }
        if (isset($recordData['pid'])) {
            throw new \InvalidArgumentException('The column "pid" must not be set in $recordData.', 1331489764);
        }

        $completeRecordData = $recordData;
        $completeRecordData['pid'] = $pageId;

        return $this->createRecordWithoutTableNameChecks('sys_template', $completeRecordData);
    }

    /**
     * Creates a FE user group.
     *
     * @param array<string, string|int|bool> $recordData data to save, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new user group, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createFrontEndUserGroup(array $recordData = []): int
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1331489807);
        }

        return $this->createRecordWithoutTableNameChecks('fe_groups', $recordData);
    }

    /**
     * Creates a FE user record.
     *
     * @param string|int $frontEndUserGroups
     *        comma-separated list of UIDs of the user groups to which the new user belongs, each must be > 0,
     *        may contain spaces, if empty a new FE user group will be created
     * @param array<string, string|int|bool> $recordData data to save, may be empty,
     *        but must not contain the keys "uid" or "usergroup"
     *
     * @return int the UID of the new FE user, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createFrontEndUser($frontEndUserGroups = '', array $recordData = []): int
    {
        $frontEndUserGroupsWithoutSpaces = str_replace(' ', '', (string)$frontEndUserGroups);

        if ($frontEndUserGroupsWithoutSpaces === '') {
            $frontEndUserGroupsWithoutSpaces = (string)$this->createFrontEndUserGroup();
        }
        $groupsCheckResult = \preg_match('/^(?:[1-9]+\\d*,?)+$/', $frontEndUserGroupsWithoutSpaces);
        if (!\is_int($groupsCheckResult) || $groupsCheckResult === 0) {
            throw new \InvalidArgumentException(
                '$frontEndUserGroups must contain a comma-separated list of UIDs. Each UID must be > 0.',
                1331489824
            );
        }
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1331489842);
        }
        if (isset($recordData['usergroup'])) {
            throw new \InvalidArgumentException('The column "usergroup" must not be set in $recordData.', 1331489846);
        }

        $completeRecordData = $recordData;
        $completeRecordData['usergroup'] = $frontEndUserGroupsWithoutSpaces;

        return $this->createRecordWithoutTableNameChecks('fe_users', $completeRecordData);
    }

    /**
     * Creates and logs in an FE user.
     *
     * @param string|int $frontEndUserGroups comma-separated list of UIDs of the user groups to which the user belongs,
     *        each must be > 0, may contain spaces; if empty a new front-end user group is created
     * @param array<string, string|int|bool> $recordData data to save, may be empty,
     *        but must not contain the keys "uid" or "usergroup"
     *
     * @return int the UID of the new FE user, will be > 0
     */
    public function createAndLoginFrontEndUser($frontEndUserGroups = '', array $recordData = []): int
    {
        $frontEndUserUid = $this->createFrontEndUser($frontEndUserGroups, $recordData);

        $this->loginFrontEndUser($frontEndUserUid);

        return $frontEndUserUid;
    }

    /**
     * Creates a BE user record.
     *
     * @param array<string, string|int|bool> $recordData data to save, may be empty, but must not contain the key "uid"
     *
     * @return int the UID of the new BE user, will be > 0
     */
    public function createBackEndUser(array $recordData = []): int
    {
        if (isset($recordData['uid'])) {
            throw new \InvalidArgumentException('The column "uid" must not be set in $recordData.', 1331489905);
        }

        return $this->createRecordWithoutTableNameChecks('be_users', $recordData);
    }

    /**
     * Changes an existing dummy record and stores the new data for this
     * record. Only fields that get new values in $recordData will be changed,
     * everything else will stay untouched.
     *
     * The array with the new recordData must contain at least one entry, but
     * must not contain a new UID for the record. If you need to change the UID,
     * you have to create a new record!
     *
     * @param non-empty-string $table the name of the table
     * @param int $uid the UID of the record to change
     * @param array<string, string|int|bool|float> $rawData the values to be changed as key-value pairs
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function changeRecord(string $table, int $uid, array $rawData): void
    {
        $this->initializeDatabase();
        $dummyColumnName = $this->getDummyColumnName($table);

        if (!$this->isTableNameAllowed($table)) {
            throw new \InvalidArgumentException(
                'The table "' . $table . '" is not on the lists with allowed tables.',
                1331489997
            );
        }
        if ($uid === 0) {
            throw new \InvalidArgumentException('The parameter $uid must not be zero.', 1331490003);
        }
        if ($rawData === []) {
            throw new \InvalidArgumentException('The array with the new record data must not be empty.', 1331490008);
        }
        if (isset($rawData['uid'])) {
            throw new \InvalidArgumentException(
                'The parameter $recordData must not contain changes to the UID of a record.',
                1331490017
            );
        }
        if (isset($rawData[$dummyColumnName])) {
            throw new \InvalidArgumentException(
                'The parameter $recordData must not contain changes to the field "' . $dummyColumnName .
                '". It is impossible to convert a dummy record into a regular record.',
                1331490024
            );
        }
        if (!$this->existsRecordWithUid($table, $uid)) {
            throw new \BadMethodCallException(
                'There is no record with UID ' . $uid . ' on table "' . $table . '".',
                1331490033
            );
        }

        $dataToSave = $this->normalizeDatabaseRow($rawData);
        $this->getConnectionForTable($table)->update($table, $dataToSave, ['uid' => $uid, $dummyColumnName => 1]);
    }

    /**
     * Creates a relation between two records on different tables (so called
     * m:n relation).
     *
     * @param non-empty-string $table name of the m:n table to which the record should be added
     * @param int $uidLocal UID of the local table, must be > 0
     * @param int $uidForeign UID of the foreign table, must be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function createRelation(string $table, int $uidLocal, int $uidForeign): void
    {
        $this->initializeDatabase();
        if (!$this->isNoneSystemTableNameAllowed($table)) {
            throw new \InvalidArgumentException('The table name "' . $table . '" is not allowed.', 1331490358);
        }

        if ($uidLocal <= 0) {
            throw new \InvalidArgumentException('$uidLocal must be > 0, but is: ' . $uidLocal, 1331490370);
        }
        if ($uidForeign <= 0) {
            throw new \InvalidArgumentException('$uidForeign must be > 0, but is: ' . $uidForeign, 1331490378);
        }

        $this->markTableAsDirty($table);

        $recordData = [
            'uid_local' => $uidLocal,
            'uid_foreign' => $uidForeign,
            'sorting' => $this->getRelationSorting($table, $uidLocal),
            $this->getDummyColumnName($table) => 1,
        ];

        $this->getConnectionForTable($table)->insert($table, $recordData);
    }

    /**
     * Creates a relation between two records based on the rules defined in TCA
     * regarding the relation.
     *
     * @param non-empty-string $tableName name of the table from which a relation should be created
     * @param int $uidLocal UID of the record in the local table, must be > 0
     * @param int $uidForeign UID of the record in the foreign table, must be > 0
     * @param non-empty-string $columnName name of the column in which the relation counter should be updated
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    public function createRelationAndUpdateCounter(
        string $tableName,
        int $uidLocal,
        int $uidForeign,
        string $columnName
    ): void {
        $this->initializeDatabase();
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException('The table name "' . $tableName . '" is not allowed.', 1331490419);
        }

        if ($uidLocal <= 0) {
            throw new \InvalidArgumentException(
                '$uidLocal must be > 0, but actually is "' . $uidLocal . '"',
                1331490425
            );
        }
        if ($uidForeign <= 0) {
            throw new \InvalidArgumentException(
                '$uidForeign must be  > 0, but actually is "' . $uidForeign . '"',
                1331490429
            );
        }

        $tca = $this->getTcaForTable($tableName);
        $relationConfiguration = $tca['columns'][$columnName];

        if (!isset($relationConfiguration['config']['MM']) || ($relationConfiguration['config']['MM'] === '')) {
            throw new \BadMethodCallException(
                'The column ' . $columnName . ' in the table ' . $tableName .
                ' is not configured to contain m:n relations using a m:n table.',
                1331490434
            );
        }

        if (isset($relationConfiguration['config']['MM_opposite_field'])) {
            // Switches the order of $uidForeign and $uidLocal as the relation
            // is the reverse part of a bidirectional relation.
            $this->createRelationAndUpdateCounter(
                $relationConfiguration['config']['foreign_table'],
                $uidForeign,
                $uidLocal,
                $relationConfiguration['config']['MM_opposite_field']
            );
        } else {
            $this->createRelation(
                $relationConfiguration['config']['MM'],
                $uidLocal,
                $uidForeign
            );
        }

        $this->increaseRelationCounter($tableName, $uidLocal, $columnName);
    }

    /**
     * Returns the TCA for a certain table.
     *
     * @param non-empty-string $tableName the table name to look up
     *
     * @return array<array<string, mixed>> associative array with the TCA description for this table
     */
    private function getTcaForTable(string $tableName): array
    {
        if (!isset($GLOBALS['TCA'][$tableName])) {
            throw new \BadMethodCallException('The table "' . $tableName . '" has no TCA.', 1569701919);
        }

        return $GLOBALS['TCA'][$tableName];
    }

    /**
     * Deletes all dummy records that have been added through this framework.
     * For this, all records with the "is_dummy_record" flag set to 1 will be
     * deleted from all tables that have been used within this instance of the
     * testing framework.
     *
     * If you set $performDeepCleanUp to TRUE, it will go through ALL tables to
     * which the current instance of the testing framework has access. Please
     * consider well, whether you want to do this as it's a huge performance
     * issue.
     *
     * @param bool $performDeepCleanUp whether a deep clean up should be performed, may be empty
     */
    public function cleanUp(bool $performDeepCleanUp = false): void
    {
        $this->cleanUpDatabase($performDeepCleanUp);
        $this->cleanUpWithoutDatabase();
    }

    private function cleanUpDatabase(bool $performDeepCleanUp = false): void
    {
        $this->initializeDatabase();
        $this->cleanUpTableSet(false, $performDeepCleanUp);
        $this->cleanUpTableSet(true, $performDeepCleanUp);
    }

    /**
     * Cleanup without deleting dummy records. Use this method instead of cleanUp() for better performance when
     * another testing framework (e.g., nimut/testing-framework) already takes care of cleaning up old database records.
     */
    public function cleanUpWithoutDatabase(): void
    {
        $this->deleteAllDummyFoldersAndFiles();
        $this->discardFakeFrontEnd();
        WritableEnvironment::restoreCurrentScript();
        GeneralUtility::flushInternalRuntimeCaches();
        if (\is_array($this->serverVariablesBackup)) {
            $GLOBALS['_SERVER'] = $this->serverVariablesBackup;
            $this->serverVariablesBackup = null;
        }

        foreach ($this->getHooks() as $hook) {
            if (method_exists($hook, 'cleanUp')) {
                $hook->cleanUp($this);
            }
        }

        RootlineUtility::purgeCaches();
    }

    /**
     * Deletes a set of records that have been added through this framework for
     * a set of tables (either the test tables or the allowed system tables).
     * For this, all records with the "is_dummy_record" flag set to 1 will be
     * deleted from all tables that have been used within this instance of the
     * testing framework.
     *
     * If you set $performDeepCleanUp to TRUE, it will go through ALL tables to
     * which the current instance of the testing framework has access. Please
     * consider well, whether you want to do this as it's a huge performance
     * issue.
     *
     * @param bool $useSystemTables whether to clean up the system tables (TRUE) or the non-system test tables (FALSE)
     * @param bool $performDeepCleanUp whether a deep clean up should be performed, may be empty
     */
    private function cleanUpTableSet(bool $useSystemTables, bool $performDeepCleanUp): void
    {
        if ($useSystemTables) {
            $tablesToCleanUp = $performDeepCleanUp ? self::ALLOWED_SYSTEM_TABLES : $this->dirtySystemTables;
        } else {
            $tablesToCleanUp = $performDeepCleanUp ? $this->ownAllowedTables : $this->dirtyTables;
        }

        foreach ($tablesToCleanUp as $currentTable) {
            $dummyColumnName = $this->getDummyColumnName($currentTable);
            if (!$this->tableHasColumn($currentTable, $dummyColumnName)) {
                continue;
            }

            // Runs a DELETE query for each allowed table. A "one-query-deletes-them-all" approach was tested,
            // but we didn't find a working solution for that.
            $this->getConnectionForTable($currentTable)->delete($currentTable, [$dummyColumnName => 1]);
            $this->resetAutoIncrementLazily($currentTable);
        }

        $this->dirtyTables = [];
    }

    /**
     * Checks whether a table has a column "uid".
     *
     * @param non-empty-string $table the name of the table to check
     *
     * @return bool
     */
    private function tableHasColumnUid(string $table): bool
    {
        return $this->tableHasColumn($table, 'uid');
    }

    /**
     * Checks whether a table has a column with a particular name.
     *
     * @param non-empty-string $table the name of the table to check
     * @param string $column the column name to check
     *
     * @return bool
     */
    private function tableHasColumn(string $table, string $column): bool
    {
        if ($column === '') {
            return false;
        }

        $this->retrieveColumnsForTable($table);

        return isset(self::$tableColumnCache[$table][$column]);
    }

    /**
     * Retrieves and caches the column data for the table $table.
     *
     * If the column data for that table already is cached, this function does
     * nothing.
     *
     * @param non-empty-string $table the name of the table for which the column names should be retrieved
     */
    private function retrieveColumnsForTable(string $table): void
    {
        if (isset(self::$tableColumnCache[$table])) {
            return;
        }

        $connection = $this->getConnectionForTable($table);
        $query = 'SHOW FULL COLUMNS FROM `' . $table . '`';
        if (\method_exists($connection, 'executeQuery')) {
            $queryResult = $connection->executeQuery($query);
        } else {
            $queryResult = $connection->query($query);
        }
        $columns = [];
        if (\method_exists($queryResult, 'fetchAllAssociative')) {
            /** @var array<string, string> $fieldRow */
            foreach ($queryResult->fetchAllAssociative() as $fieldRow) {
                $field = $fieldRow['Field'];
                $columns[$field] = $fieldRow;
            }
        } else {
            /** @var array<string, string> $fieldRow */
            foreach ($queryResult->fetchAll() as $fieldRow) {
                $field = $fieldRow['Field'];
                $columns[$field] = $fieldRow;
            }
        }

        self::$tableColumnCache[$table] = $columns;
    }

    /**
     * Deletes all dummy files and folders.
     */
    private function deleteAllDummyFoldersAndFiles(): void
    {
        // If the upload folder was created by the testing framework, it can be
        // removed at once.
        if (isset($this->dummyFolders['uploadFolder'])) {
            GeneralUtility::rmdir($this->getUploadFolderPath(), true);
            $this->dummyFolders = [];
            $this->dummyFiles = [];
        } else {
            foreach ($this->dummyFiles as $dummyFile) {
                $this->deleteDummyFile($dummyFile);
            }
            foreach ($this->dummyFolders as $dummyFolder) {
                $this->deleteDummyFolder($dummyFolder);
            }
        }
    }

    // File creation and deletion

    /**
     * Creates an empty dummy file with a unique file name in the calling
     * extension's upload directory.
     *
     * @param non-empty-string $fileName path of the dummy file to create,
     *        relative to the calling extension's upload directory
     * @param string $content content for the file to create, may be empty
     *
     * @return non-empty-string the absolute path of the created dummy file
     *
     * @throws \RuntimeException
     *
     * @deprecated will be removed in oelib 5.0
     */
    public function createDummyFile(string $fileName = 'test.txt', string $content = ''): string
    {
        $this->createDummyUploadFolder();
        $uniqueFileName = $this->getUniqueFileOrFolderPath($fileName);

        if (!GeneralUtility::writeFile($uniqueFileName, $content)) {
            throw new \RuntimeException('The file ' . $uniqueFileName . ' could not be created.', 1331490486);
        }

        $this->addToDummyFileList($uniqueFileName);

        return $uniqueFileName;
    }

    /**
     * Adds a file name to $this->dummyFiles.
     *
     * @param non-empty-string $uniqueFileName file name to add, must be the unique name of a dummy file
     */
    private function addToDummyFileList(string $uniqueFileName): void
    {
        $relativeFileName = $this->getPathRelativeToUploadDirectory($uniqueFileName);

        $this->dummyFiles[$relativeFileName] = $relativeFileName;
    }

    /**
     * Deletes the dummy file specified by the first parameter $fileName.
     *
     * @param non-empty-string $fileName the path to the file to delete relative to $this->uploadFolderPath
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    public function deleteDummyFile(string $fileName): void
    {
        $absolutePathToFile = $this->getUploadFolderPath() . $fileName;
        $fileExists = is_file($absolutePathToFile);

        if (!isset($this->dummyFiles[$fileName])) {
            throw new \InvalidArgumentException(
                'The file "' . $absolutePathToFile . '" which you are trying to delete ' .
                (!$fileExists ? 'does not exist and has never been ' : 'was not ') .
                'created by this instance of the testing framework.',
                1331490556
            );
        }

        if ($fileExists && !@unlink($absolutePathToFile)) {
            throw new \RuntimeException('The file "' . $absolutePathToFile . '" could not be deleted.', 1331490596);
        }

        unset($this->dummyFiles[$fileName]);
    }

    /**
     * Creates a dummy folder with a unique folder name in the calling
     * extension's upload directory.
     *
     * @param non-empty-string $folderName name of the dummy folder to create relative to `$this->uploadFolderPath`
     *
     * @return non-empty-string the absolute path of the created dummy folder
     *
     * @throws \RuntimeException
     */
    public function createDummyFolder(string $folderName): string
    {
        $this->createDummyUploadFolder();
        $uniqueFolderName = $this->getUniqueFileOrFolderPath($folderName);

        if (!GeneralUtility::mkdir($uniqueFolderName)) {
            throw new \RuntimeException('The folder ' . $uniqueFolderName . ' could not be created.', 1331490619);
        }

        $relativeUniqueFolderName = $this->getPathRelativeToUploadDirectory($uniqueFolderName);

        // Adds the created dummy folder to the top of $this->dummyFolders, so that
        // it gets deleted before previously created folders through
        // $this->cleanUpFolders(). This is needed for nested dummy folders.
        $this->dummyFolders = [$relativeUniqueFolderName => $relativeUniqueFolderName] + $this->dummyFolders;

        return $uniqueFolderName;
    }

    /**
     * Deletes the dummy folder specified in the first parameter $folderName.
     * The folder must be empty (no files or subfolders).
     *
     * @param non-empty-string $folderName the path to the folder to delete relative to $this->uploadFolderPath
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function deleteDummyFolder(string $folderName): void
    {
        $absolutePathToFolder = $this->getUploadFolderPath() . $folderName;

        if (!is_dir($absolutePathToFolder)) {
            throw new \InvalidArgumentException(
                'The folder "' . $absolutePathToFolder . '" which you are trying to delete does not exist.',
                1331490646
            );
        }

        if (!isset($this->dummyFolders[$folderName])) {
            throw new \InvalidArgumentException(
                'The folder "' . $absolutePathToFolder .
                '" which you are trying to delete was not created by this instance of the testing framework.',
                1331490670
            );
        }

        if (!GeneralUtility::rmdir($absolutePathToFolder)) {
            throw new \RuntimeException('The folder "' . $absolutePathToFolder . '" could not be deleted.', 1331490702);
        }

        unset($this->dummyFolders[$folderName]);
    }

    /**
     * Creates the upload folder if it does not exist yet.
     *
     * @throws \RuntimeException
     *
     * @deprecated will be removed in oelib 5.0
     */
    private function createDummyUploadFolder(): void
    {
        $uploadFolderPath = $this->getUploadFolderPath();
        if (is_dir($uploadFolderPath)) {
            return;
        }

        if (!GeneralUtility::mkdir($uploadFolderPath)) {
            throw new \RuntimeException(
                'The upload folder ' . $uploadFolderPath . ' could not be created.',
                1331490723
            );
        }

        $this->dummyFolders['uploadFolder'] = $uploadFolderPath;
    }

    /**
     * Sets the upload folder path.
     *
     * @param non-empty-string $absolutePath absolute path to the folder where to work on during the tests,
     *        can be either an existing folder which will be cleaned up after the tests or a path of a folder
     *        to be created as soon as it is needed and deleted during cleanUp, must end with a trailing slash
     *
     * @throws \BadMethodCallException if there are dummy files within the current upload folder as these files could
     *         not be deleted if the upload folder path has changed
     */
    public function setUploadFolderPath(string $absolutePath): void
    {
        if ($this->dummyFiles !== [] || $this->dummyFolders !== []) {
            throw new \BadMethodCallException(
                'The upload folder path must not be changed if there are already dummy files or folders.',
                1331490745
            );
        }

        $this->uploadFolderPath = $absolutePath;
    }

    /**
     * Returns the absolute path to the upload folder of the extension to test.
     *
     * @return non-empty-string the absolute path to the upload folder of the extension to test, with the trailing slash
     */
    public function getUploadFolderPath(): string
    {
        return $this->uploadFolderPath;
    }

    /**
     * Returns the path relative to the calling extension's upload directory for
     * a path given in the first parameter $absolutePath.
     *
     * @param non-empty-string $absolutePath the absolute path to process,
     *        must be within the calling extension's upload directory
     *
     * @return non-empty-string the path relative to the calling extension's upload directory
     *
     * @throws \InvalidArgumentException if the first parameter $absolutePath is not within
     *         the calling extension's upload directory
     */
    public function getPathRelativeToUploadDirectory(string $absolutePath): string
    {
        $checkResult
            = \preg_match('/^' . \str_replace('/', '\\/', $this->getUploadFolderPath()) . '.*$/', $absolutePath);
        if (!\is_int($checkResult) || $checkResult === 0) {
            throw new \InvalidArgumentException(
                'The first parameter $absolutePath is not within the calling extension\'s upload directory.',
                1331490760
            );
        }

        $encoding = 'UTF-8';
        $uploadFolderPathLength = mb_strlen($this->getUploadFolderPath(), $encoding);
        $absolutePathLength = mb_strlen($absolutePath, $encoding);

        /** @var non-empty-string $result */
        $result = \mb_substr($absolutePath, $uploadFolderPathLength, $absolutePathLength, $encoding);

        return $result;
    }

    /**
     * Returns a unique absolute path of a file or folder.
     *
     * @param non-empty-string $path the path of a file or folder relative to the calling extension's upload directory
     *
     * @return non-empty-string the unique absolute path of a file or folder
     *
     * @throws \InvalidArgumentException
     */
    private function getUniqueFileOrFolderPath(string $path): string
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($path === '') {
            throw new \InvalidArgumentException('The first parameter $path must not be empty.', 1331490775);
        }

        $pathInformation = pathinfo($path);
        $fileNameWithoutExtension = $pathInformation['filename'];
        if ($pathInformation['dirname'] !== '.') {
            $absoluteDirectoryWithTrailingSlash = $this->getUploadFolderPath() . $pathInformation['dirname'] . '/';
        } else {
            $absoluteDirectoryWithTrailingSlash = $this->getUploadFolderPath();
        }

        $extension = isset($pathInformation['extension']) ? ('.' . $pathInformation['extension']) : '';

        $suffixCounter = 0;
        do {
            $suffix = ($suffixCounter > 0) ? ('-' . $suffixCounter) : '';
            $newPath = $absoluteDirectoryWithTrailingSlash . $fileNameWithoutExtension . $suffix . $extension;
            $suffixCounter++;
        } while (is_file($newPath));

        return $newPath;
    }

    // Functions concerning a fake front end

    /**
     * @return non-empty-string
     */
    public function getFakeFrontEndDomain(): string
    {
        return self::FAKE_FRONTEND_DOMAIN_NAME;
    }

    /**
     * @return non-empty-string
     */
    public function getFakeSiteUrl(): string
    {
        return 'http://' . $this->getFakeFrontEndDomain() . '/';
    }

    /**
     * Fakes a TYPO3 front end, using $pageUid as front-end page ID if provided.
     *
     * If $pageUid is zero, the front end will have not page UID.
     *
     * This function creates `$GLOBALS['TSFE']`.
     *
     * Note: This function does not set TYPO3_MODE to "FE" (because the value of
     * a constant cannot be changed after it has once been set).
     *
     * @param int $pageUid UID of a page record to use, must be >= 0
     *
     * @return int the UID of the used front-end page, will be > 0
     *
     * @throws \InvalidArgumentException if $pageUid is < 0
     */
    public function createFakeFrontEnd(int $pageUid = 0): int
    {
        if ($pageUid < 0) {
            throw new \InvalidArgumentException('$pageUid must be >= 0.', 1331490786);
        }

        $this->suppressFrontEndCookies();
        $this->discardFakeFrontEnd();

        $this->setPageIndependentGlobalsForFakeFrontEnd();
        $this->setRequestUriForFakeFrontEnd($pageUid);

        $frontEndUser = GeneralUtility::makeInstance(FrontendUserAuthentication::class);
        $frontEndUser->start();
        $frontEndUser->unpack_uc();
        $frontEndUser->fetchGroupData();

        if ($pageUid > 0) {
            $this->createDummySite($pageUid);
            $allSites = GeneralUtility::makeInstance(SiteConfiguration::class)->getAllExistingSites(false);
            $site = $allSites[self::SITE_IDENTIFIER] ?? null;
            if (!$site instanceof Site) {
                throw new \RuntimeException('Dummy site not found.', 1635024025);
            }
            $language = $site->getLanguageById(0);
        } else {
            $site = new Site('test', $pageUid, []);
            $language = new SiteLanguage(0, 'en_US.utf8', new Uri($this->getFakeSiteUrl()), []);
        }
        $frontEnd = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $language,
            new PageArguments($pageUid, '', []),
            $frontEndUser
        );
        $GLOBALS['TSFE'] = $frontEnd;

        $frontEnd->fe_user = $frontEndUser;
        if ($pageUid > 0) {
            $frontEnd->id = (string)$pageUid;
            $frontEnd->determineId();
        }
        $frontEnd->tmpl = GeneralUtility::makeInstance(TemplateService::class);
        $frontEnd->config = [
            'config' => ['MP_disableTypolinkClosestMPvalue' => true, 'typolinkLinkAccessRestrictedPages' => true],
        ];

        if ($pageUid > 0 && \in_array('sys_template', $this->dirtySystemTables, true)) {
            try {
                $rootLine = GeneralUtility::makeInstance(RootlineUtility::class, $pageUid)->get();
            } catch (PageNotFoundException $e) {
                $rootLine = [];
            }

            $frontEnd->tmpl->runThroughTemplates($rootLine);
            $frontEnd->tmpl->generateConfig();
            $frontEnd->tmpl->loaded = true;
            Locales::setSystemLocaleFromSiteLanguage($frontEnd->getLanguage());
        }

        $frontEnd->newCObj();
        /** @var ContentObjectRenderer $contentObject */
        $contentObject = $frontEnd->cObj;
        $contentObject->setLogger(new NullLogger());

        $this->hasFakeFrontEnd = true;
        $this->logoutFrontEndUser();

        return $pageUid;
    }

    /**
     * Discards all site configuration files, and creates a new configuration file for a dummy site.
     *
     * Starting with TYPO3 10, we will be able to use `SiteConfiguration::createNewBasicSite()` for this.
     */
    private function createDummySite(int $pageUid): void
    {
        $siteConfigurationDirectory = Environment::getConfigPath() . '/sites/';
        GeneralUtility::rmdir($siteConfigurationDirectory, true);
        $configurationDirectoryForTestingDummySite = $siteConfigurationDirectory . self::SITE_IDENTIFIER;
        GeneralUtility::mkdir_deep($configurationDirectoryForTestingDummySite);

        $url = $this->getFakeSiteUrl();
        $contents =
            "rootPageId: $pageUid
base: '$url'
baseVariants: {  }
languages:
  -
    title: 'Englisch'
    enabled: true
    languageId: 0
    base: '/'
    typo3Language: 'default'
    locale: 'en_US.UTF-8'
    iso-639-1: 'en'
    navigationTitle: 'Englisch'
    hreflang: 'en-US'
    direction: 'ltr'
    flag: 'us'
errorHandling: {  }
routes: {  }";

        $file = $configurationDirectoryForTestingDummySite . '/config.yaml';
        \file_put_contents($file, $contents);
        if (!\is_readable($file)) {
            throw new \RuntimeException('Site config file "' . $file . '" could not be created.', 1634918114);
        }
    }

    private function setPageIndependentGlobalsForFakeFrontEnd(): void
    {
        if (!\is_array($this->serverVariablesBackup)) {
            $this->serverVariablesBackup = $GLOBALS['_SERVER'];
        }

        GeneralUtility::flushInternalRuntimeCaches();
        unset($GLOBALS['TYPO3_REQUEST']);

        $hostName = $this->getFakeFrontEndDomain();
        $documentRoot = '/var/www/html/public';
        $relativeScriptPath = '/index.php';
        $absoluteScriptPath = $documentRoot . '/index.php';
        $server = &$GLOBALS['_SERVER'];

        $server['DOCUMENT_ROOT'] = $documentRoot;
        $server['HOSTNAME'] = $hostName;
        $server['HTTP'] = 'off';
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';
        $server['HTTP_ACCEPT_LANGUAGE'] = 'de,en-US;q=0.7,en;q=0.3';
        $server['HTTP_HOST'] = $hostName;
        $server['HTTP_REFERER'] = $this->getFakeSiteUrl();
        $server['HTTP_USER_AGENT'] = 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:93.0) Gecko/20100101 Firefox/93.0';
        $server['PHP_SELF'] = '/index.php';
        $server['QUERY_STRING'] = '';
        $server['REMOTE_ADDR'] = '127.0.0.1';
        $server['REMOTE_HOST'] = '';
        $server['REQUEST_SCHEME'] = 'http';
        $server['SCRIPT_FILENAME'] = $absoluteScriptPath;
        $server['SCRIPT_NAME'] = $relativeScriptPath;
        $server['SERVER_ADDR'] = '127.0.0.1';
        $server['SERVER_NAME'] = $hostName;
        $server['SERVER_SOFTWARE'] = 'Apache/2.4.48 (Debian)';

        WritableEnvironment::setCurrentScript($absoluteScriptPath);
    }

    private function setRequestUriForFakeFrontEnd(int $pageUid): void
    {
        $slug = '/';
        if ($pageUid > 0) {
            $slug .= $pageUid;
        }

        $GLOBALS['_SERVER']['REQUEST_URI'] = $slug;
    }

    /**
     * Discards the fake front end.
     *
     * This function nulls out $GLOBALS['TSFE']. In addition, any logged-in front-end user will be logged out.
     *
     * The page record for the current front end will _not_ be deleted by this
     * function, though.
     *
     * If no fake front end has been created, this function does nothing.
     */
    private function discardFakeFrontEnd(): void
    {
        if (!$this->hasFakeFrontEnd()) {
            return;
        }

        $this->logoutFrontEndUser();

        $GLOBALS['TSFE'] = null;
        unset(
            $GLOBALS['TYPO3_REQUEST'],
            $GLOBALS['TYPO3_CONF_VARS']['FE']['dontSetCookie'],
            $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUserAuthentication::class]
        );

        $this->hasFakeFrontEnd = false;
    }

    /**
     * Returns whether this testing framework instance has a fake front end.
     *
     * @return bool TRUE if this instance has a fake front end, FALSE
     *                 otherwise
     */
    private function hasFakeFrontEnd(): bool
    {
        return $this->hasFakeFrontEnd;
    }

    /**
     * Makes sure that no FE login cookies will be sent.
     */
    private function suppressFrontEndCookies(): void
    {
        // avoid cookies from the phpMyAdmin extension
        $GLOBALS['PHP_UNIT_TEST_RUNNING'] = true;

        $GLOBALS['_POST']['FE_SESSION_KEY'] = '';
        $GLOBALS['_GET']['FE_SESSION_KEY'] = '';

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUserAuthentication::class]
            = ['className' => UserWithoutCookies::class];
    }

    // FE user activities

    /**
     * Fakes that a front-end user has logged in.
     *
     * If a front-end user currently is logged in, he/she will be logged out
     * first.
     *
     * Note: To set the logged-in users group data properly, the front-end user
     *       and his groups must actually exist in the database.
     *
     * @param int $userId UID of the FE user, must not necessarily exist in the database, must be > 0
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException if no front end has been created
     */
    private function loginFrontEndUser(int $userId): void
    {
        if ($userId <= 0) {
            throw new \InvalidArgumentException('The user ID must be > 0.', 1331490798);
        }
        if (!$this->hasFakeFrontEnd()) {
            throw new \BadMethodCallException(
                'Please create a front end before calling loginFrontEndUser.',
                1331490812
            );
        }

        if ($this->isLoggedIn()) {
            $this->logoutFrontEndUser();
        }

        $mapper = MapperRegistry::get(FrontEndUserMapper::class);
        // loads the model from database if it is a ghost
        $mapper->existsModel($userId);
        $dataToSet = $mapper->find($userId)->getData();
        $dataToSet['uid'] = $userId;
        if (isset($dataToSet['usergroup'])) {
            /** @var Collection<FrontEndUserGroup> $userGroups */
            $userGroups = $dataToSet['usergroup'];
            $dataToSet['usergroup'] = $userGroups->getUids();
        }

        $this->suppressFrontEndCookies();

        $frontEndUser = $this->getFrontEndController()->fe_user;
        if ($frontEndUser instanceof FrontendUserAuthentication) {
            $frontEndUser->createUserSession(['uid' => $userId, 'disableIPlock' => true]);
            $frontEndUser->user = $dataToSet;
            $frontEndUser->fetchGroupData();
        }
    }

    /**
     * Logs out the current front-end user.
     *
     * If no front-end user is logged in, this function does nothing.
     *
     * @throws \BadMethodCallException if no front end has been created
     */
    public function logoutFrontEndUser(): void
    {
        if (!$this->hasFakeFrontEnd()) {
            throw new \BadMethodCallException(
                'Please create a front end before calling logoutFrontEndUser.',
                1331490825
            );
        }
        if (!$this->isLoggedIn()) {
            return;
        }

        $this->suppressFrontEndCookies();
        $frontEndUser = $this->getFrontEndController()->fe_user;
        if ($frontEndUser instanceof FrontendUserAuthentication) {
            $frontEndUser->logoff();
        }

        FrontEndLoginManager::getInstance()->logInUser();
    }

    /**
     * Checks whether a FE user is logged in.
     *
     * @return bool TRUE if a FE user is logged in, FALSE otherwise
     *
     * @throws \BadMethodCallException if no front end has been created
     */
    public function isLoggedIn(): bool
    {
        if (!$this->hasFakeFrontEnd()) {
            throw new \BadMethodCallException('Please create a front end before calling isLoggedIn.', 1331490846);
        }

        return FrontEndLoginManager::getInstance()->isLoggedIn();
    }

    // ----------------------------------------------------------------------
    // Various helper functions
    // ----------------------------------------------------------------------

    /**
     * Returns a list of all table names that are available in the current
     * database.
     *
     * @return array<int, non-empty-string> table names
     */
    private function getAllTableNames(): array
    {
        $this->retrieveTableNames();

        return \array_keys(self::$tableNameCache);
    }

    /**
     * Retrieves the table names of the current DB and stores them in self::$tableNameCache.
     *
     * This function does nothing if the table names already have been retrieved.
     */
    private function retrieveTableNames(): void
    {
        if (self::$tableNameCache !== []) {
            return;
        }

        $connection = $this->getConnectionPool()->getConnectionByName('Default');
        $query = 'SHOW TABLE STATUS FROM `' . $connection->getDatabase() . '`';
        if (\method_exists($connection, 'executeQuery')) {
            $queryResult = $connection->executeQuery($query);
        } else {
            $queryResult = $connection->query($query);
        }
        $tableNames = [];
        if (\method_exists($queryResult, 'fetchAllAssociative')) {
            /** @var array<string, string|int|null> $tableInformation */
            foreach ($queryResult->fetchAllAssociative() as $tableInformation) {
                /** @var non-empty-string $tableName */
                $tableName = $tableInformation['Name'];
                $tableNames[$tableName] = $tableInformation;
            }
        } else {
            /** @var array<string, string|int|null> $tableInformation */
            foreach ($queryResult->fetchAll() as $tableInformation) {
                /** @var non-empty-string $tableName */
                $tableName = $tableInformation['Name'];
                $tableNames[$tableName] = $tableInformation;
            }
        }

        self::$tableNameCache = $tableNames;
    }

    /**
     * Generates a list of allowed tables to which this instance of the testing
     * framework has access to create/remove test records.
     *
     * The generated list is based on the list of all tables that TYPO3 can
     * access (which will be all tables in this database), filtered by prefix of
     * the extension to test.
     *
     * The array with the allowed table names is written directly to
     * $this->ownAllowedTables.
     */
    private function createListOfOwnAllowedTables(): void
    {
        $this->ownAllowedTables = [];
        $allTables = $this->getAllTableNames();
        $length = \strlen($this->tablePrefix);

        foreach ($allTables as $currentTable) {
            if (substr_compare($this->tablePrefix, $currentTable, 0, $length) === 0) {
                $this->ownAllowedTables[] = $currentTable;
            }
        }
    }

    /**
     * Generates a list of additional allowed tables to which this instance of
     * the testing framework has access to create/remove test records.
     *
     * The generated list is based on the list of all tables that TYPO3 can
     * access (which will be all tables in this database), filtered by the
     * prefixes of additional extensions.
     *
     * The array with the allowed table names is written directly to
     * $this->additionalAllowedTables.
     */
    private function createListOfAdditionalAllowedTables(): void
    {
        $allTables = \implode(',', $this->getAllTableNames());
        $additionalTablePrefixes = \implode('|', $this->additionalTablePrefixes);

        $matches = [];

        preg_match_all(
            '/((' . $additionalTablePrefixes . ')_[a-z\\d]+[a-z\\d_]*)(,|$)/',
            $allTables,
            $matches
        );

        if (isset($matches[1])) {
            $this->additionalAllowedTables = $matches[1];
        }
    }

    /**
     * Checks whether the given table name is in the list of allowed tables for
     * this instance of the testing framework.
     *
     * @param non-empty-string $table the name of the table to check
     *
     * @return bool TRUE if the name of the table is in the list of
     *                 allowed tables, FALSE otherwise
     */
    private function isOwnTableNameAllowed(string $table): bool
    {
        return in_array($table, $this->ownAllowedTables, true);
    }

    /**
     * Checks whether the given table name is in the list of additional allowed
     * tables for this instance of the testing framework.
     *
     * @param non-empty-string $table the name of the table to check
     *
     * @return bool TRUE if the name of the table is in the list of
     *                 additional allowed tables, FALSE otherwise
     */
    private function isAdditionalTableNameAllowed(string $table): bool
    {
        return in_array($table, $this->additionalAllowedTables, true);
    }

    /**
     * Checks whether the given table name is in the list of allowed
     * system tables for this instance of the testing framework.
     *
     * @param non-empty-string $table the name of the table to check
     *
     * @return bool TRUE if the name of the table is in the list of
     *                 allowed system tables, FALSE otherwise
     */
    private function isSystemTableNameAllowed(string $table): bool
    {
        return in_array($table, self::ALLOWED_SYSTEM_TABLES, true);
    }

    /**
     * Checks whether the given table name is in the list of allowed tables or
     * additional allowed tables for this instance of the testing framework.
     *
     * @param non-empty-string $table the name of the table to check
     *
     * @return bool TRUE if the name of the table is in the list of
     *                 allowed tables or additional allowed tables, FALSE
     *                 otherwise
     */
    private function isNoneSystemTableNameAllowed(string $table): bool
    {
        return $this->isOwnTableNameAllowed($table)
            || $this->isAdditionalTableNameAllowed($table);
    }

    /**
     * Checks whether the given table name is in the list of allowed tables,
     * additional allowed tables or allowed system tables.
     *
     * @param non-empty-string $table the name of the table to check
     *
     * @return bool TRUE if the name of the table is in the list of
     *                 allowed tables, additional allowed tables or allowed
     *                 system tables, FALSE otherwise
     */
    private function isTableNameAllowed(string $table): bool
    {
        return $this->isNoneSystemTableNameAllowed($table)
            || $this->isSystemTableNameAllowed($table);
    }

    /**
     * Returns the name of the column that marks a record as a dummy record.
     *
     * On most tables this is "is_dummy_record", but on system tables like
     * "pages" or "fe_users", the column is called "tx_oelib_dummy_record".
     *
     * On additional tables, the column is built using $this->tablePrefix as
     * prefix e.g. "tx_seminars_is_dummy_record" if $this->tablePrefix =
     * "tx_seminars".
     *
     * @param non-empty-string $table the table name to look up
     *
     * @return non-empty-string the name of the column that marks a record as dummy record
     */
    public function getDummyColumnName(string $table): string
    {
        $this->initializeDatabase();

        if ($this->isSystemTableNameAllowed($table)) {
            $result = 'tx_oelib_is_dummy_record';
        } elseif ($this->isAdditionalTableNameAllowed($table)) {
            $result = $this->tablePrefix . '_is_dummy_record';
        } else {
            $result = 'is_dummy_record';
        }

        return $result;
    }

    /**
     * Counts the dummy records in the table given by the first parameter $table
     * that match a given WHERE clause.
     *
     * @param non-empty-string $table the name of the table to query
     * @param array<string, string|int|bool> $criteria key-value pairs to match
     *
     * @return int the number of records that have been found, will be >= 0
     *
     * @throws \InvalidArgumentException
     */
    public function count(string $table, array $criteria = []): int
    {
        $this->initializeDatabase();
        if (!$this->isTableNameAllowed($table)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1569784847
            );
        }

        $allCriteria = $criteria;
        $dummyColumn = $this->getDummyColumnName($table);
        $allCriteria[$dummyColumn] = 1;
        $query = $this->getQueryBuilderForTable($table)->count('*')->from($table);
        $query->getRestrictions()->removeAll();
        foreach ($allCriteria as $identifier => $value) {
            $query->andWhere($query->expr()->eq($identifier, $query->createNamedParameter($value)));
        }
        $result = $query->execute();
        if (!$result instanceof ResultStatement) {
            throw new \UnexpectedValueException('Expected ResultStatement, got int instead.', 1646321756);
        }

        if (\method_exists($result, 'fetchOne')) {
            $count = (int)$result->fetchOne();
        } else {
            $count = (int)$result->fetchColumn();
        }

        return $count;
    }

    /**
     * Checks whether there is a dummy record in the table given by the first
     * parameter $table that has the given UID.
     *
     * @param non-empty-string $table the name of the table to query
     * @param int $uid the UID of the record to look up, must be > 0
     *
     * @return bool
     */
    public function existsRecordWithUid(string $table, int $uid): bool
    {
        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        if ($table === '') {
            throw new \InvalidArgumentException('$table must not be empty.', 1569785503);
        }
        if ($uid <= 0) {
            throw new \InvalidArgumentException('$uid must be > 0.', 1331490872);
        }

        $this->initializeDatabase();
        if (!$this->isTableNameAllowed($table)) {
            throw new \InvalidArgumentException(
                'The given table name is not in the list of allowed tables.',
                1569785708
            );
        }

        $dummyColumn = $this->getDummyColumnName($table);
        $queryResult = $this->getConnectionForTable($table)
            ->select(['*'], $table, ['uid' => $uid, $dummyColumn => 1]);
        if (\method_exists($queryResult, 'fetchAllAssociative')) {
            $data = $queryResult->fetchAllAssociative();
        } else {
            $data = $queryResult->fetchAll();
        }

        return $data !== [];
    }

    /**
     * Eagerly resets the auto increment value for a given table to the highest
     * existing UID + 1.
     *
     * @param non-empty-string $table the name of the table on which we're going to reset the auto increment entry
     *
     * @throws \InvalidArgumentException
     *
     * @see resetAutoIncrementLazily
     */
    public function resetAutoIncrement(string $table): void
    {
        $this->initializeDatabase();

        if (!$this->isTableNameAllowed($table)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1331490882
            );
        }

        // Checks whether the current table qualifies for this method. If there
        // is no column "uid" that has the "auto_increment" flag set, we should
        // not try to reset this inexistent auto increment index to avoid DB
        // errors.
        if (!$this->tableHasColumnUid($table)) {
            return;
        }

        $newAutoIncrementValue = $this->getMaximumUidFromTable($table) + 1;

        // Updates the auto increment index for this table. The index will be
        // set to one UID above the highest existing UID.
        $connection = $this->getConnectionPool()->getConnectionByName('Default');
        $query = 'ALTER TABLE `' . $table . '` AUTO_INCREMENT=' . $newAutoIncrementValue . ';';
        if (\method_exists($connection, 'executeQuery')) {
            $connection->executeQuery($query);
        } else {
            $connection->query($query);
        }
    }

    /**
     * Resets the auto increment value for a given table to the highest existing
     * UID + 1 if the current auto increment value is higher than a certain
     * threshold over the current maximum UID.
     *
     * The threshold is 100 by default and can be set using
     * setResetAutoIncrementThreshold.
     *
     * @param non-empty-string $table the name of the table on which we're going to reset the auto increment entry
     *
     * @see resetAutoIncrement
     */
    private function resetAutoIncrementLazily(string $table): void
    {
        $this->initializeDatabase();

        if (!$this->isTableNameAllowed($table)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1331490899
            );
        }

        // Checks whether the current table qualifies for this method. If there
        // is no column "uid" that has the "auto_increment" flag set, we should
        // not try to reset this inexistent auto increment index to avoid
        // database errors.
        if (!$this->tableHasColumnUid($table)) {
            return;
        }

        if (
            $this->getAutoIncrement($table) >
            ($this->getMaximumUidFromTable($table) + $this->resetAutoIncrementThreshold)
        ) {
            $this->resetAutoIncrement($table);
        }
    }

    /**
     * Sets the threshold for resetAutoIncrementLazily.
     *
     * @param int $threshold threshold, must be > 0
     *
     * @throws \InvalidArgumentException
     *
     * @see resetAutoIncrementLazily
     */
    public function setResetAutoIncrementThreshold(int $threshold): void
    {
        if ($threshold <= 0) {
            throw new \InvalidArgumentException('$threshold must be > 0.', 1331490913);
        }

        $this->resetAutoIncrementThreshold = $threshold;
    }

    /**
     * Reads the highest UID for a database table.
     *
     * This function may only be called after that the provided table name
     * has been checked to be non-empty, valid and pointing to an existing
     * database table that has the "uid" column.
     *
     * @param non-empty-string $table the name of an existing table that has the "uid" column
     *
     * @return int the highest UID from this table, will be >= 0
     */
    private function getMaximumUidFromTable(string $table): int
    {
        $connection = $this->getConnectionForTable($table);
        $query = 'SELECT MAX(uid) AS uid FROM `' . $table . '`';
        if (\method_exists($connection, 'executeQuery')) {
            $queryResult = $connection->executeQuery($query);
        } else {
            $queryResult = $connection->query($query);
        }
        if (\method_exists($queryResult, 'fetchAllAssociative')) {
            $data = $queryResult->fetchAllAssociative();
        } else {
            $data = $queryResult->fetchAll();
        }

        return (int)$data['uid'];
    }

    /**
     * Reads the current auto increment value for a given table.
     *
     * This function is only valid for tables that actually have an auto
     * increment value.
     *
     * @param non-empty-string $table the name of the table for which the auto increment value should be retrieved
     *
     * @return int the current auto_increment value of table $table, will be > 0
     *
     * @throws \InvalidArgumentException
     */
    public function getAutoIncrement(string $table): int
    {
        $this->initializeDatabase();

        if (!$this->isTableNameAllowed($table)) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1331490926
            );
        }

        $connection = $this->getConnectionForTable($table);
        $query = 'SHOW TABLE STATUS WHERE Name = \'' . $table . '\';';
        if (\method_exists($connection, 'executeQuery')) {
            $queryResult = $connection->executeQuery($query);
        } else {
            $queryResult = $connection->query($query);
        }
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }

        $autoIncrement = $row['Auto_increment'];
        if ($autoIncrement === null) {
            throw new \InvalidArgumentException(
                'The given table name is invalid. This means it is either empty or not in the list of allowed tables.',
                1416849363
            );
        }

        return (int)$autoIncrement;
    }

    /**
     * Puts one or multiple table names on the list of dirty tables (which
     * represents a list of tables that were used for testing and contain dummy
     * records and thus are called "dirty" until the next cleanup).
     *
     * @param non-empty-string $tableNames the table name or a comma-separated list of table names
     *        to put on the list of dirty tables
     *
     * @throws \InvalidArgumentException
     */
    public function markTableAsDirty(string $tableNames): void
    {
        $this->initializeDatabase();

        /** @var non-empty-string $currentTable */
        foreach (GeneralUtility::trimExplode(',', $tableNames, true) as $currentTable) {
            if ($this->isNoneSystemTableNameAllowed($currentTable)) {
                $this->dirtyTables[$currentTable] = $currentTable;
            } elseif ($this->isSystemTableNameAllowed($currentTable)) {
                $this->dirtySystemTables[$currentTable] = $currentTable;
            } else {
                throw new \InvalidArgumentException(
                    'The table name "' . $currentTable . '" is not allowed for markTableAsDirty.',
                    1331490947
                );
            }
        }
    }

    /**
     * Returns the next sorting value of the relation table which should be used.
     *
     * Note: This function does not take already existing relations in the
     * database - which were created without using the testing framework - into
     * account. So you always should create new dummy records and create a
     * relation between these two dummy records, so you're sure there aren't
     * already relations for a local UID in the database.
     *
     * @see https://bugs.oliverklee.com/show_bug.cgi?id=1423
     *
     * @param non-empty-string $table the relation table
     * @param int $uidLocal UID of the local table, must be > 0
     *
     * @return int the next sorting value to use (> 0)
     */
    private function getRelationSorting(string $table, int $uidLocal): int
    {
        if (!isset($this->relationSorting[$table][$uidLocal])) {
            $this->relationSorting[$table][$uidLocal] = 0;
        }

        $this->relationSorting[$table][$uidLocal]++;

        return $this->relationSorting[$table][$uidLocal];
    }

    /**
     * Updates an integer field of a database table by one. This is mainly needed
     * for counting up the relation counter when creating a database relation.
     *
     * The field to update must be of type int.
     *
     * @param non-empty-string $tableName name of the table
     * @param int $uid the UID of the record to modify, must be > 0
     * @param non-empty-string $fieldName the field name of the field to modify
     *
     * @throws \InvalidArgumentException
     * @throws \BadMethodCallException
     */
    private function increaseRelationCounter(string $tableName, int $uid, string $fieldName): void
    {
        if (!$this->isTableNameAllowed($tableName)) {
            throw new \InvalidArgumentException(
                'The table name "' . $tableName .
                '" is invalid. This means it is either empty or not in the list of allowed tables.',
                1331490960
            );
        }
        if (!$this->tableHasColumn($tableName, $fieldName)) {
            throw new \InvalidArgumentException(
                'The table ' . $tableName . ' has no column ' . $fieldName . '.',
                1331490986
            );
        }

        $connection = $this->getConnectionForTable($tableName);
        $query = 'UPDATE ' . $tableName . ' SET ' . $fieldName . '=' . $fieldName . '+1 WHERE uid=' . $uid;
        if (\method_exists($connection, 'executeQuery')) {
            $queryResult = $connection->executeQuery($query);
        } else {
            $queryResult = $connection->query($query);
        }
        $numberOfAffectedRows = $queryResult->rowCount();
        if ($numberOfAffectedRows === 0) {
            throw new \BadMethodCallException(
                'The table ' . $tableName . ' does not contain a record with UID ' . $uid . '.',
                1331491003
            );
        }

        $this->markTableAsDirty($tableName);
    }

    /**
     * Gets all hooks for this class.
     *
     * @return array<int, object> the hook objects, will be empty if no hooks have been set
     */
    private function getHooks(): array
    {
        if (self::$hooksHaveBeenRetrieved) {
            return self::$hooks;
        }

        /** @var array<array-key, class-string> $hookClasses */
        $hookClasses = (array)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'] ?? []);
        foreach ($hookClasses as $hookClass) {
            self::$hooks[] = GeneralUtility::makeInstance($hookClass);
        }

        self::$hooksHaveBeenRetrieved = true;

        return self::$hooks;
    }

    /**
     * Purges the cached hooks.
     */
    public function purgeHooks(): void
    {
        self::$hooks = [];
        self::$hooksHaveBeenRetrieved = false;
    }

    /**
     * Returns the current front-end instance.
     *
     * This method must only be called when there is a front-end instance.
     *
     * @return TypoScriptFrontendController
     */
    private function getFrontEndController(): TypoScriptFrontendController
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Sets all Core caches to the `NullBackend`, except for: assets, core, di.
     *
     * @deprecated will be removed in oelib 5.0
     */
    public function disableCoreCaches(): void
    {
        $this->cacheNullifyer->setAllCoreCaches();
    }
}
