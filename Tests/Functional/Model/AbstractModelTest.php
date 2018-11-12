<?php

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AbstractModelTest extends FunctionalTestCase
{
    /**
     * @var string
     */
    const TEST_RECORD_TITLE = 'Hello world';

    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var TestingModel
     */
    private $subject = null;

    /**
     * @var TestingMapper
     */
    protected $dataMapper = null;

    protected function setUp()
    {
        parent::setUp();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
        \Tx_Oelib_MapperRegistry::getInstance()->activateTestingMode($this->testingFramework);
        $this->dataMapper = \Tx_Oelib_MapperRegistry::get(TestingMapper::class);

        $uid = $this->createTestRecord();
        $this->subject = $this->dataMapper->find($uid);
    }

    /*
     * Tests concerning __clone
     */

    /**
     * Creates a test record.
     *
     * @return int the UID
     */
    private function createTestRecord()
    {
        return $this->testingFramework->createRecord('tx_oelib_test', ['title' => self::TEST_RECORD_TITLE]);
    }

    /**
     * @test
     */
    public function cloneReturnsInstanceOfSameClass()
    {
        self::assertInstanceOf(
            get_class($this->subject),
            clone $this->subject
        );
    }

    /**
     * @test
     */
    public function cloneReturnsNewInstance()
    {
        self::assertNotSame(
            $this->subject,
            clone $this->subject
        );
    }

    /**
     * @return int[][]
     */
    public function cloneableStatusDataProvider()
    {
        return [
            'virgin' => [\Tx_Oelib_Model::STATUS_VIRGIN],
            'ghost' => [\Tx_Oelib_Model::STATUS_GHOST],
            'loaded' => [\Tx_Oelib_Model::STATUS_LOADED],
        ];
    }

    /**
     * @test
     *
     * @param string $status
     *
     * @dataProvider cloneableStatusDataProvider
     */
    public function cloneReturnsDirtyModel($status)
    {
        $this->subject->setLoadStatus($status);

        $clone = clone $this->subject;
        self::assertTrue(
            $clone->isDirty()
        );
    }

    /**
     * @test
     */
    public function cloningVirginModelReturnsVirginModel()
    {
        $subject = new TestingModel();
        self::assertTrue($subject->isVirgin());

        $clone = clone $subject;

        self::assertTrue($clone->isVirgin());
    }

    /**
     * @test
     */
    public function cloningGhostLoadsModel()
    {
        self::assertTrue($this->subject->isGhost());

        $clone = clone $this->subject;

        self::assertTrue($clone->isLoaded());
    }

    /**
     * @test
     */
    public function cloningLoadedModelReturnsLoadedModel()
    {
        self::assertSame(self::TEST_RECORD_TITLE, $this->subject->getTitle());
        self::assertTrue($this->subject->isLoaded());

        $clone = clone $this->subject;

        self::assertTrue($clone->isLoaded());
    }

    /**
     * @test
     */
    public function cloningModelWithUidReturnsModelWithoutUid()
    {
        self::assertTrue($this->subject->hasUid());

        $clone = clone $this->subject;

        self::assertFalse($clone->hasUid());
    }

    /**
     * @test
     */
    public function clonedModelHasStringDataFromOriginal()
    {
        $clone = clone $this->subject;

        self::assertSame($this->subject->getTitle(), $clone->getTitle());
    }

    /**
     * @test
     */
    public function clonedModelHasNto1RelationFromOriginal()
    {
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->setFriend($relatedRecord);
        $this->dataMapper->save($this->subject);

        $clone = clone $this->subject;

        self::assertSame($this->subject->getFriend(), $clone->getFriend());
    }

    /**
     * @test
     */
    public function clonedModelHasModelsFromMtoNRelationFromOriginal()
    {
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);
        $this->dataMapper->save($this->subject);

        $clone = clone $this->subject;

        self::assertSame($relatedRecord, $clone->getRelatedRecords()->first());
    }

    /**
     * @test
     */
    public function clonedModelHasNewInstanceOfMtoNRelation()
    {
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);
        $this->dataMapper->save($this->subject);

        $clone = clone $this->subject;

        self::assertNotSame($clone->getRelatedRecords(), $this->subject->getRelatedRecords());
    }

    /**
     * @test
     */
    public function clonedModelHasMtoNRelationWithCloneAsParentModel()
    {
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);
        $this->dataMapper->save($this->subject);
        self::assertSame($this->subject, $this->subject->getRelatedRecords()->getParentModel());

        $clone = clone $this->subject;

        self::assertSame($clone, $clone->getRelatedRecords()->getParentModel());
    }

    /**
     * @test
     */
    public function clonedModelHasClonesOfModelsFrom1toNRelationFromOriginal()
    {
        $childRecord = new TestingChildModel();
        $childRecordTitle = 'bubble bobble';
        $childRecord->setTitle($childRecordTitle);
        $this->subject->addCompositionRecord($childRecord);
        $this->dataMapper->save($this->subject);

        $clone = clone $this->subject;

        /** @var TestingChildModel $firstCloneChild */
        $firstCloneChild = $clone->getComposition()->first();
        self::assertSame($childRecord->getTitle(), $firstCloneChild->getTitle());
        self::assertNotSame($childRecord, $firstCloneChild);
    }

    /**
     * @test
     */
    public function clonedModelHasNewInstanceOf1toNRelation()
    {
        $childRecord = new TestingChildModel();
        $childRecord->setData([]);
        $this->subject->addCompositionRecord($childRecord);
        $this->dataMapper->save($this->subject);

        $clone = clone $this->subject;

        self::assertNotSame($clone->getRelatedRecords(), $this->subject->getComposition());
    }

    /**
     * @test
     */
    public function clonedModelHas1toNRelationWithCloneAsParentModel()
    {
        $childRecord = new TestingChildModel();
        $childRecord->setData([]);
        $this->subject->addCompositionRecord($childRecord);
        $this->dataMapper->save($this->subject);
        self::assertSame($this->subject, $this->subject->getRelatedRecords()->getParentModel());

        $clone = clone $this->subject;

        self::assertSame($clone, $clone->getComposition()->getParentModel());
    }
}
