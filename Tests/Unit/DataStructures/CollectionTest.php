<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingChildModel;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * @covers \OliverKlee\Oelib\DataStructures\Collection
 */
final class CollectionTest extends UnitTestCase
{
    /**
     * @var Collection<TestingModel>
     */
    private $subject = null;

    protected function setUp(): void
    {
        /** @var Collection<TestingModel> $subject */
        $subject = new Collection();
        $this->subject = $subject;
    }

    /**
     * @param TestingModel $firstModel
     * @param TestingModel $secondModel
     *
     * @return int
     */
    public function sortByTitleAscending(TestingModel $firstModel, TestingModel $secondModel): int
    {
        return strcmp($firstModel->getTitle(), $secondModel->getTitle());
    }

    /**
     * @param TestingModel $firstModel
     * @param TestingModel $secondModel
     *
     * @return int
     */
    public function sortByTitleDescending(TestingModel $firstModel, TestingModel $secondModel): int
    {
        return strcmp($secondModel->getTitle(), $firstModel->getTitle());
    }

    /**
     * Adds models with the given titles to the subject, one for each title
     * given in $titles.
     *
     * @param array<int, string> $titles the titles for the models, must not be empty
     */
    private function addModelsToFixture(array $titles = ['']): void
    {
        foreach ($titles as $title) {
            $model = new TestingModel();
            $model->setTitle($title);
            $this->subject->add($model);
        }
    }

    /**
     * @test
     */
    public function sortByTitleAscendingForFirstModelTitleAlphaAndSecondModelTitleBetaReturnsMinusOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');
        $secondModel = new TestingModel();
        $secondModel->setTitle('beta');

        self::assertSame(
            -1,
            $this->sortByTitleAscending($firstModel, $secondModel)
        );
    }

    /**
     * @test
     */
    public function sortByTitleAscendingForFirstModelTitleBetaAndSecondModelTitleAlphaReturnsOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('beta');
        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            1,
            $this->sortByTitleAscending($firstModel, $secondModel)
        );
    }

    /**
     * @test
     */
    public function sortByTitleAscendingForFirstAndSecondModelTitleSameReturnsZero(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');
        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            0,
            $this->sortByTitleAscending($firstModel, $secondModel)
        );
    }

    ///////////////////////////////////////////
    // Tests concerning sortByTitleDescending
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function sortByTitleDescendingForFirstModelTitleAlphaAndSecondModelTitleBetaReturnsOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');
        $secondModel = new TestingModel();
        $secondModel->setTitle('beta');

        self::assertSame(
            1,
            $this->sortByTitleDescending($firstModel, $secondModel)
        );
    }

    /**
     * @test
     */
    public function sortByTitleDescendingForFirstModelTitleBetaAndSecondModelTitleAlphaReturnsMinusOne(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('beta');
        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            -1,
            $this->sortByTitleDescending($firstModel, $secondModel)
        );
    }

    /**
     * @test
     */
    public function sortByTitleDescendingForFirstAndSecondModelTitleSameReturnsZero(): void
    {
        $firstModel = new TestingModel();
        $firstModel->setTitle('alpha');
        $secondModel = new TestingModel();
        $secondModel->setTitle('alpha');

        self::assertSame(
            0,
            $this->sortByTitleDescending($firstModel, $secondModel)
        );
    }

    /**
     * @test
     */
    public function addModelsToFixtureForOneGivenTitleAddsOneModelToFixture(): void
    {
        $this->addModelsToFixture(['foo']);

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function addModelsToFixtureForOneGivenTitleAddsModelWithTitleGiven(): void
    {
        $this->addModelsToFixture(['foo']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'foo',
            $firstItem->getTitle()
        );
    }

    /**
     * @test
     */
    public function addModelsToFixtureForTwoGivenTitlesAddsTwoModelsToFixture(): void
    {
        $this->addModelsToFixture(['foo', 'bar']);

        self::assertCount(2, $this->subject);
    }

    /**
     * @test
     */
    public function addModelsToFixtureForTwoGivenTitlesAddsFirstTitleToFirstModelFixture(): void
    {
        $this->addModelsToFixture(['bar', 'foo']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'bar',
            $firstItem->getTitle()
        );
    }

    /**
     * @test
     */
    public function addModelsToFixtureForThreeGivenTitlesAddsThreeModelsToFixture(): void
    {
        $this->addModelsToFixture(['foo', 'bar', 'fooBar']);

        self::assertCount(3, $this->subject);
    }

    /**
     * @test
     */
    public function isEmptyForEmptyListReturnsTrue(): void
    {
        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function isEmptyAfterAddingModelReturnsFalse(): void
    {
        $this->addModelsToFixture();

        self::assertFalse(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function countForEmptyListReturnsZero(): void
    {
        self::assertCount(0, $this->subject);
    }

    /**
     * @test
     */
    public function countWithOneModelWithoutUidReturnsOne(): void
    {
        $this->addModelsToFixture();

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function countWithOneModelWithUidReturnsOne(): void
    {
        $model = new TestingModel();
        $model->setUid(1);
        $this->subject->add($model);

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function countWithTwoDifferentModelsReturnsTwo(): void
    {
        $this->addModelsToFixture(['', '']);

        self::assertCount(2, $this->subject);
    }

    /**
     * @test
     */
    public function countAfterAddingTheSameModelTwiceReturnsOne(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $this->subject->add($model);

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function currentForEmptyListReturnsNull(): void
    {
        if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
            self::markTestSkipped(
                'This test only makes sense for PHP < 8.1.0.' .
                'Above that, PHP will complain about calling current() on an invalid iterator.'
            );
        }

        /** @var object|null $current */
        $current = $this->subject->current();
        self::assertNull($current);
    }

    /**
     * @test
     */
    public function currentWithOneItemReturnsThatItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            $model,
            $this->subject->current()
        );
    }

    /**
     * @test
     */
    public function currentWithTwoItemsInitiallyReturnsTheFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            $model1,
            $this->subject->current()
        );
    }

    /**
     * @test
     */
    public function keyInitiallyReturnsZero(): void
    {
        self::assertSame(
            0,
            $this->subject->key()
        );
    }

    /**
     * @test
     */
    public function keyAfterNextInListWithOneElementReturnsOne(): void
    {
        $this->addModelsToFixture();
        $this->subject->next();

        self::assertSame(
            1,
            $this->subject->key()
        );
    }

    /**
     * @test
     */
    public function currentWithOneItemAfterNextReturnsNull(): void
    {
        if (version_compare(PHP_VERSION, '8.1.0') >= 0) {
            self::markTestSkipped(
                'This test only makes sense for PHP < 8.1.0.' .
                'Above that, PHP will complain about calling current() on an invalid iterator.'
            );
        }

        $this->addModelsToFixture();

        $this->subject->next();

        /** @var object|null $current */
        $current = $this->subject->current();
        self::assertNull($current);
    }

    /**
     * @test
     */
    public function currentWithTwoItemsAfterNextReturnsTheSecondItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        $this->subject->next();

        self::assertSame(
            $model2,
            $this->subject->current()
        );
    }

    /**
     * @test
     */
    public function rewindAfterNextResetsKeyToZero(): void
    {
        $this->subject->next();
        $this->subject->rewind();

        self::assertSame(
            0,
            $this->subject->key()
        );
    }

    /**
     * @test
     */
    public function rewindAfterNextForOneItemsResetsCurrentToTheOnlyItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        $this->subject->next();
        $this->subject->rewind();

        self::assertSame(
            $model,
            $this->subject->current()
        );
    }

    /**
     * @test
     */
    public function firstForEmptyListReturnsNull(): void
    {
        self::assertNull(
            $this->subject->first()
        );
    }

    /**
     * @test
     */
    public function firstForListWithOneItemReturnsThatItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            $model,
            $this->subject->first()
        );
    }

    /**
     * @test
     */
    public function firstWithTwoItemsReturnsTheFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            $model1,
            $this->subject->first()
        );
    }

    /**
     * @test
     */
    public function firstWithTwoItemsAfterNextReturnsTheFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        $this->subject->next();

        self::assertSame(
            $model1,
            $this->subject->first()
        );
    }

    /**
     * @test
     */
    public function validForEmptyListReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->valid()
        );
    }

    /**
     * @test
     */
    public function validForOneElementInitiallyReturnsTrue(): void
    {
        $this->addModelsToFixture();

        self::assertTrue(
            $this->subject->valid()
        );
    }

    /**
     * @test
     */
    public function validForOneElementAfterNextReturnsFalse(): void
    {
        $this->addModelsToFixture();

        $this->subject->next();

        self::assertFalse(
            $this->subject->valid()
        );
    }

    /**
     * @test
     */
    public function validForOneElementAfterNextAndRewindReturnsTrue(): void
    {
        $this->addModelsToFixture();

        $this->subject->next();
        $this->subject->rewind();

        self::assertTrue(
            $this->subject->valid()
        );
    }

    /**
     * @test
     */
    public function isIterator(): void
    {
        self::assertInstanceOf(\Iterator::class, $this->subject);
    }

    /**
     * @test
     */
    public function getUidsForEmptyListReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function getUidsForOneItemsWithoutUidReturnsEmptyString(): void
    {
        $this->addModelsToFixture();

        self::assertSame(
            '',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function getUidsForOneItemsWithUidReturnsThatUid(): void
    {
        $model = new TestingModel();
        $model->setUid(1);
        $this->subject->add($model);

        self::assertSame(
            '1',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function getUidsForTwoItemsWithUidReturnsCommaSeparatedItems(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $model2->setUid(42);
        $this->subject->add($model2);

        self::assertSame(
            '1,42',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function getUidsForTwoItemsWithDecreasingUidReturnsItemsInOrdnerOfInsertion(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(42);
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $model2->setUid(1);
        $this->subject->add($model2);

        self::assertSame(
            '42,1',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function getUidsForDuplicateUidsReturnsUidsInOrdnerOfFirstInsertion(): void
    {
        $model1 = new TestingModel();
        $model1->setUid(1);
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $model2->setUid(2);
        $this->subject->add($model2);

        $this->subject->add($model1);

        self::assertSame(
            '1,2',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function getUidsForElementThatGotItsUidAfterAddingItReturnsItsUid(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $model->setUid(42);

        self::assertSame(
            '42',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function hasUidForInexistentUidReturnsFalse(): void
    {
        self::assertFalse(
            $this->subject->hasUid(42)
        );
    }

    /**
     * @test
     */
    public function hasUidForExistingUidReturnsTrue(): void
    {
        $model = new TestingModel();
        $model->setUid(42);
        $this->subject->add($model);

        self::assertTrue(
            $this->subject->hasUid(42)
        );
    }

    /**
     * @test
     */
    public function hasUidForElementThatGotItsUidAfterAddingItReturnsTrue(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $model->setUid(42);

        self::assertTrue(
            $this->subject->hasUid(42)
        );
    }

    /**
     * @test
     */
    public function sortWithTwoModelsAndSortByTitleAscendingFunctionSortsModelsByTitleAscending(): void
    {
        $this->addModelsToFixture(['Beta', 'Alpha']);
        $this->subject->sort([$this, 'sortByTitleAscending']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'Alpha',
            $firstItem->getTitle()
        );
    }

    /**
     * @test
     */
    public function sortWithThreeModelsAndSortByTitleAscendingFunctionSortsModelsByTitleAscending(): void
    {
        $this->addModelsToFixture(['Zeta', 'Beta', 'Alpha']);
        $this->subject->sort([$this, 'sortByTitleAscending']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'Alpha',
            $firstItem->getTitle()
        );
    }

    /**
     * @test
     */
    public function sortWithTwoModelsAndSortByTitleDescendingFunctionSortsModelsByTitleDescending(): void
    {
        $this->addModelsToFixture(['Alpha', 'Beta']);
        $this->subject->sort([$this, 'sortByTitleDescending']);

        /** @var TestingModel $firstItem */
        $firstItem = $this->subject->first();
        self::assertSame(
            'Beta',
            $firstItem->getTitle()
        );
    }

    /**
     * @test
     */
    public function sortMakesListDirty(): void
    {
        $subject = $this->createPartialMock(Collection::class, ['markAsDirty']);
        $subject->expects(self::once())->method('markAsDirty');

        $subject->sort([$this, 'sortByTitleAscending']);
    }

    /**
     * @test
     */
    public function appendEmptyListToEmptyListMakesEmptyList(): void
    {
        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $this->subject->append($otherList);

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function appendTwoItemListToEmptyListMakesTwoItemList(): void
    {
        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $model1 = new TestingModel();
        $otherList->add($model1);
        $model2 = new TestingModel();
        $otherList->add($model2);

        $this->subject->append($otherList);

        self::assertCount(2, $this->subject);
    }

    /**
     * @test
     */
    public function appendEmptyListToTwoItemListMakesTwoItemList(): void
    {
        $this->addModelsToFixture(['First', 'Second']);

        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $this->subject->append($otherList);

        self::assertCount(2, $this->subject);
    }

    /**
     * @test
     */
    public function appendOneItemListToOneItemListWithTheSameItemMakesOneItemList(): void
    {
        $model = new TestingModel();
        $model->setUid(42);
        $this->subject->add($model);

        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $otherList->add($model);

        $this->subject->append($otherList);

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function appendTwoItemListKeepsOrderOfAppendedItems(): void
    {
        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $model1 = new TestingModel();
        $otherList->add($model1);
        $model2 = new TestingModel();
        $otherList->add($model2);

        $this->subject->append($otherList);

        self::assertSame(
            $model1,
            $this->subject->first()
        );
    }

    /**
     * @test
     */
    public function appendAppendsItemAfterExistingItems(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        /** @var Collection<TestingModel> $otherList */
        $otherList = new Collection();
        $otherModel = new TestingModel();
        $otherList->add($otherModel);

        $this->subject->append($otherList);

        self::assertSame(
            $model,
            $this->subject->first()
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function purgeCurrentWithEmptyListDoesNotFail(): void
    {
        $this->subject->purgeCurrent();
    }

    /**
     * @test
     */
    public function purgeCurrentWithRewoundOneElementListMakesListEmpty(): void
    {
        $this->addModelsToFixture();

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertTrue(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function purgeCurrentWithRewoundOneElementListMakesPointerInvalid(): void
    {
        $this->addModelsToFixture();

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertFalse(
            $this->subject->valid()
        );
    }

    /**
     * @test
     */
    public function purgeCurrentWithOneElementListAndPointerAfterLastItemLeavesListUntouched(): void
    {
        $this->addModelsToFixture();

        $this->subject->rewind();
        $this->subject->next();
        $this->subject->purgeCurrent();

        self::assertFalse(
            $this->subject->isEmpty()
        );
    }

    /**
     * @test
     */
    public function purgeCurrentForFirstOfTwoElementsMakesOneItemList(): void
    {
        $this->addModelsToFixture(['', '']);

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function purgeCurrentForSecondOfTwoElementsMakesOneItemList(): void
    {
        $this->addModelsToFixture(['', '']);

        $this->subject->rewind();
        $this->subject->next();
        $this->subject->purgeCurrent();

        self::assertCount(1, $this->subject);
    }

    /**
     * @test
     */
    public function purgeCurrentForFirstOfTwoElementsSetsPointerToFormerSecondElement(): void
    {
        $this->addModelsToFixture();

        $model = new TestingModel();
        $this->subject->add($model);

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertSame(
            $model,
            $this->subject->current()
        );
    }

    /**
     * @test
     */
    public function purgeCurrentForSecondOfTwoElementsInWhileLoopDoesNotChangeNumberOfIterations(): void
    {
        $this->addModelsToFixture(['', '']);

        $completedIterations = 0;

        while ($this->subject->valid()) {
            if ($completedIterations === 1) {
                $this->subject->purgeCurrent();
            }

            $completedIterations++;
            $this->subject->next();
        }

        self::assertSame(
            2,
            $completedIterations
        );
    }

    /**
     * @test
     */
    public function purgeCurrentForModelWithUidRemovesModelFromGetUids(): void
    {
        $model = new TestingModel();
        $model->setUid(1);
        $this->subject->add($model);

        $this->subject->rewind();
        $this->subject->purgeCurrent();

        self::assertSame(
            '',
            $this->subject->getUids()
        );
    }

    /**
     * @test
     */
    public function sortBySortingMovesItemWithHigherSortingValueAfterItemWithLowerSortingValue(): void
    {
        /** @var Collection<TestingChildModel> $subject */
        $subject = new Collection();

        $model1 = new TestingChildModel();
        $model1->setSorting(2);
        $subject->add($model1);

        $model2 = new TestingChildModel();
        $model2->setSorting(1);
        $subject->add($model2);

        $subject->sortBySorting();

        self::assertSame($model2, $subject->first());
    }

    /**
     * @test
     */
    public function atForNegativePositionThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->at(-1);
    }

    /**
     * @test
     */
    public function atForPositionZeroWithEmptyListReturnsNull(): void
    {
        self::assertNull(
            $this->subject->at(0)
        );
    }

    /**
     * @test
     */
    public function atForPositionOneWithEmptyListReturnsNull(): void
    {
        self::assertNull(
            $this->subject->at(1)
        );
    }

    /**
     * @test
     */
    public function atForPositionZeroWithOneItemListReturnsItem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            $model,
            $this->subject->at(0)
        );
    }

    /**
     * @test
     */
    public function atForPositionOneWithOneItemListReturnsNull(): void
    {
        $this->subject->add(new TestingModel());

        self::assertNull(
            $this->subject->at(1)
        );
    }

    /**
     * @test
     */
    public function atForPositionZeroWithTwoItemListReturnsFirstItem(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $this->subject->add(new TestingModel());

        self::assertSame(
            $model1,
            $this->subject->at(0)
        );
    }

    /**
     * @test
     */
    public function atForPositionOneWithTwoItemListReturnsSecondItem(): void
    {
        $this->subject->add(new TestingModel());
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            $model2,
            $this->subject->at(1)
        );
    }

    /**
     * @test
     */
    public function atForPositionTwoWithTwoItemListReturnsNull(): void
    {
        $this->subject->add(new TestingModel());
        $this->subject->add(new TestingModel());

        self::assertNull(
            $this->subject->at(2)
        );
    }

    /**
     * @test
     */
    public function inRangeWithNegativeStartThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->inRange(-1, 1);
    }

    /**
     * @test
     */
    public function inRangeWithNegativeLengthThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->inRange(1, -1);
    }

    /**
     * @test
     */
    public function inRangeWithZeroLengthReturnsEmptyList(): void
    {
        $this->subject->add(new TestingModel());
        $this->subject->add(new TestingModel());

        self::assertTrue(
            $this->subject->inRange(1, 0)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function inRangeCanReturnOneElementFromStartOfList(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
        $this->subject->add(new TestingModel());

        $result = $this->subject->inRange(0, 1);
        self::assertCount(1, $result);
        self::assertSame(
            $model,
            $result->first()
        );
    }

    /**
     * @test
     */
    public function inRangeCanReturnOneElementAfterStartOfList(): void
    {
        $model = new TestingModel();
        $this->subject->add(new TestingModel());
        $this->subject->add($model);

        $result = $this->subject->inRange(1, 1);
        self::assertCount(1, $result);
        self::assertSame(
            $model,
            $result->first()
        );
    }

    /**
     * @test
     */
    public function inRangeCanReturnTwoElementsFromStartOfList(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertCount(2, $this->subject->inRange(0, 2));
    }

    /**
     * @test
     */
    public function inRangeWithStartAfterListEndReturnsEmptyList(): void
    {
        $this->subject->add(new TestingModel());

        self::assertTrue(
            $this->subject->inRange(1, 1)->isEmpty()
        );
    }

    /**
     * @test
     */
    public function inRangeWithRangeCrossingListEndReturnsElementUpToListEnd(): void
    {
        $this->subject->add(new TestingModel());
        $model = new TestingModel();
        $this->subject->add($model);

        $result = $this->subject->inRange(1, 2);

        self::assertCount(1, $result);
        self::assertSame(
            $model,
            $result->first()
        );
    }

    /**
     * @test
     */
    public function toArrayForNoElementsReturnsEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->toArray()
        );
    }

    /**
     * @test
     */
    public function toArrayWithOneElementReturnsArrayWithElement(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);

        self::assertSame(
            [$model],
            $this->subject->toArray()
        );
    }

    /**
     * @test
     */
    public function toArrayWithTwoElementsReturnsArrayWithBothElementsInAddingOrder(): void
    {
        $model1 = new TestingModel();
        $this->subject->add($model1);
        $model2 = new TestingModel();
        $this->subject->add($model2);

        self::assertSame(
            [$model1, $model2],
            $this->subject->toArray()
        );
    }

    /**
     * @test
     */
    public function parentModelByDefaultIsNull(): void
    {
        self::assertNull($this->subject->getParentModel());
    }

    /**
     * @test
     */
    public function setParentModelSetsParentModel(): void
    {
        $model = new TestingModel();
        $this->subject->setParentModel($model);

        self::assertSame(
            $model,
            $this->subject->getParentModel()
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function addWithoutParentModelIsNoProblem(): void
    {
        $model = new TestingModel();
        $this->subject->add($model);
    }

    /**
     * @test
     */
    public function addWithoutParentModelMarksParentModelAsDirty(): void
    {
        $parentModel = new TestingModel();
        self::assertFalse($parentModel->isDirty());
        $this->subject->setParentModel($parentModel);

        $model = new TestingModel();
        $this->subject->add($model);

        self::assertTrue($parentModel->isDirty());
    }

    /**
     * @test
     */
    public function isRelationOwnedByParentByDefaultIsFalse(): void
    {
        self::assertFalse($this->subject->isRelationOwnedByParent());
    }

    /**
     * @test
     */
    public function isRelationOwnedByParentCanBeSetToTrue(): void
    {
        $this->subject->markAsOwnedByParent();

        self::assertTrue($this->subject->isRelationOwnedByParent());
    }
}
