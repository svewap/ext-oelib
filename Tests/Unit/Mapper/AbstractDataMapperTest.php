<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\ColumnLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\ModelLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TableLessTestingMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingChildMapper;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AbstractDataMapperTest extends UnitTestCase
{
    /**
     * @var TestingMapper
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingMapper();
    }

    ///////////////////////////////////////
    // Tests concerning the instantiation
    ///////////////////////////////////////

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyTableNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new TableLessTestingMapper();
    }

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyColumnListThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ColumnLessTestingMapper();
    }

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyModelNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        new ModelLessTestingMapper();
    }

    //////////////////////////////
    // Tests concerning getModel
    //////////////////////////////

    /**
     * @test
     */
    public function getModelWithArrayWithoutUidElementProvidedThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$data must contain an element "uid".'
        );

        $this->subject->getModel([]);
    }

    /*
     * Tests concerning load and reload
     */

    /**
     * @test
     */
    public function loadWithModelWithoutUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'load must only be called with models that already have a UID.'
        );

        $model = new TestingModel();
        $this->subject->load($model);
    }

    /**
     * @test
     */
    public function reloadForTestingOnlyGhostThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $model = $this->subject->getNewGhost();
        $this->subject->load($model);
    }

    /**
     * @test
     */
    public function reloadForModelWithoutUidThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $model = new TestingModel();
        $this->subject->load($model);
    }

    //////////////////////////////////////
    // Tests concerning the model states
    //////////////////////////////////////

    /**
     * @test
     */
    public function findInitiallyReturnsGhostModel()
    {
        $uid = 42;

        self::assertTrue(
            $this->subject->find($uid)->isGhost()
        );
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithZeroUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uid must be > 0.'
        );

        $this->subject->find(0);
    }

    /**
     * @test
     */
    public function findWithNegativeUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uid must be > 0.'
        );

        $this->subject->find(-1);
    }

    /**
     * @test
     */
    public function findWithUidOfCachedModelReturnsThatModel()
    {
        $model = new TestingModel();
        $model->setUid(1);

        $map = new \Tx_Oelib_IdentityMap();
        $map->add($model);
        $this->subject->setMap($map);

        self::assertSame(
            $model,
            $this->subject->find(1)
        );
    }

    /**
     * @test
     */
    public function findWithUidReturnsModelWithThatUid()
    {
        $uid = 42;

        self::assertSame(
            $uid,
            $this->subject->find($uid)->getUid()
        );
    }

    /**
     * @test
     */
    public function findWithUidCalledTwoTimesReturnsSameModel()
    {
        $uid = 42;

        self::assertSame(
            $this->subject->find($uid),
            $this->subject->find($uid)
        );
    }

    /////////////////////////////////
    // Tests concerning getNewGhost
    /////////////////////////////////

    /**
     * @test
     */
    public function getNewGhostReturnsModel()
    {
        self::assertInstanceOf(\Tx_Oelib_Model::class, $this->subject->getNewGhost());
    }

    /**
     * @test
     */
    public function getNewGhostReturnsModelSpecificToTheMapper()
    {
        $result = $this->subject->getNewGhost();

        self::assertInstanceOf(TestingModel::class, $result);
    }

    /**
     * @test
     */
    public function getNewGhostReturnsGhost()
    {
        self::assertTrue(
            $this->subject->getNewGhost()->isGhost()
        );
    }

    /**
     * @test
     */
    public function getNewGhostReturnsModelWithUid()
    {
        self::assertTrue(
            $this->subject->getNewGhost()->hasUid()
        );
    }

    /**
     * @test
     */
    public function getNewGhostCreatesRegisteredModel()
    {
        $ghost = $this->subject->getNewGhost();

        self::assertSame(
            $ghost,
            $this->subject->find($ghost->getUid())
        );
    }

    /**
     * @test
     */
    public function loadingAGhostCreatedWithGetNewGhostThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'This ghost was created via getNewGhost and must not be loaded.'
        );

        $ghost = $this->subject->getNewGhost();
        $this->subject->load($ghost);
    }

    //////////////////////////////////////////////
    // Tests concerning disabled database access
    //////////////////////////////////////////////

    /**
     * @test
     */
    public function hasDatabaseAccessInitiallyReturnsTrue()
    {
        self::assertTrue(
            $this->subject->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function hasDatabaseAccessAfterDisableDatabaseAccessReturnsFalse()
    {
        $this->subject->disableDatabaseAccess();

        self::assertFalse(
            $this->subject->hasDatabaseAccess()
        );
    }

    /**
     * @test
     */
    public function findSingleByWhereClauseAndDatabaseAccessDisabledThrowsException()
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
            'No record can be retrieved from the database because database ' .
            'access is disabled for this mapper instance.'
        );

        $this->subject->disableDatabaseAccess();
        $this->subject->findSingleByWhereClause(['title' => 'foo']);
    }

    ////////////////////////////////////////////////
    // Tests concerning findSingleByWhereClause().
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function findSingleByWhereClauseWithEmptyWhereClausePartsThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The parameter $whereClauseParts must not be empty.'
        );

        $this->subject->findSingleByWhereClause([]);
    }

    /////////////////////////////////////
    // Tests concerning additional keys
    /////////////////////////////////////

    /**
     * @test
     */
    public function findOneByKeyFromCacheForEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->findOneByKeyFromCache('', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForInexistentKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '"foo" is not a valid key for this mapper.'
        );

        $this->subject->findOneByKeyFromCache('foo', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForEmptyValueThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$value must not be empty.'
        );

        $this->subject->findOneByKeyFromCache('title', '');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForModelNotInCacheThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findOneByKeyFromCache('title', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->findOneByKey('', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForInexistentKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '"foo" is not a valid key for this mapper.'
        );

        $this->subject->findOneByKey('foo', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForEmptyValueThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$value must not be empty.'
        );

        $this->subject->findOneByKey('title', '');
    }

    /*
     * Tests concerning compound key
     */

    /**
     * @test
     */
    public function findOneByCompoundKeyFromCacheForEmptyCompoundKeyThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findOneByCompoundKeyFromCache('bar');
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyFromCacheForEmptyValueThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findOneByCompoundKeyFromCache('');
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyFromCacheForModelNotInCacheThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->subject->findOneByCompoundKeyFromCache('foo.bar');
    }

    /**
     * @test
     */
    public function findOneByCompoundKeyForEmptyCompoundKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->findOneByCompoundKey([]);
    }

    ///////////////////////////////////////
    // Tests concerning findAllByRelation
    ///////////////////////////////////////

    /**
     * @test
     */
    public function findAllByRelationWithModelWithoutUidThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$model must have a UID.'
        );

        $model = new TestingModel();

        \Tx_Oelib_MapperRegistry::get(TestingChildMapper::class)
            ->findAllByRelation($model, 'parent');
    }

    /**
     * @test
     */
    public function getTableNameReturnsTableName()
    {
        self::assertSame(
            'tx_oelib_test',
            $this->subject->getTableName()
        );
    }
}
