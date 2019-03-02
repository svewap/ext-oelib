<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
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
        $this->setExpectedException(\InvalidArgumentException::class);

        new TableLessTestingMapper();
    }

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyColumnListThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        new ColumnLessTestingMapper();
    }

    /**
     * @test
     */
    public function instantiationOfSubclassWithEmptyModelNameThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

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
        $this->setExpectedException(
            'InvalidArgumentException',
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
        $this->setExpectedException(
            'InvalidArgumentException',
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
        $this->setExpectedException(\InvalidArgumentException::class);

        $model = $this->subject->getNewGhost();
        $this->subject->load($model);
    }

    /**
     * @test
     */
    public function reloadForModelWithoutUidThrowsException()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

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
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uid must be > 0.'
        );

        $this->subject->find(0);
    }

    /**
     * @test
     */
    public function findWithNegativeUidThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
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

        static::assertInstanceOf(TestingModel::class, $result);
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
        $this->setExpectedException(
            'InvalidArgumentException',
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
        $this->setExpectedException(
            \Tx_Oelib_Exception_NotFound::class,
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
        $this->setExpectedException(
            'InvalidArgumentException',
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
        $this->setExpectedException(
            'InvalidArgumentException',
            '$key must not be empty.'
        );

        $this->subject->findOneByKeyFromCache('', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForInexistentKeyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '"foo" is not a valid key for this mapper.'
        );

        $this->subject->findOneByKeyFromCache('foo', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForEmptyValueThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$value must not be empty.'
        );

        $this->subject->findOneByKeyFromCache('title', '');
    }

    /**
     * @test
     */
    public function findOneByKeyFromCacheForModelNotInCacheThrowsException()
    {
        $this->setExpectedException(\Tx_Oelib_Exception_NotFound::class);

        $this->subject->findOneByKeyFromCache('title', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForEmptyKeyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$key must not be empty.'
        );

        $this->subject->findOneByKey('', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForInexistentKeyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '"foo" is not a valid key for this mapper.'
        );

        $this->subject->findOneByKey('foo', 'bar');
    }

    /**
     * @test
     */
    public function findOneByKeyForEmptyValueThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$value must not be empty.'
        );

        $this->subject->findOneByKey('title', '');
    }

    /*
     * Tests concerning compound key
     */

    /**
     * @test
     *
     * @expectedException \Tx_Oelib_Exception_NotFound
     */
    public function findOneByCompoundKeyFromCacheForEmptyCompoundKeyThrowsException()
    {
        $this->subject->findOneByCompoundKeyFromCache('bar');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function findOneByCompoundKeyFromCacheForEmptyValueThrowsException()
    {
        $this->subject->findOneByCompoundKeyFromCache('');
    }

    /**
     * @test
     *
     * @expectedException \Tx_Oelib_Exception_NotFound
     */
    public function findOneByCompoundKeyFromCacheForModelNotInCacheThrowsException()
    {
        $this->subject->findOneByCompoundKeyFromCache('foo.bar');
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function findOneByCompoundKeyForEmptyCompoundKeyThrowsException()
    {
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
        $this->setExpectedException(
            'InvalidArgumentException',
            '$model must have a UID.'
        );

        $model = new TestingModel();

        \Tx_Oelib_MapperRegistry::get(TestingChildMapper::class)
            ->findAllByRelation($model, 'parent');
    }

    ///////////////////////////////////
    // Tests concerning findByPageUid
    ///////////////////////////////////

    /**
     * @test
     */
    public function findByPageUidWithoutPageUidAndWithoutLimitCallsFindByWhereClauseWithoutLimit()
    {
        $subject = $this->getMock(TestingMapper::class, ['findByWhereClause']);

        $subject->expects(self::once())
            ->method('findByWhereClause')
            ->with('', '', '');

        /* @var TestingMapper $subject */
        $subject->findByPageUid('');
    }

    /**
     * @test
     */
    public function findByPageUidWithoutPageUidWithLimitCallsFindByWhereClauseWithLimit()
    {
        $subject = $this->getMock(TestingMapper::class, ['findByWhereClause']);

        $subject->expects(self::once())
            ->method('findByWhereClause')
            ->with('', '', '1,1');

        /* @var TestingMapper $subject */
        $subject->findByPageUid('', '', '1,1');
    }

    /**
     * @test
     */
    public function findByPageUidWithPageUidWithoutLimitCallsFindByWhereClauseWithoutLimit()
    {
        $subject = $this->getMock(TestingMapper::class, ['findByWhereClause']);

        $subject->expects(self::once())
            ->method('findByWhereClause')
            ->with('tx_oelib_test.pid IN (42)', '', '');

        /* @var TestingMapper $subject */
        $subject->findByPageUid('42', '', '');
    }

    /**
     * @test
     */
    public function findByPageUidWithPageUidAndLimitCallsFindByWhereClauseWithLimit()
    {
        $subject = $this->getMock(TestingMapper::class, ['findByWhereClause']);

        $subject->expects(self::once())
            ->method('findByWhereClause')
            ->with('tx_oelib_test.pid IN (42)', '', '1,1');

        /* @var TestingMapper $subject */
        $subject->findByPageUid('42', '', '1,1');
    }

    /////////////////////////////////////
    // Tests regarding countByPageUid()
    /////////////////////////////////////

    /**
     * @test
     */
    public function countByPageUidWithEmptyStringCallsCountByWhereClauseWithEmptyString()
    {
        $subject = $this->getMock(TestingMapper::class, ['countByWhereClause']);
        $subject->expects(self::once())
            ->method('countByWhereClause')
            ->with('');

        /* @var TestingMapper $subject */
        $subject->countByPageUid('');
    }

    /**
     * @test
     */
    public function countByPageUidWithZeroCallsCountByWhereClauseWithEmptyString()
    {
        $subject = $this->getMock(TestingMapper::class, ['countByWhereClause']);
        $subject->expects(self::once())
            ->method('countByWhereClause')
            ->with('');

        /* @var TestingMapper $subject */
        $subject->countByPageUid('0');
    }

    /**
     * @test
     */
    public function countByPageUidWithPageUidCallsCountByWhereClauseWithWhereClauseContainingPageUid()
    {
        $subject = $this->getMock(TestingMapper::class, ['countByWhereClause']);
        $subject->expects(self::once())
            ->method('countByWhereClause')
            ->with('tx_oelib_test.pid IN (42)');

        /* @var TestingMapper $subject */
        $subject->countByPageUid('42');
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
