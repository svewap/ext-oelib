<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\ReadOnlyModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class AbstractModelTest extends UnitTestCase
{
    /**
     * @var TestingModel
     */
    private $subject = null;

    protected function setUp()
    {
        $GLOBALS['SIM_EXEC_TIME'] = 1524751343;

        $this->subject = new TestingModel();
    }

    /**
     * Loading function stub.
     *
     * @param \Tx_Oelib_Model $model
     *
     * @return void
     */
    public function load(\Tx_Oelib_Model $model)
    {
    }

    /*
     * Tests concerning __clone
     */

    /**
     * @test
     */
    public function cloneReturnsInstanceOfSameClass()
    {
        self::assertInstanceOf(
            \get_class($this->subject),
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
    public function cloneableStatusDataProvider(): array
    {
        return [
            'virgin' => [\Tx_Oelib_Model::STATUS_VIRGIN],
            'loaded' => [\Tx_Oelib_Model::STATUS_LOADED],
        ];
    }

    /**
     * @test
     *
     * @param int $status
     *
     * @dataProvider cloneableStatusDataProvider
     */
    public function cloneReturnsDirtyModel(int $status)
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
    public function cloningModelWithUidReturnsModelWithoutUid()
    {
        $this->subject->setData(['uid' => 42]);
        self::assertTrue($this->subject->hasUid());

        $clone = clone $this->subject;

        self::assertFalse($clone->hasUid());
    }

    /**
     * @test
     */
    public function clonedModelHasStringDataFromOriginal()
    {
        $this->subject->setTitle('Bon Jovi');
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

        $clone = clone $this->subject;

        self::assertSame($this->subject->getFriend(), $clone->getFriend());
    }

    /**
     * @test
     */
    public function clonedModelHasModelsFromMtoNRelationFromOriginal()
    {
        $this->subject->setData(['related_records' => new \Tx_Oelib_List()]);
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);

        $clone = clone $this->subject;

        self::assertSame($relatedRecord, $clone->getRelatedRecords()->first());
    }

    /**
     * @test
     */
    public function clonedModelHasNewInstanceOfMtoNRelation()
    {
        $this->subject->setData(['related_records' => new \Tx_Oelib_List()]);
        $relatedRecord = new TestingModel();
        $relatedRecord->setData([]);
        $this->subject->addRelatedRecord($relatedRecord);

        $clone = clone $this->subject;

        self::assertNotSame($clone->getRelatedRecords(), $this->subject->getRelatedRecords());
    }

    /**
     * @test
     */
    public function clonedModelHasNewInstanceOf1toNRelation()
    {
        $this->subject->setData(['composition' => new \Tx_Oelib_List()]);
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
    public function getWithNoDataThrowsException()
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
    public function setDataWithEmptyArrayIsAllowed()
    {
        $this->subject->setData([]);
    }

    /**
     * @test
     */
    public function getAfterSetReturnsTheSetValue()
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
    public function getAfterSetDataReturnsTheSetValue()
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
    public function setDataCalledTwoTimesThrowsAnException()
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
    public function getAfterResetDataReturnsTheSetValue()
    {
        $this->subject->resetData(['title' => 'bar']);

        self::assertSame('bar', $this->subject->getTitle());
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function resetDataCanBeCalledTwoTimes()
    {
        $this->subject->resetData(['title' => 'bar']);
        $this->subject->resetData(['title' => 'foobar']);
    }

    /**
     * @test
     */
    public function isHiddenForLoadedHiddenObjectReturnsTrue()
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
    public function isHiddenForLoadedNonHiddenObjectReturnsFalse()
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
    public function existsKeyForInexistentKeyReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->existsKey('foo')
        );
    }

    /**
     * @test
     */
    public function existsKeyForExistingKeyWithNonEmptyDataReturnsTrue()
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
    public function existsKeyForExistingKeyWithEmptyStringDataReturnsTrue()
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
    public function existsKeyForExistingKeyWithZeroDataReturnsTrue()
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
    public function existsKeyForExistingKeyWithNullDataReturnsTrue()
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
    public function getAsModelWithEmptyKeyThrowsException()
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
    public function getAsModelWithInexistentKeyReturnsNull()
    {
        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getAsModel('foo')
        );
    }

    /**
     * @test
     */
    public function getAsModelWithKeyForStringDataThrowsException()
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
    public function getAsModelReturnsNullSetViaSetData()
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
    public function getAsModelReturnsModelSetViaSetData()
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
    public function getAsModelForSelfReturnsSelf()
    {
        $this->subject->setData(
            ['foo' => $this->subject]
        );

        self::assertSame(
            $this->subject,
            $this->subject->getAsModel('foo')
        );
    }

    ////////////////////////////////
    // Tests concerning getAsList
    ////////////////////////////////

    /**
     * @test
     */
    public function getAsListWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsList('');
    }

    /**
     * @test
     */
    public function getAsListWithInexistentKeyThrowsException()
    {
        $this->expectException(
            \UnexpectedValueException::class
        );
        $this->expectExceptionMessage(
            'The data item for the key "foo" is no list instance.'
        );

        $this->subject->setData([]);

        self::assertNull(
            $this->subject->getAsList('foo')
        );
    }

    /**
     * @test
     */
    public function getAsListWithKeyForStringDataThrowsException()
    {
        $this->expectException(
            \UnexpectedValueException::class
        );
        $this->expectExceptionMessage(
            'The data item for the key "foo" is no list instance.'
        );

        $this->subject->setData(['foo' => 'bar']);

        $this->subject->getAsList('foo');
    }

    /**
     * @test
     */
    public function getAsListReturnsListSetViaSetData()
    {
        $list = new \Tx_Oelib_List();
        $this->subject->setData(
            ['foo' => $list]
        );

        self::assertSame(
            $list,
            $this->subject->getAsList('foo')
        );
    }

    /////////////////////////////
    // Tests concerning the UID
    /////////////////////////////

    /**
     * @test
     */
    public function getUidForNoUidReturnsZero()
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
    public function getUidForSetUidReturnsTheSetUid()
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
    public function getUidForSetUidViaSetDataReturnsTheSetUid()
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
    public function getUidForSetStringUidViaSetDataReturnsTheSetIntegerUid()
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
    public function hasUidForNoUidReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasUid()
        );
    }

    /**
     * @test
     */
    public function hasUidForPositiveUidReturnsTrue()
    {
        $this->subject->setUid(42);

        self::assertTrue(
            $this->subject->hasUid()
        );
    }

    /**
     * @test
     */
    public function setUidTwoTimesThrowsAnException()
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
    public function setUidForAModelWithAUidSetViaSetDataThrowsException()
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
    public function setUidForAModelWithoutUidDoesNotFail()
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
    public function initiallyHasVirginState()
    {
        self::assertTrue(
            $this->subject->isVirgin()
        );
    }

    /**
     * @test
     */
    public function afterSettingDataWithoutUidHasLoadedState()
    {
        $this->subject->setData([]);

        self::assertTrue(
            $this->subject->isLoaded()
        );
    }

    /**
     * @test
     */
    public function afterSettingDataWithUidHasLoadedState()
    {
        $this->subject->setData(['uid' => 1]);

        self::assertTrue(
            $this->subject->isLoaded()
        );
    }

    /**
     * @test
     */
    public function afterSettingDataWithUidNotHasDeadState()
    {
        $this->subject->setData(['uid' => 1]);

        self::assertFalse(
            $this->subject->isDead()
        );
    }

    /**
     * @test
     */
    public function afterSettingUidWithoutDataHasGhostState()
    {
        $this->subject->setUid(1);

        self::assertTrue(
            $this->subject->isGhost()
        );
    }

    /**
     * @test
     */
    public function afterMarkAsDeadHasDeadState()
    {
        $this->subject->markAsDead();

        self::assertTrue(
            $this->subject->isDead()
        );
    }

    /**
     * @test
     */
    public function getOnAModelWithoutLoadCallbackThrowsException()
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
    public function setOnAModelInStatusGhostWithoutLoadCallbackThrowsException()
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
    public function getOnDeadModelThrowsException()
    {
        $this->expectException(\Tx_Oelib_Exception_NotFound::class);

        $this->subject->markAsDead();
        $this->subject->getTitle();
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function getUidOnDeadModelDoesNotFail()
    {
        $this->subject->markAsDead();
        $this->subject->getUid();
    }

    /**
     * @test
     */
    public function isHiddenOnDeadModelThrowsException()
    {
        $this->expectException(\Tx_Oelib_Exception_NotFound::class);

        $this->subject->markAsDead();
        $this->subject->isHidden();
    }

    //////////////////////
    // Tests for isEmpty
    //////////////////////

    /**
     * @test
     */
    public function isEmptyForLoadedEmptyObjectReturnsTrue()
    {
        $this->subject->setData([]);

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForLoadedNotEmptyObjectReturnsFalse()
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
    public function isEmptyForGhostLoadsModel()
    {
        $this->subject->setData([]);
        $this->subject->setUid(1);
        $this->subject->setLoadCallback([$this, 'load']);
        $this->subject->isEmpty();

        self::assertTrue(
            $this->subject->isLoaded()
        );
    }

    /**
     * @test
     */
    public function isEmptyForGhostWithLoadedDataReturnsFalse()
    {
        $this->subject->setData(
            ['foo' => 'bar']
        );
        $this->subject->setUid(1);
        $this->subject->setLoadCallback([$this, 'load']);

        self::assertFalse(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForGhostWithoutLoadedDataReturnsTrue()
    {
        $this->subject->setUid(1);
        $this->subject->setLoadCallback([$this, 'load']);

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyForVirginStateReturnsTrue()
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
    public function isDirtyAfterMarkAsDirtyReturnsTrue()
    {
        $this->subject->markAsDirty();

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterMarkAsCleanReturnsFalse()
    {
        $this->subject->markAsClean();

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetReturnsTrue()
    {
        $this->subject->setTitle('foo');

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataWithUidAndOtherDataReturnsFalse()
    {
        $this->subject->setData(['uid' => 42, 'title' => 'foo']);

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataOnlyWithUidReturnsFalse()
    {
        $this->subject->setData(['uid' => 42, 'title' => 'foo']);

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyAfterSetDataForAModelAlreadyHavingAUidReturnsFalse()
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
    public function isDirtyAfterSetDataWithoutUidReturnsTrue()
    {
        $this->subject->setData(['title' => 'foo']);

        self::assertTrue(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyOnModelInVirginStateReturnsFalse()
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
    public function isDirtyOnModelInGhostStateReturnsFalse()
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
    public function isDirtyOnInitiallyDeadModelReturnsFalse()
    {
        $this->subject->markAsDead();

        self::assertFalse(
            $this->subject->isDirty()
        );
    }

    /**
     * @test
     */
    public function isDirtyOnModelWhichTurnedIntoDeadStateReturnsFalse()
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
    public function setToDeletedOnVirginModelMarksModelAsDead()
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
    public function setToDeletedOnGhostModelMarksModelAsDead()
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
    public function setToDeletedOnLoadedModelMarksModelAsDirty()
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
    public function setToDeletedOnLoadedModelMarksModelAsDeleted()
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
    public function settingDeletedByUsingSetThrowsAnException()
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
    public function isDeletedForModelSetToDeletedReturnsTrue()
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
    public function isDeletedForNonDeletedModelReturnsFalse()
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
    public function isReadOnlyOnReadWriteModelReturnsFalse()
    {
        self::assertFalse(
            $this->subject->isReadOnly()
        );
    }

    /**
     * @test
     */
    public function isReadOnlyOnReadOnlyModelReturnsTrue()
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
    public function setDataOnReadOnlyModelDoesNotFail()
    {
        $model = new ReadOnlyModel();
        $model->setData([]);
    }

    /**
     * @test
     */
    public function setOnReadOnlyModelThrowsException()
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
    public function getDataForNoDataSetReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getData()
        );
    }

    /**
     * @test
     */
    public function getDataReturnsArrayWithTheSetData()
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
    public function getDataReturnsArrayWithoutKeyUid()
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
    public function getModificationDateAsUnixTimeStampByDefaultReturnsZero()
    {
        $this->subject->setData([]);

        self::assertSame(0, $this->subject->getModificationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getModificationDateAsUnixTimeStampReturnsModificationDate()
    {
        $date = 124445;
        $this->subject->setData(['tstamp' => $date]);

        self::assertSame($date, $this->subject->getModificationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setTimestampForLoadedModelSetsTheTimestamp()
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
    public function getCreationDateAsUnixTimeStampByDefaultReturnsZero()
    {
        $this->subject->setData([]);

        self::assertSame(0, $this->subject->getCreationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function getCreationDateAsUnixTimeStampReturnsCreationDate()
    {
        $date = 124445;
        $this->subject->setData(['crdate' => $date]);

        self::assertSame($date, $this->subject->getCreationDateAsUnixTimeStamp());
    }

    /**
     * @test
     */
    public function setCreationDateForLoadedModelWithUidThrowsException()
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
    public function setCreationDateForLoadedModelWithoutUidSetsCreation()
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
    public function getPageUidForNoPageUidSetReturnsZero()
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
    public function getPageUidReturnsPageUid()
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
    public function setPageUidSetsPageUid()
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
    public function setPageUidWithZeroPageUidNotThrowsException()
    {
        $this->subject->setPageUid(0);
    }

    /**
     * @test
     */
    public function setPageUidWithNegativePageUidThrowsException()
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
    public function markAsHiddenMarksVisibleModelAsHidden()
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
    public function markAsVisibleMarksHiddenModelAsNotHidden()
    {
        $this->subject->setData(['hidden' => true]);

        $this->subject->markAsVisible();

        self::assertFalse(
            $this->subject->isHidden()
        );
    }

    /*
     * Tests concerning __clone
     */

    /**
     * @test
     */
    public function cloneOfReadOnlyModelThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->markAsReadOnly();

        clone $this->subject;
    }

    /**
     * @return int[][]
     */
    public function uncloneableStatusDataProvider(): array
    {
        return [
            'loading' => [\Tx_Oelib_Model::STATUS_LOADING],
            'deleted' => [\Tx_Oelib_Model::STATUS_DEAD],
        ];
    }

    /**
     * @test
     *
     * @param int $status
     *
     * @dataProvider uncloneableStatusDataProvider
     */
    public function cloneWithInvalidStatusThrowsException(int $status)
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->setLoadStatus($status);

        clone $this->subject;
    }
}
