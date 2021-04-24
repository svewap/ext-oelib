<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Doctrine\DBAL\Driver\Mysqli\MysqliStatement;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
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

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AbstractDataMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingMapper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = MapperRegistry::get(TestingMapper::class);
    }

    protected function tearDown()
    {
        MapperRegistry::purgeInstance();
        parent::tearDown();
    }

    /*
     * Tests concerning usage with the testing framework
     */

    /**
     * @test
     */
    public function cleanUpAfterSaveRemovesCreatedRecord()
    {
        $testingFramework = new TestingFramework('tx_oelib');
        $this->subject->setTestingFramework($testingFramework);

        $model = new TestingModel();
        $model->setTitle('New and fresh');
        $this->subject->save($model);
        $testingFramework->cleanUp();

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $model->getUid())
        );
    }

    /**
     * @test
     */
    public function cleanUpAfterSaveRemovesAssociationTableEntriesRecord()
    {
        $testingFramework = new TestingFramework('tx_oelib');
        $this->subject->setTestingFramework($testingFramework);

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $leftUid = (int)$this->getDatabaseConnection()->lastInsertId();

        $rightModel = new TestingModel();
        $rightModel->setData([]);
        $rightModel->setTitle('right model');

        $leftModel = $this->subject->find($leftUid);
        $leftModel->addRelatedRecord($rightModel);
        $this->subject->save($leftModel);
        $testingFramework->cleanUp();

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test_article_mm', 'uid_local = ' . $leftUid)
        );
    }

    /*
     * Tests concerning load
     */

    /**
     * @test
     */
    public function loadWithModelWithExistingUidFillsModelWithData()
    {
        $title = 'Assassin of Kings';
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => $title]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $model = new TestingModel();
        $model->setUid($uid);
        $this->subject->load($model);

        self::assertSame($title, $model->getTitle());
    }

    /*
     * Tests concerning find
     */

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsModelDataFromDatabase()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function getModelForNonMappedUidReturnsModelInstance()
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getModel(['uid' => 2])
        );
    }

    /**
     * @test
     */
    public function getModelForNonMappedUidReturnsLoadedModel()
    {
        self::assertTrue(
            $this->subject->getModel(['uid' => 2])->isLoaded()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfGhostReturnsModelInstance()
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
    public function getModelForMappedUidOfGhostReturnsLoadedModel()
    {
        $mappedUid = $this->subject->getNewGhost()->getUid();

        self::assertTrue(
            $this->subject->getModel(['uid' => $mappedUid])->isLoaded()
        );
    }

    /**
     * @test
     */
    public function getModelForMappedUidOfGhostReturnsLoadedModelWithTheProvidedData()
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
    public function getModelForMappedUidOfGhostReturnsThatModel()
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
    public function getModelForMappedUidOfLoadedModelReturnsThatModelInstance()
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
    public function getModelForMappedUidOfLoadedModelAndNoNewDataProvidedReturnsModelWithTheInitialData()
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
    public function getModelForMappedUidOfLoadedModelAndNewDataProvidedReturnsModelWithTheInitialData()
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
    public function getModelForMappedUidOfDeadModelReturnsDeadModel()
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
    public function getModelForNonMappedUidReturnsModelWithChildrenList()
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
    public function getModelSavesModelToCacheByKeys()
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
    public function getListOfModelsReturnsInstanceOfList()
    {
        self::assertInstanceOf(
            Collection::class,
            $this->subject->getListOfModels([['uid' => 1]])
        );
    }

    /**
     * @test
     */
    public function getListOfModelsForAnEmptyArrayProvidedReturnsEmptyList()
    {
        self::assertTrue(
            $this->subject->getListOfModels([])->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getListOfModelsForOneRecordsProvidedReturnsListWithOneElement()
    {
        self::assertSame(
            1,
            $this->subject->getListOfModels([['uid' => 1]])->count()
        );
    }

    /**
     * @test
     */
    public function getListOfModelsForTwoRecordsProvidedReturnsListWithTwoElements()
    {
        self::assertSame(
            2,
            $this->subject->getListOfModels([['uid' => 1], ['uid' => 2]])->count()
        );
    }

    /**
     * @test
     */
    public function getListOfModelsReturnsListOfModelInstances()
    {
        self::assertInstanceOf(
            AbstractModel::class,
            $this->subject->getListOfModels([['uid' => 1]])->current()
        );
    }

    /**
     * @test
     */
    public function getListOfModelsReturnsListOfModelWithProvidedTitle()
    {
        self::assertSame(
            'foo',
            $this->subject->getListOfModels([['uid' => 1, 'title' => 'foo']])
                ->current()->getTitle()
        );
    }

    /*
     * Tests concerning load and reload
     */

    /**
     * @test
     */
    public function loadWithModelWithExistingUidOfHiddenRecordMarksModelAsLoaded()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['hidden' => 1]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function loadForModelWithExistingUidMarksModelAsClean()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function loadCanReadFloatDataFromFloatColumn()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['float_data' => 12.5]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function loadCanReadFloatDataFromDecimalColumn()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['decimal_data' => 12.5]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function loadCanReadFloatDataFromStringColumn()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['string_data' => 12.5]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function reloadCanLoadGhostFromDisk()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue($model->isGhost());

        $newTitle = 'bar';
        $this->getDatabaseConnection()->updateArray('tx_oelib_test', ['uid' => $uid], ['title' => $newTitle]);

        $this->subject->reload($model);

        self::assertSame($newTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function reloadCanReloadCleanLoadedModelFromDisk()
    {
        $oldTitle = 'foo';
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => $oldTitle]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($oldTitle, $model->getTitle());
        self::assertTrue($model->isLoaded());

        $newTitle = 'bar';
        $this->getDatabaseConnection()->updateArray('tx_oelib_test', ['uid' => $uid], ['title' => $newTitle]);

        $this->subject->reload($model);

        self::assertSame($newTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function reloadCanReloadDirtyModelFromDisk()
    {
        $oldTitle = 'foo';
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => '']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle($oldTitle);
        self::assertTrue($model->isLoaded());
        self::assertTrue($model->isDirty());

        $newTitle = 'bar';
        $this->getDatabaseConnection()->updateArray('tx_oelib_test', ['uid' => $uid], ['title' => $newTitle]);

        $this->subject->reload($model);

        self::assertSame($newTitle, $model->getTitle());
    }

    /**
     * @test
     */
    public function reloadWithModelWithInexistentUidMarksModelAsDead()
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
    public function findAndAccessingDataLoadsModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function isHiddenOnGhostInDatabaseLoadsModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $model = $this->subject->find($uid);
        $model->isHidden();

        self::assertTrue(
            $model->isLoaded()
        );
    }

    /**
     * @test
     */
    public function isHiddenOnGhostNotInDatabaseThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->find(1)->isHidden();
    }

    /**
     * @test
     */
    public function loadWithModelWithExistingUidLoadsModel()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'foo']
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function loadWithModelWithInexistentUidMarksModelAsDead()
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
    public function existsModelForUidOfLoadedModelReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForUidOfNotLoadedModelInDatabaseReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertTrue(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForInexistentUidReturnsFalse()
    {
        self::assertFalse(
            $this->subject->existsModel(1)
        );
    }

    /**
     * @test
     */
    public function existsModelForGhostModelWithInexistentUidReturnsFalse()
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
    public function existsModelForExistingUidLoadsModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->subject->existsModel($uid);

        self::assertTrue(
            $this->subject->find($uid)->isLoaded()
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfHiddenRecordReturnsFalse()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertFalse(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfHiddenRecordAndHiddenBeingAllowedReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenNotBeingAllowedReturnsFalse()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->subject->load($this->subject->find($uid));

        self::assertFalse(
            $this->subject->existsModel($uid)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfLoadedHiddenRecordAndHiddenBeingAllowedReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfLoadedNonHiddenRecordAndHiddenBeingAllowedReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 0]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->existsModel($uid, true)
        );
    }

    /**
     * @test
     */
    public function existsModelForExistentUidOfHiddenRecordAfterLoadingAsNonHiddenAndHiddenBeingAllowedReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function getLoadedTestingModelReturnsModel()
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
    public function getLoadedTestingModelReturnsLoadedModel()
    {
        $this->subject->disableDatabaseAccess();

        self::assertTrue(
            $this->subject->getLoadedTestingModel([])->isLoaded()
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelReturnsModelWithUid()
    {
        $this->subject->disableDatabaseAccess();

        self::assertTrue(
            $this->subject->getLoadedTestingModel([])->hasUid()
        );
    }

    /**
     * @test
     */
    public function getLoadedTestingModelCreatesRegisteredModel()
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
    public function getLoadedTestingModelSetsTheProvidedData()
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
    public function getLoadedTestingModelCreatesRelations()
    {
        $this->subject->disableDatabaseAccess();

        $relatedModel = $this->subject->getNewGhost();
        /** @var TestingModel $model */
        $model = $this->subject->getLoadedTestingModel(
            ['friend' => $relatedModel->getUid()]
        );

        self::assertSame(
            $relatedModel->getUid(),
            $model->getFriend()->getUid()
        );
    }

    /////////////////////////////////////////////
    // Tests concerning the foreign key mapping
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function relatedRecordWithZeroUidIsNull()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertNull(
            $model->getFriend()
        );
    }

    /**
     * @test
     */
    public function relatedRecordWithExistingUidReturnsRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $friendUid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['friend' => $friendUid]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $friendUid,
            $model->getFriend()->getUid()
        );
    }

    /**
     * @test
     */
    public function relatedRecordWithRelationToSelfReturnsSelf()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->updateArray(
            'tx_oelib_test',
            ['uid' => $uid],
            ['friend' => $uid]
        );
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
    public function relatedRecordWithExistingUidCanReturnOtherModelType()
    {
        $this->getDatabaseConnection()->insertArray('fe_users', []);
        $ownerUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['owner' => $ownerUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function relatedRecordWithExistingUidReturnsRelatedRecordThatCanBeLoaded()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $friendUid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->getFriend()->getTitle();

        self::assertTrue(
            $model->getFriend()->isLoaded()
        );
    }

    /**
     * @test
     */
    public function relatedRecordWithInexistentUidReturnsRelatedRecordAsGhost()
    {
        $friendUid = 2;
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame(
            $friendUid,
            $model->getFriend()->getUid()
        );
    }

    /*
     * Tests concerning the m:n mapping with a comma-separated list of UIDs
     */

    /**
     * @test
     */
    public function commaSeparatedRelationsWithEmptyStringCreatesEmptyList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getChildren()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['children' => $childUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function commaSeparatedRelationsWithTwoUidsReturnsListWithBothRelatedModels()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['children' => $childUid1 . ',' . $childUid2]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function commaSeparatedRelationsWithOneUidAndZeroIgnoresZero()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['children' => $childUid1 . ',0']
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function commaSeparatedRelationHasParentModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function commaSeparatedRelationIsNotOwnedByParent()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function mnRelationsWithEmptyStringCreatesEmptyList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getRelatedRecords()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

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
    public function mnRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid1]);
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid2]);

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
    public function mnRelationsReturnsListSortedBySorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test_article_mm',
            ['uid_local' => $uid, 'uid_foreign' => $relatedUid1, 'sorting' => 2]
        );
        $this->getDatabaseConnection()->insertArray(
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
    public function mnRelationHasParentModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function mnRelationIsNotOwnedByParent()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function bidirectionalMNRelationsWithEmptyStringCreatesEmptyList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getBidirectional()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

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
    public function bidirectionalMNRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 2]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid1, 'uid_foreign' => $relatedUid]);
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid2, 'uid_foreign' => $relatedUid]);

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
    public function bidirectionalMNRelationsReturnsListSortedByUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 2]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid1, 'uid_foreign' => $relatedUid]);
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid2, 'uid_foreign' => $relatedUid]);

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
    public function bidirectionalMnRelationHasParentModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function bidirectionalMnRelationIsNotOwnedByParent()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function oneToManyRelationsWithEmptyStringCreatesEmptyList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertTrue(
            $model->getComposition()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['parent' => $uid]
        );
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function oneToManyRelationsCanSortByForeignSortBy()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition' => 2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $uid, 'title' => 'b']);
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $uid, 'title' => 'a']);
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($relatedUid2 . ',' . $relatedUid1, $model->getComposition()->getUids());
    }

    /**
     * @test
     */
    public function oneToManyRelationsCanSortByForeignDefaultSortBy()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition2' => 2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['tx_oelib_parent2' => $uid, 'title' => 'b']
        );
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['tx_oelib_parent2' => $uid, 'title' => 'a']
        );
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($relatedUid2 . ',' . $relatedUid1, $model->getComposition2()->getUids());
    }

    /**
     * @test
     */
    public function oneToManyRelationWithoutSortingDoesNotCrash()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition_without_sorting' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['tx_oelib_parent3' => $uid]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame((string)$relatedUid, $model->getCompositionWithoutSorting()->getUids());
    }

    /**
     * @test
     */
    public function oneToManyRelationsWithOneRelatedModelNotLoadsDeletedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $uid, 'deleted' => 1]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertTrue($model->getComposition()->isEmpty());
    }

    /**
     * @test
     */
    public function oneToManyRelationsWithTwoRelatedModelsReturnsListWithBothRelatedModels()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['composition' => 2]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['parent' => $uid, 'title' => 'relation A']
        );
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['parent' => $uid, 'title' => 'relation B']
        );
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function oneToManyRelationsReturnsListSortedByForeignSortBy()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['composition' => 2]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['parent' => $uid, 'title' => 'relation B']
        );
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_testchild',
            ['parent' => $uid, 'title' => 'relation A']
        );
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function oneToManyRelationHasParentModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function oneToManyRelationIsOwnedByParent()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        self::assertTrue(
            $model->getComposition()->isRelationOwnedByParent()
        );
    }

    /*
     * Tests concerning n:1 association mapping
     */

    /**
     * @test
     */
    public function relatedRecordWithExistingUidReturnsRelatedRecordWithData()
    {
        $friendTitle = 'Brianna';
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => $friendTitle]);
        $friendUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame($friendTitle, $model->getFriend()->getTitle());
    }

    /*
     * Tests concerning the m:n mapping with a comma-separated list of UIDs
     */

    /**
     * @test
     */
    public function commaSeparatedRelationsWithOneUidReturnsListWithRelatedModelWithData()
    {
        $childTitle = 'Abraham';
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => $childTitle]);
        $childUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['children' => (string)$childUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function silentlyIgnoresCommaSeparatedOneToManyRelationWithZeroForeignUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['children' => '0']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        // load any property to trigger loading the data
        $model->getTitle();
    }

    /*
     * Tests concerning the m:n mapping using an m:n table
     */

    /**
     * @test
     */
    public function mnRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData()
    {
        $relatedTitle = 'Geralt of Rivia';
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => $relatedTitle, 'bidirectional' => 1]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

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
    public function silentlyIgnoresManyToManyRelationWithZeroForeignUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => 0]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        // load any property to trigger loading the data
        $model->getTitle();
    }

    /*
     * Tests concerning the bidirectional m:n mapping using an m:n table.
     */

    /**
     * @test
     */
    public function bidirectionalMNRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid]);

        /** @var TestingModel $model */
        $model = $this->subject->find($relatedUid);
        self::assertSame((string)$uid, $model->getBidirectional()->getUids());
    }

    /*
     * Tests concerning the 1:n mapping using a foreign field.
     */

    /**
     * @test
     */
    public function oneToManyRelationsWithOneRelatedModelReturnsListWithRelatedModelWithData()
    {
        $relatedTitle = 'Triss Merrigold';
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $uid, 'title' => $relatedTitle]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChildModel */
        $firstChildModel = $model->getComposition()->first();
        self::assertSame($relatedTitle, $firstChildModel->getTitle());
    }

    ////////////////////////////////////////////////
    // Tests concerning findSingleByWhereClause().
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function findSingleByWhereClauseWithUidOfInexistentRecordThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findSingleByWhereClause(
            ['uid' => 1]
        );
    }

    /**
     * @test
     */
    public function findSingleByWhereClauseWithUidOfExistentNotMappedRecordReturnsModelWithTheData()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);

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
    public function findSingleByWhereClauseWithUidOfExistentYetMappedRecordReturnsModelWithTheMappedData()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function loadWithUidOfRecordInDatabaseAndDatabaseAccessDisabledMarksModelAsDead()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->disableDatabaseAccess();
        $this->subject->load($this->subject->find($uid));

        self::assertTrue(
            $this->subject->find($uid)->isDead()
        );
    }

    /**
     * @test
     */
    public function loadWithUidOfRecordNotInDatabaseAndDatabaseAccessDisabledMarksModelAsDead()
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
    public function saveForReadOnlyModelDoesNotCommitModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->setModelClassName(ReadOnlyModel::class);
        $this->subject->save($this->subject->find($uid));

        self::assertSame(
            0,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_test', 'title = "foo" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME'])
        );
    }

    /**
     * @test
     */
    public function saveForDatabaseAccessDeniedDoesNotCommitDirtyLoadedModelToDatabase()
    {
        $this->subject->disableDatabaseAccess();

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar"')
        );
    }

    /**
     * @test
     */
    public function saveForGhostDoesNotCommitModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->save($this->subject->find($uid));

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'tstamp > 0')
        );
    }

    /**
     * @test
     */
    public function saveForDeadModelDoesNotCommitDirtyModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->markAsDead();
        $this->subject->save($model);

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar"')
        );
    }

    /**
     * @test
     */
    public function saveForCleanLoadedModelDoesNotCommitModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $model->markAsClean();
        $this->subject->save($model);

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar"')
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidCommitsModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar"')
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
    public function savePersistsAllBasicDataTypes(string $propertyName, $expectedValue)
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

        /** @var MysqliStatement $result */
        $result = $this->getDatabaseConnection()->select('*', 'tx_oelib_test', 'uid = ' . $uid);
        $data = $result->fetch();

        self::assertSame($expectedValue, $data[$propertyName]);
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidDoesNotChangeTheUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function saveForDirtyLoadedModelWithUidSetsTimestamp()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_test', 'title = "bar" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME'])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithUidAndWithoutDataCommitsModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        $model = new TestingModel();
        $model->setUid($uid);
        $model->setData([]);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'tstamp = ' . $GLOBALS['SIM_EXEC_TIME'])
        );
    }

    /**
     * @test
     */
    public function saveNewModelFromMemoryAndMapperInTestingModeMarksModelAsDummyModel()
    {
        $model = new TestingModel();
        $model->setData(['title' => 'foo']);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo"')
        );
    }

    /**
     * @test
     */
    public function saveNewModelFromMemoryRegistersModelInMapper()
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
    public function isDirtyAfterSaveForDirtyLoadedModelWithUidReturnsFalse()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function saveForDirtyLoadedModelWithoutUidAndWithoutRelationsCommitsModelToDatabase()
    {
        $model = new TestingModel();
        $model->setData(['title' => 'bar']);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar"')
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidAndWithRelationsCommitsModelToDatabase()
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar"')
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidAddsModelToMapAfterSave()
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
    public function saveForDirtyLoadedModelWithoutUidSetsUidForModel()
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
    public function saveForDirtyLoadedModelWithoutUidSetsUidReceivedFromDatabaseForModel()
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $model->getUid())
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSaveForDirtyLoadedModelWithoutUidReturnsFalse()
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
    public function saveForDirtyLoadedModelWithoutUidSetsTimestamp()
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_test', 'title = "bar" AND tstamp = ' . $GLOBALS['SIM_EXEC_TIME'])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithoutUidSetsCreationDate()
    {
        $model = new TestingModel();

        $data = ['title' => 'bar'];
        $this->subject->createRelations($data, $model);

        $model->setData($data);
        $model->markAsDirty();

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_test', 'title = "bar" AND crdate = ' . $GLOBALS['SIM_EXEC_TIME'])
        );
    }

    /**
     * @test
     */
    public function saveForDirtyLoadedModelWithNoDataDoesNotCommitModelToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo" AND tstamp > 0')
        );

        $model = $this->subject->find($uid);
        $model->markAsDirty();
        $this->subject->save($model);

        self::assertSame(
            0,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo" AND tstamp > 0')
        );
    }

    /**
     * @test
     */
    public function isDeadAfterSaveForDirtyLoadedModelWithDeletedFlagSetReturnsTrue()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function saveForModelWithN1RelationSavesUidOfRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $friendUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_test', 'title = "bar" AND friend = ' . $friendUid)
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNCommaSeparatedRelationSavesUidList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['children' => $childUid1 . ',' . $childUid2]
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_test',
                'title = "bar" AND children = "' . $childUid1 . ',' . $childUid2 . '"'
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNTableRelationSavesNumberOfRelatedRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['related_records' => 2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['bidirectional' => 1]);
        $relatedUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid1]);
        $this->getDatabaseConnection()
            ->insertArray('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => $relatedUid2]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar" AND related_records = 2')
        );
    }

    /**
     * @test
     */
    public function saveForModelWithOneToManyRelationSavesNumberOfRelatedRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $composition = $model->getComposition();
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', []);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', []);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $composition->add($mapper->find($childUid1));
        $composition->add($mapper->find($childUid2));

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "bar" AND composition = 2')
        );
    }

    /**
     * @test
     */
    public function saveForModelWithOneToManyRelationSavesDirtyRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $composition = $model->getComposition();
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', []);
        $childUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $component = $mapper->find($childUid);
        $composition->add($component);

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_testchild',
                'uid = ' . $component->getUid() . ' AND parent = ' . $model->getUid()
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWith1NRelationSavesFirstNewRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $component = new TestingChildModel();
        $component->markAsDummyModel();
        $model->getComposition()->add($component);

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_testchild',
                'uid = ' . $component->getUid() . ' AND parent = ' . $model->getUid()
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWith1NRelationSavesSecondNewRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_testchild',
                'uid = ' . $newComponent2->getUid() . ' AND parent = ' . $model->getUid()
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWith1NRelationSavesNewRelatedRecordWithPrefixInForeignKey()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');

        $component = new TestingChildModel();
        $component->markAsDummyModel();
        $model->getComposition2()->add($component);

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_testchild',
                'uid = ' . $component->getUid() . ' AND tx_oelib_parent2 = ' . $model->getUid()
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithOneToManyRelationDeletesUnconnectedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->markAsDirty();

        $composition = $model->getComposition();
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $component1 */
        $component1 = $mapper->find($childUid1);
        $composition->add($component1);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $component2 */
        $component2 = $mapper->find($childUid2);

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_testchild', 'uid = ' . $component2->getUid() . ' AND deleted = 1')
        );
    }

    /**
     * @test
     */
    public function saveForModelWithN1RelationSavesDirtyRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $friendUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['friend' => $friendUid]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        /** @var TestingModel $friend */
        $friend = $this->subject->find($friendUid);
        $friend->setTitle('foo');

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo" AND uid = ' . $friendUid)
        );
    }

    /**
     * @test
     */
    public function saveForModelWithN1RelationSavesNewRelatedRecord()
    {
        $friend = new TestingModel();
        $friend->markAsDummyModel();
        $friend->setTitle('foo');

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setFriend($friend);

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $friend->getUid())
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNCommaSeparatedRelationSavesDirtyRelatedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['children' => $childUid1 . ',' . $childUid2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        $model->setTitle('bar');
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid1);
        $child->setTitle('foo');

        $this->subject->save($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'title = "foo" AND uid = ' . $childUid1)
        );
    }

    /**
     * @test
     */
    public function saveAddsModelToCache()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function addModelToListMarksParentModelAsDirty()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid = (int)$this->getDatabaseConnection()->lastInsertId();

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
    public function appendListMarksParentModelAsDirty()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->getNewGhost();
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
    public function purgeModelFromListMarksModelAsDirty()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid = (int)$this->getDatabaseConnection()->lastInsertId();

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

    /*
     * Tests concerning save
     */

    /**
     * @test
     */
    public function saveForModelWithMNTableRelationCreatesIntermediateRelationRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child = $this->subject->find($childUid);

        $parent->getRelatedRecords()->add($child);
        $this->subject->save($parent);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_test_article_mm',
                'uid_local=' . $parentUid . ' AND uid_foreign=' . $childUid . ' AND sorting = 0'
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithMNTableRelationsCreatesIntermediateRelationRecordAndIncrementsSorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var TestingModel $parent */
        $parent = $this->subject->find($parentUid);
        $child1 = $this->subject->find($childUid1);
        $child2 = $this->subject->find($childUid2);

        $parent->getRelatedRecords()->add($child1);
        $parent->getRelatedRecords()->add($child2);
        $this->subject->save($parent);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_test_article_mm',
                'uid_local=' . $parentUid . ' AND uid_foreign=' . $childUid2 . ' AND sorting = 1'
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid = (int)$this->getDatabaseConnection()->lastInsertId();

        $parent = $this->subject->find($parentUid);
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid);

        $child->getBidirectional()->add($parent);
        $this->subject->save($child);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_test_article_mm',
                'uid_local=' . $parentUid . ' AND uid_foreign=' . $childUid . ' AND sorting = 0'
            )
        );
    }

    /**
     * @test
     */
    public function saveForModelWithBidirectionalMNRelationCreatesIntermediateRelationRecordAndIncrementsSorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $parentUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $childUid = (int)$this->getDatabaseConnection()->lastInsertId();

        $parent1 = $this->subject->find($parentUid1);
        $parent2 = $this->subject->find($parentUid2);
        /** @var TestingModel $child */
        $child = $this->subject->find($childUid);

        $child->getBidirectional()->add($parent1);
        $child->getBidirectional()->add($parent2);
        $this->subject->save($child);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount(
                '*',
                'tx_oelib_test_article_mm',
                'uid_local=' . $parentUid2 . ' AND uid_foreign=' . $childUid . ' AND sorting = 1'
            )
        );
    }

    /**
     * @test
     */
    public function saveCanSaveFloatDataToFloatColumn()
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
    public function saveCanSaveFloatDataToDecimalColumn()
    {
        $model = new TestingModel();
        $model->setData(['decimal_data' => 9.5]);
        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim($row['decimal_data'], '0'));
    }

    /**
     * @test
     */
    public function saveCanSaveFloatDataToStringColumn()
    {
        $model = new TestingModel();
        $model->setData(['string_data' => 9.5]);
        $this->subject->save($model);

        $row = $this->findRecordByUid($model->getUid());
        self::assertSame('9.5', rtrim($row['string_data'], '0'));
    }

    /**
     * @param int $uid
     *
     * @return array|null
     */
    private function findRecordByUid(int $uid): array
    {
        $connection = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getConnectionForTable('tx_oelib_test');
        $columns = ['float_data', 'decimal_data', 'string_data'];

        return $connection->select($columns, 'tx_oelib_test', ['uid' => $uid])->fetch();
    }

    /////////////////////////////
    // Tests concerning findAll
    /////////////////////////////

    /**
     * @test
     */
    public function findAllForNoRecordsReturnsEmptyList()
    {
        self::assertTrue(
            $this->subject->findAll()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllForOneRecordInDatabaseReturnsOneRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        self::assertSame(
            1,
            $this->subject->findAll()->count()
        );
    }

    /**
     * @test
     */
    public function findAllForTwoRecordsInDatabaseReturnsTwoRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        self::assertSame(
            2,
            $this->subject->findAll()->count()
        );
    }

    /**
     * @test
     */
    public function findAllForOneRecordInDatabaseReturnsLoadedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        self::assertTrue(
            $this->subject->findAll()->first()->isLoaded()
        );
    }

    /**
     * @test
     */
    public function findAllIgnoresHiddenRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['hidden' => 1]);

        self::assertTrue(
            $this->subject->findAll()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllIgnoresDeletedRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['deleted' => 1]);

        self::assertTrue(
            $this->subject->findAll()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllSortsRecordsBySorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            min($uid1, $uid2),
            $this->subject->findAll()->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findAllForGivenSortParameterOverridesDefaultSorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'record a']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'record b']);

        self::assertSame(
            $uid,
            $this->subject->findAll('title')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findAllForGivenSortParameterWithSortDirectionSortsResultsBySortdirection()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'record b']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'record a']);

        self::assertSame(
            $uid,
            $this->subject->findAll('title DESC')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findAllForGivenSortParameterFindsMultipleEntries()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        self::assertSame(
            2,
            $this->subject->findAll('title ASC')->count()
        );
    }

    ///////////////////////////////////////
    // Tests concerning findByWhereClause
    ///////////////////////////////////////

    /**
     * @test
     */
    public function findByWhereClauseForNoGivenParameterAndTwoRecordsFindsBothRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        self::assertSame(
            2,
            $this->subject->findByWhereClause()->count()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseForGivenWhereClauseAndOneMatchingRecordFindsThisRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $foundRecordUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $foundRecordUid,
            $this->subject->findByWhereClause('title LIKE "foo"')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseForGivenWhereClauseAndTwoRecordsOneMatchingOneNotDoesNotFindNonMatchingRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);
        $notMatchingUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertNotSame(
            $notMatchingUid,
            $this->subject->findByWhereClause('title LIKE "foo"')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseForNoSortingProvidedSortsRecordsByDefaultSorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            min($uid1, $uid2),
            $this->subject->findByWhereClause()->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseForSortingProvidedSortsRecordsByGivenSorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);
        $firstEntryUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $firstEntryUid,
            $this->subject->findByWhereClause('', 'title ASC')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseForSortingAndWhereClauseProvidedSortsMatchingRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo', 'sorting' => 2]);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo', 'sorting' => 0]);
        $firstMatchingUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar', 'sorting' => 1]);

        self::assertSame(
            $firstMatchingUid,
            $this->subject->findByWhereClause('title LIKE "foo"', 'sorting ASC')
                ->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseWithoutLimitFindsAllRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $firstUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);
        $secondUid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $firstUid . ',' . $secondUid,
            $this->subject->findByWhereClause()->getUids()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseWithTwoRecordsAndLimitOneFindsOnlyFirstRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $firstUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);

        self::assertSame(
            (string)$firstUid,
            $this->subject->findByWhereClause('', '', '1')->getUids()
        );
    }

    /**
     * @test
     */
    public function findByWhereClauseWithThreeRecordsAndLimitBeginOneAndMaximumOneFindsOnlySecondRecord()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);
        $secondUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);

        self::assertSame(
            (string)$secondUid,
            $this->subject->findByWhereClause('', '', '1,1')->getUids()
        );
    }

    ///////////////////////////////////
    // Tests concerning findByPageUid
    ///////////////////////////////////

    /**
     * @test
     */
    public function findByPageUidForPageUidZeroReturnsEntryWithZeroPageUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByPageUid(0)->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForPageUidZeroReturnsEntryWithNonZeroPageUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 42]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByPageUid(0)->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForPageUidEmptyReturnsRecordWithNonZeroPageUid()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 42]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByPageUid('')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForNonZeroPageUidReturnsEntryFromThatPage()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByPageUid(1)->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForNonZeroPageUidDoesNotReturnEntryWithDifferentPageUId()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 2]);

        self::assertTrue(
            $this->subject->findByPageUid(1)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForPageUidAndSortingGivenReturnEntrySortedBySorting()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 2, 'sorting' => 3]);

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 2, 'sorting' => 1]);
        $firstMatchingRecord = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $firstMatchingRecord,
            $this->subject->findByPageUid(2, 'sorting ASC')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForTwoNonZeroPageUidsCanReturnRecordFromFirstPage()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByPageUid('1,2')->first()->getUid()
        );
    }

    /**
     * @test
     */
    public function findByPageUidForTwoNonZeroPageUidsCanReturnRecordFromSecondPage()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['pid' => 2]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByPageUid('1,2')->first()->getUid()
        );
    }

    /////////////////////////////////////
    // Tests concerning additional keys
    /////////////////////////////////////

    /**
     * @test
     */
    public function findByKeyFindsLoadedModel()
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
    public function findByKeyFindsLastLoadedModelWithSameKey()
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
    public function findByKeyFindsSavedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function findByKeyFindsLastSavedModelWithSameKey()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid1);
        $model1->setTitle('Earl Grey');
        $this->subject->save($model1);

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'Earl Grey']);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function findOneByKeyCanFindModelFromCache()
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
    public function findOneByKeyCanLoadModelFromDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'Earl Grey']);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findOneByKey('title', 'Earl Grey')->getUid()
        );
    }

    /**
     * @test
     */
    public function findOneByKeyForInexistentThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findOneByKey('title', 'Darjeeling');
    }

    /**
     * @test
     */
    public function findByCompoundKeyFindsLoadedModel()
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
    public function findByCompoundKeyFindsLastLoadedModelWithSameCompoundKey()
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
    public function findByCompoundKeyFindsSavedModel()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function findByCompoundKeyFindsLastSavedModelWithSameCompoundKey()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model1 */
        $model1 = $this->subject->find($uid1);
        $model1->setTitle('Earl Grey');
        $model1->setHeader('Tea Time');
        $this->subject->save($model1);

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'Earl Grey', 'header' => 'Tea Time']);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function findOneByCompoundKeyCanFindModelFromCache()
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
    public function findOneByCompoundKeyCanLoadModelFromDatabase()
    {
        $this->getDatabaseConnection()->insertArray(
            'tx_oelib_test',
            ['title' => 'Earl Grey', 'header' => 'Tea Time']
        );
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findOneByCompoundKey(['title' => 'Earl Grey', 'header' => 'Tea Time'])->getUid()
        );
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyForNonExistentThrowsException()
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
    public function deleteForDeadModelDoesNotThrowException()
    {
        $model = new TestingModel();
        $model->markAsDead();

        $this->subject->delete($model);
    }

    /**
     * @test
     */
    public function deleteForModelWithoutUidMarksModelAsDead()
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
    public function deleteForModelWithUidMarksModelAsDead()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        self::assertTrue(
            $model->isDead()
        );
    }

    /**
     * @test
     */
    public function deleteForGhostFromGetNewGhostThrowsException()
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
    public function deleteForReadOnlyModelThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'This model is read-only and must not be deleted.'
        );

        $model = new ReadOnlyModel();
        $this->subject->delete($model);
    }

    /**
     * @test
     */
    public function deleteForModelWithUidWritesModelAsDeletedToDatabase()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid);

        $this->subject->delete($model);

        self::assertSame(
            1,
            $this->getDatabaseConnection()->selectCount('*', 'tx_oelib_test', 'uid = ' . $uid . ' AND deleted = 1')
        );
    }

    /**
     * @test
     */
    public function deleteForModelWithUidStillKeepsModelAccessibleViaDataMapper()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
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
    public function deleteForModelWithOneToManyRelationDeletesRelatedElements()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $uid]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();

        $this->subject->delete($this->subject->find($uid));

        self::assertSame(
            1,
            $this->getDatabaseConnection()
                ->selectCount('*', 'tx_oelib_testchild', 'uid = ' . $relatedUid . ' AND deleted = 1')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function deleteForDirtyModelWithOneToManyRelationToDirtyElementDoesNotCrash()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['composition' => 1]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $uid]);

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
    public function findAllByRelationWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$relationKey must not be empty');

        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        /** @var TestingModel $model */
        $model = $this->subject->find($uid);

        MapperRegistry::get(TestingChildMapper::class)->findAllByRelation($model, '');
    }

    /**
     * @test
     */
    public function findAllByRelationForNoMatchesReturnsEmptyList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid);

        $mapper = MapperRegistry::get(TestingChildMapper::class);
        self::assertTrue(
            $mapper->findAllByRelation($model, 'parent')->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationNotReturnsNotMatchingRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid1);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $anotherModel = $this->subject->find($uid2);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $anotherModel->getUid()]);

        $mapper = MapperRegistry::get(TestingChildMapper::class);
        self::assertTrue(
            $mapper->findAllByRelation($model, 'parent')->isEmpty()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationCanReturnOneMatch()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid);
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $relatedUid = (int)$this->getDatabaseConnection()->lastInsertId();
        $relatedModel = $mapper->find($relatedUid);

        $result = $mapper->findAllByRelation($model, 'parent');
        self::assertSame(
            1,
            $result->count()
        );
        self::assertSame(
            $relatedModel,
            $result->first()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationCanReturnTwoMatches()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);

        $result = MapperRegistry::get(TestingChildMapper::class)
            ->findAllByRelation($model, 'parent');
        self::assertSame(
            2,
            $result->count()
        );
    }

    /**
     * @test
     */
    public function findAllByRelationIgnoresIgnoreList()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();
        $model = $this->subject->find($uid);
        $mapper = MapperRegistry::get(TestingChildMapper::class);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid1 = (int)$this->getDatabaseConnection()->lastInsertId();
        $relatedModel = $mapper->find($childUid1);
        $this->getDatabaseConnection()->insertArray('tx_oelib_testchild', ['parent' => $model->getUid()]);
        $childUid2 = (int)$this->getDatabaseConnection()->lastInsertId();
        $ignoredRelatedModel = $mapper->find($childUid2);

        $ignoreList = new Collection();
        $ignoreList->add($ignoredRelatedModel);

        $result = MapperRegistry::get(TestingChildMapper::class)
            ->findAllByRelation($model, 'parent', $ignoreList);
        self::assertSame(
            1,
            $result->count()
        );
        self::assertSame(
            $relatedModel,
            $result->first()
        );
    }

    //////////////////////////////////////////
    // Tests concerning countByWhereClause()
    //////////////////////////////////////////

    /**
     * @test
     */
    public function countByWhereClauseWithoutWhereClauseCountsAllRecords()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', []);

        self::assertSame(
            1,
            $this->subject->countByWhereClause()
        );
    }

    /**
     * @test
     */
    public function countByWhereClauseWithoutMatchingRecordReturnsZero()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);

        self::assertSame(
            0,
            $this->subject->countByWhereClause('title = "bar"')
        );
    }

    /**
     * @test
     */
    public function countByWhereClauseWithOneMatchingRecordsReturnsOne()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'foo']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);

        self::assertSame(
            1,
            $this->subject->countByWhereClause('title = "bar"')
        );
    }

    /**
     * @test
     */
    public function countByWhereClauseWithTwoMatchingRecordsReturnsTwo()
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => 'bar']);

        self::assertSame(
            2,
            $this->subject->countByWhereClause('title = "bar"')
        );
    }
}
