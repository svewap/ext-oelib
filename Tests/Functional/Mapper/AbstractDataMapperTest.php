<?php

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

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
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var TestingMapper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
        $this->testingFramework->setResetAutoIncrementThreshold(99999999);

        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);

        $this->subject = \Tx_Oelib_MapperRegistry::get(TestingMapper::class);
        $this->subject->setTestingFramework($this->testingFramework);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
        \Tx_Oelib_MapperRegistry::purgeInstance();
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
        $model = new TestingModel();
        $model->setTitle('New and fresh');
        $this->subject->save($model);
        $this->testingFramework->cleanUp();

        static::assertFalse($this->testingFramework->existsRecordWithUid('tx_oelib_test', $model->getUid()));
    }

    /**
     * @test
     */
    public function cleanUpAfterSaveRemovesAssociationTableEntriesRecord()
    {
        $leftUid = $this->testingFramework->createRecord('tx_oelib_test');

        $rightModel = new TestingModel();
        $rightModel->setData([]);
        $rightModel->setTitle('right model');

        $leftModel = $this->subject->find($leftUid);
        $leftModel->addRelatedRecord($rightModel);
        $this->subject->save($leftModel);
        $this->testingFramework->cleanUp();

        static::assertFalse(
            $this->testingFramework->existsRecord('tx_oelib_test_article_mm', 'uid_local = ' . $leftUid)
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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => $title]
        );

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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['title' => 'foo']
        );

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        self::assertSame('foo', $model->getTitle());
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
        $friendUid = $this->testingFramework->createRecord('tx_oelib_test', ['title' => $friendTitle]);
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['friend' => $friendUid]
        );

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
        $childUid = $this->testingFramework->createRecord('tx_oelib_test', ['title' => $childTitle]);
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['children' => (string)$childUid]
        );

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChild */
        $firstChild = $model->getChildren()->first();
        self::assertSame($childTitle, $firstChild->getTitle());
    }

    /**
     * @test
     */
    public function silentlyIgnoresCommaSeparatedOneToManyRelationWithZeroForeignUid()
    {
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['children' => '0']
        );

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
        $uid = $this->testingFramework->createRecord('tx_oelib_test');
        $relatedUid = $this->testingFramework->createRecord('tx_oelib_test', ['title' => $relatedTitle]);
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $uid,
            $relatedUid,
            'related_records'
        );

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstRelatedModel */
        $firstRelatedModel = $model->getRelatedRecords()->first();
        self::assertSame($relatedTitle, $firstRelatedModel->getTitle());
    }

    /**
     * @test
     */
    public function silentlyIgnoresManyToManyRelationWithZeroForeignUid()
    {
        $uid = $this->testingFramework->createRecord('tx_oelib_test', ['related_records' => 1]);
        $this->testingFramework->createRecord('tx_oelib_test_article_mm', ['uid_local' => $uid, 'uid_foreign' => 0]);

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
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
        $uid = $this->testingFramework->createRecord('tx_oelib_test');
        $relatedUid = $this->testingFramework->createRecord('tx_oelib_test');
        $this->testingFramework->createRelationAndUpdateCounter(
            'tx_oelib_test',
            $relatedUid,
            $uid,
            'bidirectional'
        );

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
        $uid = $this->testingFramework->createRecord(
            'tx_oelib_test',
            ['composition' => 1]
        );
        $this->testingFramework->createRecord(
            'tx_oelib_testchild',
            ['parent' => $uid, 'title' => $relatedTitle]
        );

        /** @var TestingModel $model */
        $model = $this->subject->find($uid);
        /** @var TestingModel $firstChildModel */
        $firstChildModel = $model->getComposition()->first();
        self::assertSame($relatedTitle, $firstChildModel->getTitle());
    }
}
