<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * @covers \OliverKlee\Oelib\Model\AbstractModel
 */
class AbstractModelTest extends FunctionalTestCase
{
    /**
     * @var string
     */
    private const TEST_RECORD_TITLE = 'Hello world';

    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingModel
     */
    private $subject = null;

    /**
     * @var TestingMapper
     */
    protected $dataMapper = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dataMapper = MapperRegistry::get(TestingMapper::class);

        $uid = $this->createTestRecord();
        $subject = $this->dataMapper->find($uid);
        $this->subject = $subject;
    }

    /**
     * Creates a test record.
     *
     * @return int the UID
     */
    private function createTestRecord(): int
    {
        $this->getDatabaseConnection()->insertArray('tx_oelib_test', ['title' => self::TEST_RECORD_TITLE]);
        return (int)$this->getDatabaseConnection()->lastInsertId();
    }

    // Tests concerning __clone

    /**
     * @test
     */
    public function cloneReturnsDirtyModel(): void
    {
        $this->subject->setLoadStatus(AbstractModel::STATUS_GHOST);

        $clone = clone $this->subject;
        self::assertTrue(
            $clone->isDirty()
        );
    }

    /**
     * @test
     */
    public function cloningVirginModelReturnsVirginModel(): void
    {
        $subject = new TestingModel();
        self::assertTrue($subject->isVirgin());

        $clone = clone $subject;

        self::assertTrue($clone->isVirgin());
    }

    /**
     * @test
     */
    public function cloningGhostLoadsModel(): void
    {
        self::assertTrue($this->subject->isGhost());

        $clone = clone $this->subject;

        self::assertTrue($clone->isLoaded());
    }

    /**
     * @test
     */
    public function cloningLoadedModelReturnsLoadedModel(): void
    {
        self::assertSame(self::TEST_RECORD_TITLE, $this->subject->getTitle());
        self::assertTrue($this->subject->isLoaded());

        $clone = clone $this->subject;

        self::assertTrue($clone->isLoaded());
    }

    /**
     * @test
     */
    public function clonedModelHasMtoNRelationWithCloneAsParentModel(): void
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
    public function clonedModelHasClonesOfModelsFrom1toNRelationFromOriginal(): void
    {
        $childRecord = new TestingChildModel();
        $childRecordTitle = 'bubble bobble';
        $childRecord->setTitle($childRecordTitle);
        $this->subject->addCompositionRecord($childRecord);
        $this->dataMapper->save($this->subject);

        /** @var TestingChildModel $firstCloneChild */
        $firstCloneChild = (clone $this->subject)->getComposition()->first();
        self::assertSame($childRecord->getTitle(), $firstCloneChild->getTitle());
        self::assertNotSame($childRecord, $firstCloneChild);
    }

    /**
     * @test
     */
    public function clonedModelHas1toNRelationWithCloneAsParentModel(): void
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
