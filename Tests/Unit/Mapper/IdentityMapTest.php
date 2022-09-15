<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\IdentityMap;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * @covers \OliverKlee\Oelib\Mapper\IdentityMap
 */
class IdentityMapTest extends UnitTestCase
{
    /**
     * @var IdentityMap
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new IdentityMap();
    }

    //////////////////////////
    // Tests for get and add
    //////////////////////////

    /**
     * @test
     */
    public function getWithZeroUidThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uid must be > 0.'
        );

        $this->subject->get(0);
    }

    /**
     * @test
     */
    public function getWithNegativeUidThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$uid must be > 0.'
        );

        $this->subject->get(-1);
    }

    /**
     * @test
     */
    public function addWithModelWithoutUidThrowsException(): void
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'Add() requires a model that has a UID.'
        );

        $model = new TestingModel();
        $model->setData([]);

        $this->subject->add($model);
    }

    /**
     * @test
     */
    public function getWithExistingUidAfterAddWithModelHavingAUidReturnsSameObject(): void
    {
        $model = new TestingModel();
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
    public function addForExistingUidReturnsModelWithGivenUidForSeveralUids(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);

        $model2 = new TestingModel();
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
    public function getForExistingUidAfterAddingTwoModelsWithSameUidReturnsTheLastAddedModel(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);

        $model2 = new TestingModel();
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
    public function getForInexistentUidThrowsNotFoundException(): void
    {
        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage(
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
    public function getNewUidForEmptyMapReturnsOne(): void
    {
        self::assertSame(1, $this->subject->getNewUid());
    }

    /**
     * @test
     */
    public function getNewUidForNonEmptyMapReturnsUidNotInMap(): void
    {
        $this->expectException(NotFoundException::class);

        $model = new TestingModel();
        $model->setUid(1);
        $this->subject->add($model);

        $newUid = $this->subject->getNewUid();

        $this->subject->get($newUid);
    }

    /**
     * @test
     */
    public function getNewUidForNonEmptyMapReturnsUidGreaterThanGreatestUid(): void
    {
        $model = new TestingModel();
        $model->setUid(42);
        $this->subject->add($model);

        self::assertGreaterThan(42, $this->subject->getNewUid());
    }

    /**
     * @test
     */
    public function getNewUidForMapWithTwoItemsInReverseOrderReturnsUidGreaterThanTheGreatesUid(): void
    {
        $model2 = new TestingModel();
        $model2->setUid(2);
        $this->subject->add($model2);

        $model1 = new TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);

        self::assertGreaterThan(2, $this->subject->getNewUid());
    }
}
