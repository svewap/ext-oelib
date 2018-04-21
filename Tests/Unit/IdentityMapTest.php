<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_IdentityMapTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_IdentityMap
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_IdentityMap();
    }

    //////////////////////////
    // Tests for get and add
    //////////////////////////

    /**
     * @test
     */
    public function getWithZeroUidThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uid must be > 0.'
        );

        $this->subject->get(0);
    }

    /**
     * @test
     */
    public function getWithNegativeUidThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$uid must be > 0.'
        );

        $this->subject->get(-1);
    }

    /**
     * @test
     */
    public function addWithModelWithoutUidThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'Add() requires a model that has a UID.'
        );

        $model = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model->setData([]);

        $this->subject->add($model);
    }

    /**
     * @test
     */
    public function getWithExistingUidAfterAddWithModelHavingAUidReturnsSameObject()
    {
        $model = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model->setUid(42);
        $this->subject->add($model);

        self::assertSame(
            $model,
            $this->subject->get(42)
        );
    }

    /**
     * @test
     */
    public function addForExistingUidReturnsModelWithGivenUidForSeveralUids()
    {
        $model1 = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);

        $model2 = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model2->setUid(4);
        $this->subject->add($model2);

        self::assertSame(
            1,
            $this->subject->get(1)->getUid()
        );
        self::assertSame(
            4,
            $this->subject->get(4)->getUid()
        );
    }

    /**
     * @test
     */
    public function getForExistingUidAfterAddingTwoModelsWithSameUidReturnsTheLastAddedModel()
    {
        $model1 = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);

        $model2 = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model2->setUid(1);
        $this->subject->add($model2);

        self::assertSame(
            $model2,
            $this->subject->get(1)
        );
    }

    /**
     * @test
     */
    public function getForInexistentUidThrowsNotFoundException()
    {
        $this->setExpectedException(
            \Tx_Oelib_Exception_NotFound::class,
            'This map currently does not contain a model with the UID 42.'
        );

        $this->subject->get(42);
    }

    ///////////////////////////////
    // Tests concerning getNewUid
    ///////////////////////////////

    /**
     * @test
     */
    public function getNewUidForEmptyMapReturnsOne()
    {
        self::assertSame(
            1,
            $this->subject->getNewUid()
        );
    }

    /**
     * @test
     */
    public function getNewUidForNonEmptyMapReturnsUidNotInMap()
    {
        $this->setExpectedException(\Tx_Oelib_Exception_NotFound::class);

        $model = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model->setUid(1);
        $this->subject->add($model);

        $newUid = $this->subject->getNewUid();

        $this->subject->get($newUid);
    }

    /**
     * @test
     */
    public function getNewUidForNonEmptyMapReturnsUidGreaterThanGreatestUid()
    {
        $model = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model->setUid(42);
        $this->subject->add($model);

        self::assertGreaterThan(
            42,
            $this->subject->getNewUid()
        );
    }

    /**
     * @test
     */
    public function getNewUidForMapWithTwoItemsInReverseOrderReturnsUidGreaterThanTheGreatesUid()
    {
        $model2 = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model2->setUid(2);
        $this->subject->add($model2);

        $model1 = new \Tx_Oelib_Tests_Unit_Fixtures_TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);

        self::assertGreaterThan(
            2,
            $this->subject->getNewUid()
        );
    }
}
