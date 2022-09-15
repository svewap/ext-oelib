<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\ReadOnlyModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * @covers \OliverKlee\Oelib\Model\AbstractModel
 */
class AbstractModelTest extends UnitTestCase
{
    /**
     * @var TestingModel
     */
    private $subject;

    protected function setUp(): void
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->subject = new TestingModel();
    }

    /**
     * Loading function stub.
     */
    public function load(AbstractModel $model): void
    {
    }

    private function getLoadCallback(): \Closure
    {
        return function (AbstractModel $model): void {
            $this->load($model);
        };
    }

    // Tests concerning __clone

    /**
     * @test
     */
    public function cloneReturnsInstanceOfSameClass(): void
    {
        self::assertInstanceOf(
            \get_class($this->subject),
            clone $this->subject
        );
    }

    /**
     * @test
     */
    public function cloneReturnsNewInstance(): void
    {
        self::assertNotSame(
            $this->subject,
            clone $this->subject
        );
    }

    /**
     * @return array<string, array{0: AbstractModel::STATUS_*}>
     */
    public function cloneableStatusDataProvider(): array
    {
        return [
            'virgin' => [AbstractModel::STATUS_VIRGIN],
            'loaded' => [AbstractModel::STATUS_LOADED],
        ];
    }

    /**
     * @test
     *
     * @param AbstractModel::STATUS_* $status
     *
     * @dataProvider cloneableStatusDataProvider
     */
    public function cloneReturnsDirtyModel(int $status): void
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
    public function cloningModelWithUidReturnsModelWithoutUid(): void
    {
        $this->subject->setData(['uid' => 42]);
        self::assertTrue($this->subject->hasUid());

        $clone = clone $this->subject;

        self::assertFalse($clone->hasUid());
    }

    /**
     * @test
     */
    public function clonedModelHasStringDataFromOriginal(): void
    {
        $this->subject->setTitle('Bon Jovi');
        $clone = clone $this->subject;

        self::assertSame($this->subject->getTitle(), $clone->getTitle());
    }

    /**
     * @test
     */
    public function clonedModelHasNto1RelationFromOriginal(): void
    {
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->setFriend($relatedRecord);

        $clone = clone $this->subject;

        self::assertSame($this->subject->getFriend(), $clone->getFriend());
    }

    /**
     * @test
     */
    public function clonedModelHasModelsFromMtoNRelationFromOriginal(): void
    {
        $this->subject->setData(['related_records' => new Collection()]);
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);

        $clone = clone $this->subject;

        self::assertSame($relatedRecord, $clone->getRelatedRecords()->first());
    }

    /**
     * @test
     */
    public function clonedModelHasNewInstanceOfMtoNRelation(): void
    {
        $this->subject->setData(['related_records' => new Collection()]);
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);

        $clone = clone $this->subject;

        self::assertNotSame($clone->getRelatedRecords(), $this->subject->getRelatedRecords());
    }

    /**
     * @test
     */
    public function clonedModelHasNewInstanceOf1toNRelation(): void
    {
        $this->subject->setData(['composition' => new Collection()]);
        $childRecord = new TestingChildModel();
        $childRecord->setData([]);
        $this->subject->addCompositionRecord($childRecord);

        $clone = clone $this->subject;

        self::assertNotSame($clone->getComposition(), $this->subject->getComposition());
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function getWithNoDataThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            \get_class($this->subject) . '#' . $this->subject->getUid()
            . ': Please call setData() directly after instantiation first.'
        );

        $this->subject->getTitle();
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setDataWithEmptyArrayIsAllowed(): void
    {
        $this->subject->setData([]);
    }

    /**
     * @test
     */
    public function getAfterSetReturnsTheSetValue(): void
    {
        $this->subject->setTitle('bar');

        self::assertSame(
            'bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getAfterSetDataReturnsTheSetValue(): void
    {
        $this->subject->setData(
            ['title' => 'bar']
        );

        self::assertSame(
            'bar',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function setDataCalledTwoTimesThrowsAnException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'setData must only be called once per model instance.'
        );

        $this->subject->setData(
            ['title' => 'bar']
        );
        $this->subject->setData(
            ['title' => 'bar']
        );
    }

    /**
     * @test
     */
    public function getAfterResetDataReturnsTheSetValue(): void
    {
        $this->subject->resetData(['title' => 'bar']);

        self::assertSame('bar', $this->subject->getTitle());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetDataCanBeCalledTwoTimes(): void
    {
        $this->subject->resetData(['title' => 'bar']);
        $this->subject->resetData(['title' => 'foobar']);
    }

    /**
     * @test
     */
    public function isHiddenForLoadedHiddenObjectReturnsTrue(): void
    {
        $this->subject->setData(
            ['hidden' => 1]
        );

        self::assertTrue(
            $this->subject->isHidden()
        );
    }

    /**
     * @test
     */
    public function isHiddenForLoadedNonHiddenObjectReturnsFalse(): void
    {
        $this->subject->setData(
            ['hidden' => 0]
        );

        self::assertFalse(
            $this->subject->isHidden()
        );
    }

    ///////////////////////////////
    // Tests concerning existsKey
    ///////////////////////////////

    /**
     * @test
     */
    public function existsKeyForInexistentKeyReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->existsKey('foo')
        );
    }

    /**
     * @test
     */
    public function existsKeyForExistingKeyWithNonEmptyDataReturnsTrue(): void
    {
        $this->subject->setData(
            ['foo' => 'bar']
        );

        self::assertTrue(
            $this->subject->existsKey('foo')
        );
    }

    /**
     * @test
     */
    public function existsKeyForExistingKeyWithEmptyStringDataReturnsTrue(): void
    {
        $this->subject->setData(
            ['foo' => '']
        );

        self::assertTrue(
            $this->subject->existsKey('foo')
        );
    }

    /**
     * @test
     */
    public function existsKeyForExistingKeyWithZeroDataReturnsTrue(): void
    {
        $this->subject->setData(
            ['foo' => 0]
        );

        self::assertTrue(
            $this->subject->existsKey('foo')
        );
    }

    /**
     * @test
     */
    public function existsKeyForExistingKeyWithNullDataReturnsTrue(): void
    {
        $this->subject->setData(
            ['foo' => null]
        );

        self::assertTrue(
            $this->subject->existsKey('foo')
        );
    }

    ////////////////////////////////
    // Tests concerning getAsModel
    ////////////////////////////////

    /**
     * @test
     */
    public function getAsModelWithEmptyKeyThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsModel('');
    }

    /**
     * @test
     */
    public function getAsModelWithInexistentKeyReturnsNull(): void
    {
        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getAsModel('foo')
        );
    }

    /**
     * @test
     */
    public function getAsModelWithKeyForStringDataThrowsException(): void
    {
        $this->expectException(
            \UnexpectedValueException::class
        );
        $this->expectExceptionMessage(
            'The data item for the key "foo" is no model instance.'
        );

        $this->subject->setData(['foo' => 'bar']);

        $this->subject->getAsModel('foo');
    }

    /**
     * @test
     */
    public function getAsModelReturnsNullSetViaSetData(): void
    {
        $this->subject->setData(
            ['foo' => null]
        );

        self::assertNull(
            $this->subject->getAsModel('foo')
        );
    }

    /**
     * @test
     */
    public function getAsModelReturnsModelSetViaSetData(): void
    {
        $otherModel = new TestingModel();
        $this->subject->setData(
            ['foo' => $otherModel]
        );

        self::assertSame(
            $otherModel,
            $this->subject->getAsModel('foo')
        );
    }

    /**
     * @test
     */
    public function getAsModelForSelfReturnsSelf(): void
    {
        $this->subject->setData(
            ['foo' => $this->subject]
        );

        self::assertSame(
            $this->subject,
            $this->subject->getAsModel('foo')
        );
    }

    // Tests concerning getAsCollection

    /**
     * @test
     */
    public function getAsCollectionWithEmptyKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');

        $this->subject->getAsCollection('');
    }

    /**
     * @test
     */
    public function getAsCollectionWithInexistentKeyThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The data item for the key "foo" is no collection.');

        $this->subject->setData([]);

        $this->subject->getAsCollection('foo');
    }

    /**
     * @test
     */
    public function getAsCollectionWithKeyForStringDataThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('The data item for the key "foo" is no collection.');

        $this->subject->setData(['foo' => 'bar']);

        $this->subject->getAsCollection('foo');
    }

    /**
     * @test
     */
    public function getAsCollectionReturnsCollectionSetViaSetData(): void
    {
        $list = new Collection();
        $this->subject->setData(['foo' => $list]);

        self::assertSame($list, $this->subject->getAsCollection('foo'));
    }

    /////////////////////////////
    // Tests concerning the UID
    /////////////////////////////

    /**
     * @test
     */
    public function getUidForNoUidReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getUid()
        );
    }

    /**
     * @test
     */
    public function getUidForSetUidReturnsTheSetUid(): void
    {
        $this->subject->setUid(42);

        self::assertSame(
            42,
            $this->subject->getUid()
        );
    }

    /**
     * @test
     */
    public function getUidForSetUidViaSetDataReturnsTheSetUid(): void
    {
        $this->subject->setData(['uid' => 42]);

        self::assertSame(
            42,
            $this->subject->getUid()
        );
    }

    /**
     * @test
     */
    public function getUidForSetStringUidViaSetDataReturnsTheSetIntegerUid(): void
    {
        $this->subject->setData(['uid' => '42']);

        self::assertSame(
            42,
            $this->subject->getUid()
        );
    }

    /**
     * @test
     */
    public function hasUidForNoUidReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasUid()
        );
    }

    /**
     * @test
     */
    public function hasUidForPositiveUidReturnsTrue(): void
    {
        $this->subject->setUid(42);

        self::assertTrue(
            $this->subject->hasUid()
        );
    }

    /**
     * @test
     */
    public function setUidTwoTimesThrowsAnException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'The UID of a model cannot be set a second time.'
        );
        $this->subject->setUid(42);
        $this->subject->setUid(42);
    }

    /**
     * @test
     */
    public function setUidForAModelWithAUidSetViaSetDataThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'The UID of a model cannot be set a second time.'
        );

        $this->subject->setData(['uid' => 1]);
        $this->subject->setUid(42);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setUidForAModelWithoutUidDoesNotFail(): void
    {
        $this->subject->setData([]);
        $this->subject->setUid(42);
    }

    //////////////////////////////////////
    // Tests concerning the model states
    //////////////////////////////////////

    /**
     * @test
     */
    public function initiallyHasVirginState(): void
    {
        self::assertTrue(
            $this->subject->isVirgin()
        );
    }

    /**
     * @test
     */
    public function afterSettingDataWithoutUidHasLoadedState(): void
    {
        $this->subject->setData([]);

        self::assertTrue(
            $this->subject->isLoaded()
        );
    }

    /**
     * @test
     */
    public function afterSettingDataWithUidHasLoadedState(): void
    {
        $this->subject->setData(['uid' => 1]);

        self::assertTrue(
            $this->subject->isLoaded()
        );
    }

    /**
     * @test
     */
    public function afterSettingDataWithUidNotHasDeadState(): void
    {
        $this->subject->setData(['uid' => 1]);

        self::assertFalse(
            $this->subject->isDead()
        );
    }

    /**
     * @test
     */
    public function afterSettingUidWithoutDataHasGhostState(): void
    {
        $this->subject->setUid(1);

        self::assertTrue(
            $this->subject->isGhost()
        );
    }

    /**
     * @test
     */
    public function afterMarkAsDeadHasDeadState(): void
    {
        $this->subject->markAsDead();

        self::assertTrue(
            $this->subject->isDead()
        );
    }

    /**
     * @test
     */
    public function getOnAModelWithoutLoadCallbackThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'Ghosts need a load callback function before their data can be accessed.'
        );

        $this->subject->setUid(1);
        $this->subject->getTitle();
    }

    /**
     * @test
     */
    public function setOnAModelInStatusGhostWithoutLoadCallbackThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'Ghosts need a load callback function before their data can be accessed.'
        );

        $this->subject->setUid(1);
        $this->subject->setTitle('foo');
    }

    /**
     * @test
     */
    public function getOnDeadModelThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->markAsDead();
        $this->subject->getTitle();
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getUidOnDeadModelDoesNotFail(): void
    {
        $this->subject->markAsDead();
        $this->subject->getUid();
    }

    /**
     * @test
     */
    public function isHiddenOnDeadModelThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $this->subject->markAsDead();
        $this->subject->isHidden();
    }

    //////////////////////
    // Tests for isEmpty
    //////////////////////

    /**
     * @test
     */
    public function isEmptyForLoadedEmptyObjectReturnsTrue(): void
    {
        $this->subject->setData([]);

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForLoadedNotEmptyObjectReturnsFalse(): void
    {
        $this->subject->setData(
            ['foo' => 'bar']
        );

        self::assertFalse(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForGhostLoadsModel(): void
    {
        $this->subject->setData([]);
        $this->subject->setUid(1);
        $this->subject->setLoadCallback($this->getLoadCallback());
        $this->subject->isEmpty();

        self::assertTrue(
            $this->subject->isLoaded()
        );
    }

    /**
     * @test
     */
    public function isEmptyForGhostWithLoadedDataReturnsFalse(): void
    {
        $this->subject->setData(
            ['foo' => 'bar']
        );
        $this->subject->setUid(1);
        $this->subject->setLoadCallback($this->getLoadCallback());

        self::assertFalse(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForGhostWithoutLoadedDataReturnsTrue(): void
    {
        $this->subject->setUid(1);
        $this->subject->setLoadCallback($this->getLoadCallback());

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForVirginStateReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    //////////////////////
    // Tests for isDirty
    //////////////////////

    /**
     * @test
     */
    public function isDirtyAfterMarkAsDirtyReturnsTrue(): void
    {
        $this->subject->markAsDirty();

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterMarkAsCleanReturnsFalse(): void
    {
        $this->subject->markAsClean();

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetReturnsTrue(): void
    {
        $this->subject->setTitle('foo');

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataWithUidAndOtherDataReturnsFalse(): void
    {
        $this->subject->setData(['uid' => 42, 'title' => 'foo']);

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataOnlyWithUidReturnsFalse(): void
    {
        $this->subject->setData(['uid' => 42, 'title' => 'foo']);

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataForAModelAlreadyHavingAUidReturnsFalse(): void
    {
        $this->subject->setUid(42);
        $this->subject->setData(['title' => 'foo']);

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataWithoutUidReturnsTrue(): void
    {
        $this->subject->setData(['title' => 'foo']);

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyOnModelInVirginStateReturnsFalse(): void
    {
        self::assertTrue(
            $this->subject->isVirgin()
        );
        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyOnModelInGhostStateReturnsFalse(): void
    {
        $this->subject->setUid(1);

        self::assertTrue(
            $this->subject->isGhost()
        );
        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyOnInitiallyDeadModelReturnsFalse(): void
    {
        $this->subject->markAsDead();

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyOnModelWhichTurnedIntoDeadStateReturnsFalse(): void
    {
        $this->subject->setTitle('foo');

        self::assertTrue(
            $this->subject->isDirty()
        );

        $this->subject->markAsDead();
        self::assertTrue(
            $this->subject->isDead()
        );
        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    //////////////////////////////////////////
    // Tests concerning the deleted property
    //////////////////////////////////////////

    /**
     * @test
     */
    public function setToDeletedOnVirginModelMarksModelAsDead(): void
    {
        self::assertTrue(
            $this->subject->isVirgin()
        );

        $this->subject->setToDeleted();

        self::assertTrue(
            $this->subject->isDead()
        );
    }

    /**
     * @test
     */
    public function setToDeletedOnGhostModelMarksModelAsDead(): void
    {
        $this->subject->setUid(1);

        self::assertTrue(
            $this->subject->isGhost()
        );

        $this->subject->setToDeleted();

        self::assertTrue(
            $this->subject->isDead()
        );
    }

    /**
     * @test
     */
    public function setToDeletedOnLoadedModelMarksModelAsDirty(): void
    {
        $this->subject->setData(['uid' => 1]);

        self::assertTrue(
            $this->subject->isLoaded()
        );

        $this->subject->setToDeleted();

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function setToDeletedOnLoadedModelMarksModelAsDeleted(): void
    {
        $this->subject->setData(['uid' => 1]);

        self::assertTrue(
            $this->subject->isLoaded()
        );

        $this->subject->setToDeleted();

        self::assertTrue(
            $this->subject->isDeleted()
        );
    }

    /**
     * @test
     */
    public function settingDeletedByUsingSetThrowsAnException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be "deleted". Please use setToDeleted() instead.'
        );

        $this->subject->setDeletedPropertyUsingSet();
    }

    /**
     * @test
     */
    public function isDeletedForModelSetToDeletedReturnsTrue(): void
    {
        $this->subject->setData(['uid' => 1]);

        $this->subject->setToDeleted();

        self::assertTrue(
            $this->subject->isDeleted()
        );
    }

    /**
     * @test
     */
    public function isDeletedForNonDeletedModelReturnsFalse(): void
    {
        $this->subject->setData(['uid' => 1]);

        self::assertFalse(
            $this->subject->isDeleted()
        );
    }

    //////////////////////////////////////
    // Tests concerning read-only models
    //////////////////////////////////////

    /**
     * @test
     */
    public function isReadOnlyOnReadWriteModelReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->isReadOnly()
        );
    }

    /**
     * @test
     */
    public function isReadOnlyOnReadOnlyModelReturnsTrue(): void
    {
        $model = new ReadOnlyModel();

        self::assertTrue(
            $model->isReadOnly()
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setDataOnReadOnlyModelDoesNotFail(): void
    {
        $model = new ReadOnlyModel();
        $model->setData([]);
    }

    /**
     * @test
     */
    public function setOnReadOnlyModelThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'set() must not be called on a read-only model.'
        );

        $model = new ReadOnlyModel();
        $model->setTitle('foo');
    }

    /////////////////////////////
    // Tests concerning getData
    /////////////////////////////

    /**
     * @test
     */
    public function getDataForNoDataSetReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getData()
        );
    }

    /**
     * @test
     */
    public function getDataReturnsArrayWithTheSetData(): void
    {
        $data = ['foo' => 'bar'];
        $this->subject->setData($data);

        self::assertSame(
            $data,
            $this->subject->getData()
        );
    }

    /**
     * @test
     */
    public function getDataReturnsArrayWithoutKeyUid(): void
    {
        $this->subject->setData(['uid' => 1]);

        self::assertSame(
            [],
            $this->subject->getData()
        );
    }

    /////////////////////////////////////////////////////
    // Test concerning setTimestamp and setCreationDate
    /////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getModificationDateAsUnixTimeStampByDefaultReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(0, $this->subject->getModificationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getModificationDateAsUnixTimeStampReturnsModificationDate(): void
    {
        $date = 124445;
        $this->subject->setData(['tstamp' => $date]);

        self::assertSame($date, $this->subject->getModificationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setTimestampForLoadedModelSetsTheTimestamp(): void
    {
        $this->subject->setData([]);
        $this->subject->setTimestamp();

        self::assertSame(
            $GLOBALS['SIM_EXEC_TIME'],
            $this->subject->getModificationDateAsUnixTimeStamp()
        );
    }

    /**
     * @test
     */
    public function getCreationDateAsUnixTimeStampByDefaultReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(0, $this->subject->getCreationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getCreationDateAsUnixTimeStampReturnsCreationDate(): void
    {
        $date = 124445;
        $this->subject->setData(['crdate' => $date]);

        self::assertSame($date, $this->subject->getCreationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setCreationDateForLoadedModelWithUidThrowsException(): void
    {
        $this->expectException(
            \BadMethodCallException::class
        );
        $this->expectExceptionMessage(
            'Only new objects (without UID) may receive "crdate".'
        );

        $this->subject->setData(['uid' => 1]);
        $this->subject->setCreationDate();
    }

    /**
     * @test
     */
    public function setCreationDateForLoadedModelWithoutUidSetsCreation(): void
    {
        $this->subject->setData([]);
        $this->subject->setCreationDate();

        self::assertSame(
            $GLOBALS['SIM_EXEC_TIME'],
            $this->subject->getCreationDateAsUnixTimeStamp()
        );
    }

    ////////////////////////////////
    // Tests concerning getPageUid
    ////////////////////////////////

    /**
     * @test
     */
    public function getPageUidForNoPageUidSetReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getPageUid()
        );
    }

    /**
     * @test
     */
    public function getPageUidReturnsPageUid(): void
    {
        $this->subject->setData(['pid' => 42]);

        self::assertSame(
            42,
            $this->subject->getPageUid()
        );
    }

    /**
     * @test
     */
    public function setPageUidSetsPageUid(): void
    {
        $this->subject->setPageUid(84);

        self::assertSame(
            84,
            $this->subject->getPageUid()
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setPageUidWithZeroPageUidNotThrowsException(): void
    {
        $this->subject->setPageUid(0);
    }

    /**
     * @test
     */
    public function setPageUidWithNegativePageUidThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPageUid(-1);
    }

    //////////////////////////////////////////////////////////
    // Tests concerning the setting of the "hidden" property
    //////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function markAsHiddenMarksVisibleModelAsHidden(): void
    {
        $this->subject->setData(['hidden' => false]);

        $this->subject->markAsHidden();

        self::assertTrue(
            $this->subject->isHidden()
        );
    }

    /**
     * @test
     */
    public function markAsVisibleMarksHiddenModelAsNotHidden(): void
    {
        $this->subject->setData(['hidden' => true]);

        $this->subject->markAsVisible();

        self::assertFalse(
            $this->subject->isHidden()
        );
    }

    // Tests concerning __clone

    /**
     * @test
     */
    public function cloneOfReadOnlyModelThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->markAsReadOnly();

        clone $this->subject;
    }

    /**
     * @return array<string, array{0: AbstractModel::STATUS_*}>
     */
    public function uncloneableStatusDataProvider(): array
    {
        return [
            'loading' => [AbstractModel::STATUS_LOADING],
            'deleted' => [AbstractModel::STATUS_DEAD],
        ];
    }

    /**
     * @test
     *
     * @param AbstractModel::STATUS_* $status
     *
     * @dataProvider uncloneableStatusDataProvider
     */
    public function cloneWithInvalidStatusThrowsException(int $status): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->setLoadStatus($status);

        clone $this->subject;
    }
}
