<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\ReadOnlyModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\AbstractDataMapper
 * @covers \OliverKlee\Oelib\Model\AbstractModel
 *
 * @phpstan-type DatabaseColumn string|int|float|bool|null
 * @phpstan-type DatabaseRow array<string, DatabaseColumn>
 */
final class AbstractDataMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = MapperRegistry::get(TestingMapper::class);
    }

    protected function tearDown(): void
    {
        MapperRegistry::purgeInstance();
        parent::tearDown();
    }

    // Tests concerning usage with the testing framework

    /**
     * @test
     */
    public function cleanUpAfterSaveRemovesCreatedRecord(): void
    {
        $testingFramework = new TestingFramework('tx_oelib');
        $this->subject->setTestingFramework($testingFramework);

        $model = new TestingModel();
        $model->setTitle('New and fresh');
        $this->subject->save($model);
        $testingFramework->cleanUp();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(0, $connection->count('*', 'tx_oelib_test', ['uid' => $model->getUid()]));
    }

    /**
     * @test
     */
    public function cleanUpAfterSaveRemovesAssociationTableEntriesRecord(): void
    {
        $testingFramework = new TestingFramework('tx_oelib');
        $this->subject->setTestingFramework($testingFramework);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $leftUid = (int)$connection->lastInsertId('tx_oelib_test');

        $rightModel = new TestingModel();
        $rightModel->setData([]);
        $rightModel->setTitle('right model');

        $leftModel = $this->subject->find($leftUid);
        $leftModel->addRelatedRecord($rightModel);
        $this->subject->save($leftModel);
        $testingFramework->cleanUp();

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            0,
            $relationConnection->count('*', 'tx_oelib_test_article_mm', ['uid_local' => $leftUid])
        );
    }

    // Tests concerning load

    /**
     * @test
     */
    public function loadWithModelWithExistingUidFillsModelWithData(): void
    {
        $title = 'Assassin of Kings';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => $title]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertSame($title, $model->getTitle());
    }

    // Tests concerning find

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsModelDataFromDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame('foo', $model->getTitle());
    }

    //////////////////////////////
    // Tests concerning getModel
    //////////////////////////////

    /**
     * @test
     */
    public function getModelForNonMappedUidReturnsModelInstance(): void
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getModel(['uid' => 2])
        );
    }

    /**
     * @test
     */
    public function getModelForNonMappedUidReturnsLoadedModel(): void
    {
        self::assertTrue(
            $this->subject->getModel(['uid' => 2])->isLoaded()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfGhostReturnsModelInstance(): void
    {
        $mappedUid = $this->subject->getNewGhost()->getUid();

        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getModel(['uid' => $mappedUid])
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfGhostReturnsLoadedModel(): void
    {
        $mappedUid = $this->subject->getNewGhost()->getUid();

        self::assertTrue(
            $this->subject->getModel(['uid' => $mappedUid])->isLoaded()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfGhostReturnsLoadedModelWithTheProvidedData(): void
    {
        $mappedModel = $this->subject->getNewGhost();

        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => $mappedModel->getUid(), 'title' => 'new title']);
        self::assertSame(
            'new title',
            $model->getTitle()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfGhostReturnsThatModel(): void
    {
        $mappedModel = $this->subject->getNewGhost();

        self::assertSame(
            $mappedModel,
            $this->subject->getModel(['uid' => $mappedModel->getUid()])
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfLoadedModelReturnsThatModelInstance(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->setData(['title' => 'foo']);

        self::assertSame(
            $mappedModel,
            $this->subject->getModel(['uid' => $mappedModel->getUid()])
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfLoadedModelAndNoNewDataProvidedReturnsModelWithTheInitialData(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->setData(['title' => 'foo']);

        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => $mappedModel->getUid()]);
        self::assertSame(
            'foo',
            $model->getTitle()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfLoadedModelAndNewDataProvidedReturnsModelWithTheInitialData(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->setData(['title' => 'foo']);

        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => $mappedModel->getUid(), 'title' => 'new title']);
        self::assertSame(
            'foo',
            $model->getTitle()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfDeadModelReturnsDeadModel(): void
    {
        $mappedModel = $this->subject->getNewGhost();
        $mappedModel->markAsDead();

        self::assertTrue(
            $this->subject->getModel(['uid' => $mappedModel->getUid()])->isDead()
        );
    }

    /**
     * @test
     */
    public function getModelForNonMappedUidReturnsModelWithChildrenList(): void
    {
        /** @var TestingModel $model */
        $model = $this->subject->getModel(['uid' => 2]);
        self::assertInstanceOf(
            Collection::class,
            $model->getChildren()
        );
    }

    /**
     * @test
     */
    public function getModelSavesModelToCacheByKeys(): void
    {
        $model = $this->subject->getModel(['uid' => 2]);

        self::assertSame(
            [$model],
            $this->subject->getCachedModels()
        );
    }

    /////////////////////////////////////
    // Tests concerning getListOfModels
    /////////////////////////////////////

    /**
     * @test
     */
    public function getListOfModelsReturnsInstanceOfList(): void
    {
        self::assertInstanceOf(
            Collection::class,
            $this->subject->getListOfModels([['uid' => 1]])
        );
    }

    /**
     * @test
     */
    public function getListOfModelsForAnEmptyArrayProvidedReturnsEmptyList(): void
    {
        self::assertTrue(
            $this->subject->getListOfModels([])->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getListOfModelsForOneRecordsProvidedReturnsListWithOneElement(): void
    {
        self::assertCount(1, $this->subject->getListOfModels([['uid' => 1]]));
    }

    /**
     * @test
     */
    public function getListOfModelsForTwoRecordsProvidedReturnsListWithTwoElements(): void
    {
        self::assertCount(2, $this->subject->getListOfModels([['uid' => 1], ['uid' => 2]]));
    }

    /**
     * @test
     */
    public function getListOfModelsReturnsListOfModelInstances(): void
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getListOfModels([['uid' => 1]])->current()
        );
    }

    /**
     * @test
     */
    public function getListOfModelsReturnsListOfModelWithProvidedTitle(): void
    {
        /** @var Collection<TestingModel> $models */
        $models = $this->subject->getListOfModels([['uid' => 1, 'title' => 'foo']]);

        /** @var TestingModel $current */
        $current = $models->current();
        self::assertSame('foo', $current->getTitle());
    }

    // Tests concerning load and reload

    /**
     * @test
     */
    public function loadWithModelWithExistingUidOfHiddenRecordMarksModelAsLoaded(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertTrue(
            $model->isLoaded()
        );
    }

    /**
     * @test
     */
    public function loadForModelWithExistingUidMarksModelAsClean(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertFalse(
            $model->isDirty()
        );
    }

    /**
     * @test
     */
    public function loadCanReadFloatDataFromFloatColumn(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['float_data' => 12.5]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertSame(
            12.5,
            $model->getFloatFromFloatData()
        );
    }

    /**
     * @test
     */
    public function loadCanReadFloatDataFromDecimalColumn(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['decimal_data' => 12.5]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertSame(
            12.5,
            $model->getFloatFromDecimalData()
        );
    }

    /**
     * @test
     */
    public function loadCanReadFloatDataFromStringColumn(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['string_data' => '12.5']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertSame(
            12.5,
            $model->getFloatFromStringData()
        );
    }

    /**
     * @test
     */
    public function reloadCanLoadGhostFromDisk(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue($model->isGhost());

        $newTitle = 'bar';
        $connection->update('tx_oelib_test', ['title' => $newTitle], ['uid' => $uid]);

        $this->subject->reload($model);

        self::assertSame($newTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function reloadCanReloadCleanLoadedModelFromDisk(): void
    {
        $oldTitle = 'foo';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => $oldTitle]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($oldTitle, $model->getTitle());
        self::assertTrue($model->isLoaded());

        $newTitle = 'bar';
        $connection->update('tx_oelib_test', ['title' => $newTitle], ['uid' => $uid]);

        $this->subject->reload($model);

        self::assertSame($newTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function reloadCanReloadDirtyModelFromDisk(): void
    {
        $oldTitle = 'foo';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => '']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle($oldTitle);
        self::assertTrue($model->isLoaded());
        self::assertTrue($model->isDirty());

        $newTitle = 'bar';
        $connection->update('tx_oelib_test', ['title' => $newTitle], ['uid' => $uid]);

        $this->subject->reload($model);

        self::assertSame($newTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function reloadWithModelWithInexistentUidMarksModelAsDead(): void
    {
        $model = new TestingModel();
        $model->setUid(1);
        $this->subject->reload($model);

        self::assertTrue($model->isDead());
    }

    //////////////////////////////////////
    // Tests concerning the model states
    //////////////////////////////////////

    /**
     * @test
     */
    public function findAndAccessingDataLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->getTitle();

        self::assertTrue(
            $model->isLoaded()
        );
    }

    /**
     * @test
     */
    public function isHiddenOnGhostInDatabaseLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = $this->subject->find($uid);
        $model->isHidden();

        self::assertTrue(
            $model->isLoaded()
        );
    }

    /**
     * @test
     */
    public function isHiddenOnGhostNotInDatabaseThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->find(1)->isHidden();
    }

    /**
     * @test
     */
    public function loadWithModelWithExistingUidLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertTrue(
            $model->isLoaded()
        );
    }

    /**
     * @test
     */
    public function loadWithModelWithInexistentUidMarksModelAsDead(): void
    {
        $model = new TestingModel();
        $model->setUid(1);
        $this->subject->load($model);

        self::assertTrue(
            $model->isDead()
        );
    }

    /////////////////////////////////
    // Tests concerning existsModel
    /////////////////////////////////

    /**
     * @test
     */
    public function existsModelForUidOfLoadedModelReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForUidOfNotLoadedModelInDatabaseReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        self::assertTrue(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForInexistentUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->existsModel(1)
        );
    }

    /**
     * @test
     */
    public function existsModelForGhostModelWithInexistentUidReturnsFalse(): void
    {
        $uid = 1;
        $this->subject->find($uid);

        self::assertFalse(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistingUidLoadsModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->existsModel($uid);

        self::assertTrue(
            $this->subject->find($uid)->isLoaded()
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfHiddenRecordReturnsFalse(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        self::assertFalse(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfHiddenRecordAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenNotBeingAllowedReturnsFalse(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->load($this->subject->find($uid));

        self::assertFalse(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfLoadedNonHiddenRecordAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 0]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfHiddenAfterLoadingAsNonHiddenAndHiddenBeingAllowedReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    ///////////////////////////////////////////
    // Tests concerning getLoadedTestingModel
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function getLoadedTestingModelReturnsModel(): void
    {
        $this->subject->disableDatabaseAccess();

        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getLoadedTestingModel([])
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelReturnsLoadedModel(): void
    {
        $this->subject->disableDatabaseAccess();

        self::assertTrue(
            $this->subject->getLoadedTestingModel([])->isLoaded()
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelReturnsModelWithUid(): void
    {
        $this->subject->disableDatabaseAccess();

        self::assertTrue(
            $this->subject->getLoadedTestingModel([])->hasUid()
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelCreatesRegisteredModel(): void
    {
        $this->subject->disableDatabaseAccess();
        $model = $this->subject->getLoadedTestingModel([]);

        self::assertSame(
            $model,
            $this->subject->find($model->getUid())
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelSetsTheProvidedData(): void
    {
        $this->subject->disableDatabaseAccess();

        /** @var TestingModel $model */
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'foo']
        );

        self::assertSame(
            'foo',
            $model->getTitle()
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelCreatesRelations(): void
    {
        $this->subject->disableDatabaseAccess();

        $relatedModel = $this->subject->getNewGhost();
        $model = $this->subject->getLoadedTestingModel(
            ['friend' => $relatedModel->getUid()]
        );

        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($relatedModel->getUid(), $friend->getUid());
    }

    /////////////////////////////////////////////
    // Tests concerning the foreign key mapping
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function relatedRecordWithZeroUidIsNull(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertNull(
            $model->getFriend()
        );
    }

    /**
     * @test
     */
    public function relatedRecordWithExistingUidReturnsRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');

        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = $this->subject->find($uid);

        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($friendUid, $friend->getUid());
    }

    /**
     * @test
     */
    public function relatedRecordWithRelationToSelfReturnsSelf(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->update('tx_oelib_test', ['friend' => $uid], ['uid' => $uid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getFriend()
        );
    }

    /**
     * @test
     */
    public function relatedRecordWithExistingUidCanReturnOtherModelType(): void
    {
        $usersConnection = $this->getConnectionPool()->getConnectionForTable('fe_users');
        $usersConnection->insert('fe_users', []);
        $ownerUid = (int)$usersConnection->lastInsertId('fe_users');

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['owner' => $ownerUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertInstanceOf(
            FrontEndUser::class,
            $model->getOwner()
        );
    }

    /**
     * @test
     */
    public function relatedRecordWithExistingUidReturnsRelatedRecordThatCanBeLoaded(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');

        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = $this->subject->find($uid);
        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        $friend->getTitle();

        self::assertTrue($friend->isLoaded());
    }

    /**
     * @test
     */
    public function relatedRecordWithInexistentUidReturnsRelatedRecordAsGhost(): void
    {
        $friendUid = 2;
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = $this->subject->find($uid);

        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($friendUid, $friend->getUid());
    }

    // Tests concerning the m:n mapping with a comma-separated list of UIDs

    /**
     * @test
     */
    public function commaSeparatedRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getChildren()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');

        $connection->insert('tx_oelib_test', ['children' => $childUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$childUid,
            $model->getChildren()->getUids()
        );
    }

    /**
     * @test
     */
    public function commaSeparatedRelationsWithTwoUidsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');

        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $childUid1 . ',' . $childUid2,
            $model->getChildren()->getUids()
        );
    }

    /**
     * @test
     */
    public function commaSeparatedRelationsWithOneUidAndZeroIgnoresZero(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',0']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$childUid1,
            $model->getChildren()->getUids()
        );
    }

    /**
     * @test
     */
    public function commaSeparatedRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getChildren()->getParentModel()
        );
    }

    /**
     * @test
     */
    public function commaSeparatedRelationIsNotOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertFalse(
            $model->getChildren()->isRelationOwnedByParent()
        );
    }

    ////////////////////////////////////////////////////////
    // Tests concerning the m:n mapping using an m:n table
    ////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function mnRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getRelatedRecords()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$relatedUid,
            $model->getRelatedRecords()->getUids()
        );
    }

    /**
     * @test
     */
    public function mnRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid1]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid2]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid1 . ',' . $relatedUid2,
            $model->getRelatedRecords()->getUids()
        );
    }

    /**
     * @test
     */
    public function mnRelationsReturnsListSortedBySorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert(
            'tx_oelib_test_article_mm',
            ['uid_local' => $uid, 'uid_foreign' => $relatedUid1, 'sorting' => 2]
        );
        $relationConnection->insert(
            'tx_oelib_test_article_mm',
            ['uid_local' => $uid, 'uid_foreign' => $relatedUid2, 'sorting' => 1]
        );

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid2 . ',' . $relatedUid1,
            $model->getRelatedRecords()->getUids()
        );
    }

    /**
     * @test
     */
    public function mnRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getRelatedRecords()->getParentModel()
        );
    }

    /**
     * @test
     */
    public function mnRelationIsNotOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertFalse(
            $model->getRelatedRecords()->isRelationOwnedByParent()
        );
    }

    ///////////////////////////////////////////////////////////////////////
    // Tests concerning the bidirectional m:n mapping using an m:n table.
    ///////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function bidirectionalMNRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getBidirectional()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame(
            (string)$uid,
            $model->getBidirectional()->getUids()
        );
    }

    /**
     * @test
     */
    public function bidirectionalMNRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid1, 'uid_foreign' => $relatedUid]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid2, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame(
            $uid1 . ',' . $uid2,
            $model->getBidirectional()->getUids()
        );
    }

    /**
     * @test
     */
    public function bidirectionalMNRelationsReturnsListSortedByUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid1, 'uid_foreign' => $relatedUid]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid2, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame(
            $uid2 . ',' . $uid1,
            $model->getBidirectional()->getUids()
        );
    }

    /**
     * @test
     */
    public function bidirectionalMnRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getBidirectional()->getParentModel()
        );
    }

    /**
     * @test
     */
    public function bidirectionalMnRelationIsNotOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertFalse(
            $model->getBidirectional()->isRelationOwnedByParent()
        );
    }

    ////////////////////////////////////////////////////////////
    // Tests concerning the 1:n mapping using a foreign field.
    ////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function oneToManyRelationsWithEmptyStringCreatesEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getComposition()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid]);
        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            (string)$relatedUid,
            $model->getComposition()->getUids()
        );
    }

    /**
     * @test
     */
    public function oneToManyRelationsCanSortByForeignSortBy(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'b']);
        $relatedUid1 = (int)$relationConnection->lastInsertId('tx_oelib_test');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'a']);
        $relatedUid2 = (int)$relationConnection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($relatedUid2 . ',' . $relatedUid1, $model->getComposition()->getUids());
    }

    /**
     * @test
     */
    public function oneToManyRelationsCanSortByForeignDefaultSortBy(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition2' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_testchild', ['tx_oelib_parent2' => $uid, 'title' => 'b']);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_testchild', ['tx_oelib_parent2' => $uid, 'title' => 'a']);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($relatedUid2 . ',' . $relatedUid1, $model->getComposition2()->getUids());
    }

    /**
     * @test
     */
    public function oneToManyRelationWithoutSortingDoesNotCrash(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition_without_sorting' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['tx_oelib_parent3' => $uid]);
        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame((string)$relatedUid, $model->getCompositionWithoutSorting()->getUids());
    }

    /**
     * @test
     */
    public function oneToManyRelationsWithOneRelatedModelNotLoadsDeletedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'deleted' => 1]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertTrue($model->getComposition()->isEmpty());
    }

    /**
     * @test
     */
    public function oneToManyRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation A']);
        $relatedUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation B']);
        $relatedUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid1 . ',' . $relatedUid2,
            $model->getComposition()->getUids()
        );
    }

    /**
     * @test
     */
    public function oneToManyRelationsReturnsListSortedByForeignSortBy(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation B']);
        $relatedUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => 'relation A']);
        $relatedUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $relatedUid2 . ',' . $relatedUid1,
            $model->getComposition()->getUids()
        );
    }

    /**
     * @test
     */
    public function oneToManyRelationHasParentModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertSame(
            $model,
            $model->getComposition()->getParentModel()
        );
    }

    /**
     * @test
     */
    public function oneToManyRelationIsOwnedByParent(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertTrue(
            $model->getComposition()->isRelationOwnedByParent()
        );
    }

    // Tests concerning n:1 association mapping

    /**
     * @test
     */
    public function relatedRecordWithExistingUidReturnsRelatedRecordWithData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $friendTitle = 'Brianna';
        $connection->insert('tx_oelib_test', ['title' => $friendTitle]);
        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = $this->subject->find($uid);

        self::assertInstanceOf(TestingModel::class, $model);
        $friend = $model->getFriend();
        self::assertInstanceOf(TestingModel::class, $friend);
        self::assertSame($friendTitle, $friend->getTitle());
    }

    // Tests concerning the m:n mapping with a comma-separated list of UIDs

    /**
     * @test
     */
    public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModelWithData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $childTitle = 'Abraham';
        $connection->insert('tx_oelib_test', ['title' => $childTitle]);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['children' => (string)$childUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChild */
        $firstChild = $model->getChildren()->first();
        self::assertSame($childTitle, $firstChild->getTitle());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function silentlyIgnoresCommaSeparatedOneToManyRelationWithZeroForeignUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['children' => '0']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        // load any property to trigger loading the data
        $model->getTitle();
    }

    // Tests concerning the m:n mapping using an m:n table

    /**
     * @test
     */
    public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData(): void
    {
        $relatedTitle = 'Geralt of Rivia';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => $relatedTitle, 'bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstRelatedModel */
        $firstRelatedModel = $model->getRelatedRecords()->first();
        self::assertSame($relatedTitle, $firstRelatedModel->getTitle());
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function silentlyIgnoresManyToManyRelationWithZeroForeignUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => 0]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        // load any property to trigger loading the data
        $model->getTitle();
    }

    // Tests concerning the bidirectional m:n mapping using an m:n table.

    /**
     * @test
     */
    public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame((string)$uid, $model->getBidirectional()->getUids());
    }

    // Tests concerning the 1:n mapping using a foreign field.

    /**
     * @test
     */
    public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData(): void
    {
        $relatedTitle = 'Triss Merrigold';
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid, 'title' => $relatedTitle]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChildModel */
        $firstChildModel = $model->getComposition()->first();
        self::assertSame($relatedTitle, $firstChildModel->getTitle());
    }

    // Tests concerning findSingleByWhereClause().

    /**
     * @test
     */
    public function findSingleByWhereClauseWithUidOfInexistentRecordThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findSingleByWhereClause(
            ['uid' => 1]
        );
    }

    /**
     * @test
     */
    public function findSingleByWhereClauseWithUidOfExistentNotMappedRecordReturnsModelWithTheData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);

        /** @var TestingModel $model */
        $model = $this->subject->findSingleByWhereClause(['title' => 'foo']);
        self::assertSame(
            'foo',
            $model->getTitle()
        );
    }

    /**
     * @test
     */
    public function findSingleByWhereClauseWithUidOfExistentYetMappedRecordReturnsModelWithTheMappedData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid);
        $model1->setTitle('bar');

        /** @var TestingModel $model2 */
        $model2 = $this->subject->findSingleByWhereClause(['title' => 'foo']);
        self::assertSame(
            'bar',
            $model2->getTitle()
        );
    }

    //////////////////////////////////////////////
    // Tests concerning disabled database access
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function loadWithUidOfRecordInDatabaseAndDatabaseAccessDisabledMarksModelAsDead(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->disableDatabaseAccess();
        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->find($uid)->isDead()
        );
    }

    /**
     * @test
     */
    public function loadWithUidOfRecordNotInDatabaseAndDatabaseAccessDisabledMarksModelAsDead(): void
    {
        $uid = 1;

        $this->subject->disableDatabaseAccess();
        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->find($uid)->isDead()
        );
    }

    ////////////////////////////
    // Tests concerning save()
    ////////////////////////////

    /**
     * @test
     */
    public function saveForReadOnlyModelDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->setModelClassName(ReadOnlyModel::class);
        $this->subject->save($this->subject->find($uid));

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function saveForDatabaseAccessDeniedDoesNotCommitDirtyLoadedModelToDatabase(): void
    {
        $this->subject->disableDatabaseAccess();

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function saveForGhostDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $this->subject->save($this->subject->find($uid));

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function saveForDeadModelDoesNotCommitDirtyModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->markAsDead();
        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function saveForCleanLoadedModelDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->markAsClean();
        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidCommitsModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar'])
        );
    }

    /**
     * @return array<string, array<int, string|float|int>>
     */
    public function dataTypeDataProvider(): array
    {
        return [
            'string' => ['title', 'the title'],
            'float as float' => ['float_data', 3.5],
            'float as decimal' => ['decimal_data', '3.500'],
            'float as string' => ['string_data', '3.5'],
            'boolean true' => ['bool_data1', 1],
            'boolean false' => ['bool_data2', 0],
            'int' => ['int_data', 42],
        ];
    }

    /**
     * @test
     *
     * @param string $propertyName
     * @param mixed $expectedValue
     *
     * @dataProvider dataTypeDataProvider
     */
    public function savePersistsAllBasicDataTypes(string $propertyName, $expectedValue): void
    {
        $model = new TestingModel();
        $model->setData(
            [
                'title' => 'the title',
                'float_data' => 3.5,
                'decimal_data' => 3.5,
                'string_data' => 3.5,
                'bool_data1' => true,
                'bool_data2' => false,
                'int_data' => 42,
            ]
        );

        $this->subject->save($model);

        $uid = $model->getUid();

        $result = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test')
            ->select(['*'], 'tx_oelib_test', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $data */
            $data = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $data */
            $data = $result->fetch();
        }

        self::assertIsArray($data);
        self::assertSame($expectedValue, $data[$propertyName]);
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidDoesNotChangeTheUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            $uid,
            $model->getUid()
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidSetsTimestamp(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidAndWithoutDataCommitsModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $model = new TestingModel();
        $model->setUid($uid);
        $model->setData([]);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function saveNewModelFromMemoryAndMapperInTestingModeMarksModelAsDummyModel(): void
    {
        $model = new TestingModel();
        $model->setData(['title' => 'foo']);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo'])
        );
    }

    /**
     * @test
     */
    public function saveNewModelFromMemoryRegistersModelInMapper(): void
    {
        $model = new TestingModel();
        $model->setData(['title' => 'foo']);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            $model,
            $this->subject->find($model->getUid())
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSaveForDirtyLoadedModelWithUidReturnsFalse(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertFalse(
            $this->subject->find($uid)->isDirty()
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidAndWithoutRelationsCommitsModelToDatabase(): void
    {
        $model = new TestingModel();
        $model->setData(['title' => 'bar']);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidAndWithRelationsCommitsModelToDatabase(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar'])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidAddsModelToMapAfterSave(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            $model,
            $this->subject->find($model->getUid())
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidSetsUidForModel(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertTrue(
            $model->hasUid()
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidSetsUidReceivedFromDatabaseForModel(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['uid' => $model->getUid()])
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSaveForDirtyLoadedModelWithoutUidReturnsFalse(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertFalse(
            $model->isDirty()
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidSetsTimestamp(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidSetsCreationDate(): void
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'crdate' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithNoDataDoesNotCommitModelToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );

        $model = $this->subject->find($uid);
        $model->markAsDirty();
        $this->subject->save($model);

        self::assertSame(
            0,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'tstamp' => $GLOBALS['SIM_EXEC_TIME']])
        );
    }

    /**
     * @test
     */
    public function isDeadAfterSaveForDirtyLoadedModelWithDeletedFlagSetReturnsTrue(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->setToDeleted();
        $this->subject->save($model);

        self::assertTrue(
            $this->subject->find($uid)->isDead()
        );
    }

    /**
     * @test
     */
    public function saveForModelWithN1RelationSavesUidOfRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'friend' => $friendUid])
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNCommaSeparatedRelationSavesUidList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'children' => $childUid1 . ',' . $childUid2])
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNTableRelationSavesNumberOfRelatedRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['related_records' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid1]);
        $relationConnection->insert('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid2]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'related_records' => 2])
        );
    }

    /**
     * @test
     */
    public function saveForModelWithOneToManyRelationSavesNumberOfRelatedRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $composition = $model->getComposition();
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', []);
        $childUid1 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', []);
        $childUid2 = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        $composition->add($mapper->find($childUid1));
        $composition->add($mapper->find($childUid2));

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'bar', 'composition' => 2])
        );
    }

    /**
     * @test
     */
    public function saveForModelWithOneToManyRelationSavesDirtyRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $composition = $model->getComposition();
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', []);
        $childUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');
        $component = $mapper->find($childUid);
        $composition->add($component);

        $this->subject->save($model);

        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $component->getUid(), 'parent' => $model->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWith1NRelationSavesFirstNewRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $component = new TestingChildModel();
        $component->markAsDummyModel();
        $model->getComposition()->add($component);

        $this->subject->save($model);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $component->getUid(), 'parent' => $model->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWith1NRelationSavesSecondNewRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $newComponent1 = new TestingChildModel();
        $newComponent1->markAsDummyModel();
        $model->getComposition()->add($newComponent1);

        $newComponent2 = new TestingChildModel();
        $newComponent2->markAsDummyModel();
        $model->getComposition()->add($newComponent2);

        $this->subject->save($model);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $newComponent2->getUid(), 'parent' => $model->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWith1NRelationSavesNewRelatedRecordWithPrefixInForeignKey(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $component = new TestingChildModel();
        $component->markAsDummyModel();
        $model->getComposition2()->add($component);

        $this->subject->save($model);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_testchild',
                ['uid' => $component->getUid(), 'tx_oelib_parent2' => $model->getUid()]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithOneToManyRelationDeletesUnconnectedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);
        $model->markAsDirty();

        $composition = $model->getComposition();
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid1 = (int)$relationConnection->lastInsertId('tx_oelib_test');
        $component1 = $mapper->find($childUid1);
        $composition->add($component1);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid2 = (int)$relationConnection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $component2 */
        $component2 = $mapper->find($childUid2);

        $this->subject->save($model);

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_testchild WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $relationConnection->executeQuery($query, ['uid' => $component2->getUid(), 'deleted' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
    }

    /**
     * @test
     */
    public function saveForModelWithN1RelationSavesDirtyRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $friendUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        /** @var TestingModel $friend */
        $friend = $this->subject->find($friendUid);
        $friend->setTitle('foo');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'uid' => $friendUid])
        );
    }

    /**
     * @test
     */
    public function saveForModelWithN1RelationSavesNewRelatedRecord(): void
    {
        $friend = new TestingModel();
        $friend->markAsDummyModel();
        $friend->setTitle('foo');

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setFriend($friend);

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['uid' => $friend->getUid()])
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNCommaSeparatedRelationSavesDirtyRelatedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid1);
        $child->setTitle('foo');

        $this->subject->save($model);

        self::assertSame(
            1,
            $connection->count('*', 'tx_oelib_test', ['title' => 'foo', 'uid' => $childUid1])
        );
    }

    /**
     * @test
     */
    public function saveAddsModelToCache(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        $cachedModels = $this->subject->getCachedModels();
        self::assertSame(
            $model->getUid(),
            $cachedModels[0]->getUid()
        );
    }

    /**
     * @test
     */
    public function addModelToListMarksParentModelAsDirty(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();

        $parent->getChildren()->add($child);

        self::assertTrue(
            $parent->isDirty()
        );
    }

    /**
     * @test
     */
    public function appendListMarksParentModelAsDirty(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();
        /** @var Collection<TestingModel> $list */
        $list = new Collection();
        $list->add($child);

        $parent->getChildren()->append($list);

        self::assertTrue(
            $parent->isDirty()
        );
    }

    /**
     * @test
     */
    public function purgeModelFromListMarksModelAsDirty(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();
        $parent->getChildren()->add($child);
        $parent->getChildren()->rewind();

        $parent->getChildren()->purgeCurrent();

        self::assertTrue(
            $parent->isDirty()
        );
    }

    // Tests concerning save

    /**
     * @test
     */
    public function saveForModelWithMNTableRelationCreatesIntermediateRelationRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->find($childUid);

        $parent->getRelatedRecords()->add($child);
        $this->subject->save($parent);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid, 'uid_foreign' => $childUid, 'sorting' => 0]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNTableRelationsCreatesIntermediateRelationRecordAndIncrementsSorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid2 = (int)$connection->lastInsertId('tx_oelib_test');

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child1 = $this->subject->find($childUid1);
        $child2 = $this->subject->find($childUid2);

        $parent->getRelatedRecords()->add($child1);
        $parent->getRelatedRecords()->add($child2);
        $this->subject->save($parent);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid, 'uid_foreign' => $childUid2, 'sorting' => 1]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');

        $parent = $this->subject->find($parentUid);
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid);

        $child->getBidirectional()->add($parent);
        $this->subject->save($child);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid, 'uid_foreign' => $childUid, 'sorting' => 0]
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecordAndIncrementsSorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $childUid = (int)$connection->lastInsertId('tx_oelib_test');

        $parent1 = $this->subject->find($parentUid1);
        $parent2 = $this->subject->find($parentUid2);
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid);

        $child->getBidirectional()->add($parent1);
        $child->getBidirectional()->add($parent2);
        $this->subject->save($child);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test_article_mm');
        self::assertSame(
            1,
            $relationConnection->count(
                '*',
                'tx_oelib_test_article_mm',
                ['uid_local' => $parentUid2, 'uid_foreign' => $childUid, 'sorting' => 1]
            )
        );
    }

    /**
     * @test
     */
    public function saveCanSaveFloatDataToFloatColumn(): void
    {
        $model = new TestingModel();
        $model->setData(['float_data' => 9.5]);
        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim((string)$row['float_data'], '0'));
    }

    /**
     * @test
     */
    public function saveCanSaveFloatDataToDecimalColumn(): void
    {
        $model = new TestingModel();
        $model->setData(['decimal_data' => 9.5]);
        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim((string)$row['decimal_data'], '0'));
    }

    /**
     * @test
     */
    public function saveCanSaveFloatDataToStringColumn(): void
    {
        $model = new TestingModel();
        $model->setData(['string_data' => 9.5]);
        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim((string)$row['string_data'], '0'));
    }

    /**
     * @return DatabaseRow
     */
    private function findRecordByUid(int $uid): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_oelib_test');
        $columns = ['float_data', 'decimal_data', 'string_data'];
        $result = $connection->select($columns, 'tx_oelib_test', ['uid' => $uid]);
        if (\method_exists($result, 'fetchAssociative')) {
            /** @var DatabaseRow|false $data */
            $data = $result->fetchAssociative();
        } else {
            /** @var DatabaseRow|false $data */
            $data = $result->fetch();
        }
        self::assertIsArray($data);

        return $data;
    }

    /////////////////////////////
    // Tests concerning findAll
    /////////////////////////////

    /**
     * @test
     */
    public function findAllForNoRecordsReturnsEmptyList(): void
    {
        self::assertTrue(
            $this->subject->findAll()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllForOneRecordInDatabaseReturnsOneRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);

        self::assertCount(1, $this->subject->findAll());
    }

    /**
     * @test
     */
    public function findAllForTwoRecordsInDatabaseReturnsTwoRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $connection->insert('tx_oelib_test', []);

        self::assertCount(2, $this->subject->findAll());
    }

    /**
     * @test
     */
    public function findAllForOneRecordInDatabaseReturnsLoadedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);

        $result = $this->subject->findAll()->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertTrue($result->isLoaded());
    }

    /**
     * @test
     */
    public function findAllIgnoresHiddenRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['hidden' => 1]);

        self::assertTrue(
            $this->subject->findAll()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllIgnoresDeletedRecord(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['deleted' => 1]);

        self::assertTrue(
            $this->subject->findAll()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllSortsRecordsBySorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findAll()->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame(\min($uid1, $uid2), $result->getUid());
    }

    /**
     * @test
     */
    public function findAllForGivenSortParameterOverridesDefaultSorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'record a']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'record b']);

        $result = $this->subject->findAll('title')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findAllForGivenSortParameterWithSortDirectionSortsResultsBySortDirection(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'record b']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'record a']);

        $result = $this->subject->findAll('title DESC')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findAllForGivenSortParameterFindsMultipleEntries(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $connection->insert('tx_oelib_test', []);

        self::assertCount(2, $this->subject->findAll('title ASC'));
    }

    // Tests concerning findByPageUid

    /**
     * @test
     */
    public function findByPageUidForPageUidZeroReturnsEntryWithZeroPageUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid(0)->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidForPageUidZeroReturnsEntryWithNonZeroPageUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 42]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid(0)->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidForPageUidEmptyReturnsRecordWithNonZeroPageUid(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 42]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid('')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidForNonZeroPageUidReturnsEntryFromThatPage(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid(1)->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidForNonZeroPageUidDoesNotReturnEntryWithDifferentPageUId(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 2]);

        self::assertTrue(
            $this->subject->findByPageUid(1)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForPageUidAndSortingGivenReturnEntrySortedBySorting(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 2, 'sorting' => 3]);
        $connection->insert('tx_oelib_test', ['pid' => 2, 'sorting' => 1]);
        $firstMatchingRecord = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid(2, 'sorting ASC')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($firstMatchingRecord, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidForTwoNonZeroPageUidsCanReturnRecordFromFirstPage(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid('1,2')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidForTwoNonZeroPageUidsCanReturnRecordFromSecondPage(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid('1,2')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidSilentlyIgnoresExtraneousCommas(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid(',1,2,,')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /**
     * @test
     */
    public function findByPageUidSilentlyIgnoresNonIntegerStrings(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['pid' => 2]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        $result = $this->subject->findByPageUid('1,2,Club-Mate')->first();

        self::assertInstanceOf(TestingModel::class, $result);
        self::assertSame($uid, $result->getUid());
    }

    /////////////////////////////////////
    // Tests concerning additional keys
    /////////////////////////////////////

    /**
     * @test
     */
    public function findByKeyFindsLoadedModel(): void
    {
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey']
        );

        self::assertSame(
            $model,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey')
        );
    }

    /**
     * @test
     */
    public function findByKeyFindsLastLoadedModelWithSameKey(): void
    {
        $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey']
        );
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey']
        );

        self::assertSame(
            $model,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey')
        );
    }

    /**
     * @test
     */
    public function findByKeyFindsSavedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('Earl Grey');
        $this->subject->save($model);

        self::assertSame(
            $model,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey')
        );
    }

    /**
     * @test
     */
    public function findByKeyFindsLastSavedModelWithSameKey(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid1);
        $model1->setTitle('Earl Grey');
        $this->subject->save($model1);

        $connection->insert('tx_oelib_test', ['title' => 'Earl Grey']);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model2 */
        $model2 = $this->subject->find($uid2);
        $model2->setTitle('Earl Grey');
        $this->subject->save($model2);

        self::assertSame(
            $model2,
            $this->subject->findOneByKeyFromCache('title', 'Earl Grey')
        );
    }

    /**
     * @test
     */
    public function findOneByKeyCanFindModelFromCache(): void
    {
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey']
        );

        self::assertSame(
            $model,
            $this->subject->findOneByKey('title', 'Earl Grey')
        );
    }

    /**
     * @test
     */
    public function findOneByKeyCanLoadModelFromDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['title' => 'Earl Grey']);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        self::assertSame(
            $uid,
            $this->subject->findOneByKey('title', 'Earl Grey')->getUid()
        );
    }

    /**
     * @test
     */
    public function findOneByKeyForInexistentThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findOneByKey('title', 'Darjeeling');
    }

    /**
     * @test
     */
    public function findByCompoundKeyFindsLoadedModel(): void
    {
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey', 'header' => 'Tea Time']
        );

        self::assertSame(
            $model,
            $this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
        );
    }

    /**
     * @test
     */
    public function findByCompoundKeyFindsLastLoadedModelWithSameCompoundKey(): void
    {
        $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey', 'header' => 'Tea Time']
        );
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey', 'header' => 'Tea Time']
        );

        self::assertSame(
            $model,
            $this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
        );
    }

    /**
     * @test
     */
    public function findByCompoundKeyFindsSavedModel(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('Earl Grey');
        $model->setHeader('Tea Time');
        $this->subject->save($model);

        self::assertSame(
            $model,
            $this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
        );
    }

    /**
     * @test
     */
    public function findByCompoundKeyFindsLastSavedModelWithSameCompoundKey(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid1);
        $model1->setTitle('Earl Grey');
        $model1->setHeader('Tea Time');
        $this->subject->save($model1);

        $connection->insert('tx_oelib_test', ['title' => 'Earl Grey', 'header' => 'Tea Time']);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model2 */
        $model2 = $this->subject->find($uid2);
        $model2->setTitle('Earl Grey');
        $model2->setHeader('Tea Time');
        $this->subject->save($model2);

        self::assertSame(
            $model2,
            $this->subject->findOneByCompoundKeyFromCache('Earl Grey.Tea Time')
        );
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyCanFindModelFromCache(): void
    {
        $model = $this->subject->getLoadedTestingModel(
            ['title' => 'Earl Grey', 'header' => 'Tea Time']
        );

        self::assertSame(
            $model,
            $this->subject->findOneByCompoundKey(['title' => 'Earl Grey', 'header' => 'Tea Time'])
        );
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyCanLoadModelFromDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert(
            'tx_oelib_test',
            ['title' => 'Earl Grey', 'header' => 'Tea Time']
        );
        $uid = (int)$connection->lastInsertId('tx_oelib_test');

        self::assertSame(
            $uid,
            $this->subject->findOneByCompoundKey(['title' => 'Earl Grey', 'header' => 'Tea Time'])->getUid()
        );
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyForNonExistentThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findOneByCompoundKey(['title' => 'Darjeeling', 'header' => 'Tea Time']);
    }

    ////////////////////////////
    // Tests concerning delete
    ////////////////////////////

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function deleteForDeadModelDoesNotThrowException(): void
    {
        $model = new TestingModel();
        $model->markAsDead();

        $this->subject->delete($model);
    }

    /**
     * @test
     */
    public function deleteForModelWithoutUidMarksModelAsDead(): void
    {
        $model = new TestingModel();

        $this->subject->delete($model);

        self::assertTrue(
            $model->isDead()
        );
    }

    /**
     * @test
     */
    public function deleteForModelWithUidMarksModelAsDead(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        self::assertTrue(
            $model->isDead()
        );
    }

    /**
     * @test
     */
    public function deleteForGhostFromGetNewGhostThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'This model is a memory-only dummy that must not be deleted.'
        );

        $model = $this->subject->getNewGhost();
        $this->subject->delete($model);
    }

    /**
     * @test
     */
    public function deleteForReadOnlyModelThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('This model is read-only and must not be deleted.');

        $model = new ReadOnlyModel();
        $this->subject->delete($model);
    }

    /**
     * @test
     */
    public function deleteForModelWithUidWritesModelAsDeletedToDatabase(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_test WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $connection->executeQuery($query, ['uid' => $uid, 'deleted' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
    }

    /**
     * @test
     */
    public function deleteForModelWithUidStillKeepsModelAccessibleViaDataMapper(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        self::assertSame(
            $model,
            $this->subject->find($uid)
        );
    }

    /**
     * @test
     */
    public function deleteForModelWithOneToManyRelationDeletesRelatedElements(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid]);
        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_testchild');

        $this->subject->delete($this->subject->find($uid));

        // We cannot use `$connection->count()` here because it automatically ignores hidden or deleted records.
        $query = 'SELECT COUNT(*) as count from tx_oelib_testchild WHERE uid = :uid AND deleted = :deleted';
        $queryResult = $relationConnection->executeQuery($query, ['uid' => $relatedUid, 'deleted' => 1]);
        if (\method_exists($queryResult, 'fetchAssociative')) {
            $row = $queryResult->fetchAssociative();
        } else {
            $row = $queryResult->fetch();
        }
        self::assertIsArray($row);
        self::assertSame(1, $row['count']);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function deleteForDirtyModelWithOneToManyRelationToDirtyElementDoesNotCrash(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $uid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $relatedModel */
        $relatedModel = $model->getComposition()->first();

        $model->setTitle('foo');
        $relatedModel->setTitle('bar');

        $this->subject->delete($model);
    }

    ///////////////////////////////////////
    // Tests concerning findAllByRelation
    ///////////////////////////////////////

    /**
     * @test
     */
    public function findAllByRelationWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$relationKey must not be empty');

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        MapperRegistry::get(TestingChildMapper::class)->findAllByRelation($model, '');
    }

    /**
     * @test
     */
    public function findAllByRelationForNoMatchesReturnsEmptyList(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);

        $mapper = MapperRegistry::get(TestingChildMapper::class);
        self::assertTrue(
            $mapper->findAllByRelation($model, 'parent')->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationNotReturnsNotMatchingRecords(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid1 = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid1);
        $connection->insert('tx_oelib_test', []);
        $uid2 = (int)$connection->lastInsertId('tx_oelib_test');
        $anotherModel = $this->subject->find($uid2);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $anotherModel->getUid()]);

        $mapper = MapperRegistry::get(TestingChildMapper::class);
        self::assertTrue(
            $mapper->findAllByRelation($model, 'parent')->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationCanReturnOneMatch(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $relatedUid = (int)$relationConnection->lastInsertId('tx_oelib_test');
        $relatedModel = $mapper->find($relatedUid);

        $result = $mapper->findAllByRelation($model, 'parent');
        self::assertCount(1, $result);
        self::assertSame(
            $relatedModel,
            $result->first()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationCanReturnTwoMatches(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $uid = (int)$connection->lastInsertId('tx_oelib_test');
        $model = $this->subject->find($uid);
        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $model->getUid()]);

        $result = MapperRegistry::get(TestingChildMapper::class)->findAllByRelation($model, 'parent');

        self::assertCount(2, $result);
    }

    /**
     * @test
     */
    public function findAllByRelationIgnoresIgnoreList(): void
    {
        $childMapper = MapperRegistry::get(TestingChildMapper::class);
        $parentMapper = MapperRegistry::get(TestingMapper::class);

        $connection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_test');
        $connection->insert('tx_oelib_test', []);
        $parentUid = (int)$connection->lastInsertId('tx_oelib_test');
        $parentModel = $parentMapper->find($parentUid);

        $relationConnection = $this->getConnectionPool()->getConnectionForTable('tx_oelib_testchild');
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $parentModel->getUid()]);
        $childUid1 = (int)$relationConnection->lastInsertId('tx_oelib_test');
        $relatedModel = $childMapper->find($childUid1);
        $relationConnection->insert('tx_oelib_testchild', ['parent' => $parentModel->getUid()]);
        $childUid2 = (int)$relationConnection->lastInsertId('tx_oelib_test');
        $ignoredRelatedModel = $childMapper->find($childUid2);

        /** @var Collection<TestingChildModel> $ignoreList */
        $ignoreList = new Collection();
        $ignoreList->add($ignoredRelatedModel);

        $result = $childMapper->findAllByRelation($parentModel, 'parent', $ignoreList);

        self::assertCount(1, $result);
        self::assertSame($relatedModel, $result->first());
    }
}
