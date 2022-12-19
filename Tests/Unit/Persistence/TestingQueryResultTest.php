<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Persistence;

use OliverKlee\Oelib\Persistence\TestingQueryResult;
use OliverKlee\Oelib\Tests\Unit\Persistence\Fixtures\TestingModel;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Persistence\TestingQueryResult
 */
final class TestingQueryResultTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsQueryResultInterface(): void
    {
        $subject = new TestingQueryResult();

        self::assertInstanceOf(QueryResultInterface::class, $subject);
    }

    /**
     * @test
     */
    public function currentReturnsCurrentFromStorage(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);

        $subject = new TestingQueryResult($storage);

        self::assertSame($model, $subject->current());
    }

    /**
     * @test
     */
    public function nextAdvancesStorage(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model1 = new TestingModel();
        $storage->attach($model1);
        $model2 = new TestingModel();
        $storage->attach($model2);
        $subject = new TestingQueryResult($storage);

        $subject->next();

        self::assertSame($model2, $subject->current());
    }

    /**
     * @test
     */
    public function keyForEmptyStorageReturnsEmptyString(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();

        $subject = new TestingQueryResult($storage);

        self::assertSame('', $subject->key());
    }

    /**
     * @test
     */
    public function keyReturnsKeyFromStorage(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);

        $subject = new TestingQueryResult($storage);

        self::assertSame($storage->key(), $subject->key());
    }

    /**
     * @test
     */
    public function validForEmptyStorageReturnsFalse(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();

        $subject = new TestingQueryResult($storage);

        self::assertFalse($subject->valid());
    }

    /**
     * @test
     */
    public function validForForPointerAtStartOfNonEmptyStorageReturnsTrue(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);

        $subject = new TestingQueryResult($storage);

        self::assertTrue($subject->valid());
    }

    /**
     * @test
     */
    public function validForForPointerAfterEndOfNonEmptyStorageReturnsFalse(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);

        $subject = new TestingQueryResult($storage);
        $subject->next();

        self::assertFalse($subject->valid());
    }

    /**
     * @test
     */
    public function rewindRewindsStorage(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model1 = new TestingModel();
        $storage->attach($model1);
        $model2 = new TestingModel();
        $storage->attach($model2);
        $subject = new TestingQueryResult($storage);
        $subject->next();

        $subject->rewind();

        self::assertSame($model1, $subject->current());
    }

    /**
     * @test
     */
    public function offsetExistsForNonExistentOffsetReturnsFalse(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $subject = new TestingQueryResult($storage);

        $subject->rewind();

        self::assertFalse($subject->offsetExists(new TestingModel()));
    }

    /**
     * @test
     */
    public function offsetExistsForExistingOffsetReturnsTrue(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);
        $subject = new TestingQueryResult($storage);

        self::assertTrue($subject->offsetExists($model));
    }

    /**
     * @test
     */
    public function offsetSetAddsObject(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $subject = new TestingQueryResult($storage);
        $model = new TestingModel();

        $subject->offsetSet($model, $model);

        self::assertTrue($subject->offsetExists($model));
    }

    /**
     * @test
     */
    public function offsetSetAddsObjectToStorage(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $subject = new TestingQueryResult($storage);
        $model = new TestingModel();

        $subject->offsetSet($model, $model);

        self::assertTrue($storage->contains($model));
    }

    /**
     * @test
     */
    public function offsetUnsetRemovesObject(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);
        $subject = new TestingQueryResult($storage);

        $subject->offsetUnset($model);

        self::assertFalse($subject->offsetExists($model));
    }

    /**
     * @test
     */
    public function offsetSetRemovesObjectFromStorage(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);
        $subject = new TestingQueryResult($storage);

        $subject->offsetUnset($model);

        self::assertFalse($storage->contains($model));
    }

    /**
     * @test
     */
    public function countForEmptyStorageReturnsZero(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $subject = new TestingQueryResult($storage);

        self::assertCount(0, $subject);
    }

    /**
     * @test
     */
    public function countNonEmptyStorageReturnsNumberOfContainedModels(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model1 = new TestingModel();
        $storage->attach($model1);
        $model2 = new TestingModel();
        $storage->attach($model2);
        $subject = new TestingQueryResult($storage);

        self::assertCount(2, $subject);
    }

    /**
     * @test
     */
    public function getQueryThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Not implemented.');
        $this->expectExceptionCode(1665661687);

        $subject = new TestingQueryResult();

        $subject->getQuery();
    }

    /**
     * @test
     */
    public function getFirstForEmptyStorageReturnsNull(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();

        $subject = new TestingQueryResult($storage);

        self::assertNull($subject->getFirst());
    }

    /**
     * @test
     */
    public function getFirstForNonRewoundStorageReturnsFirstElement(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model1 = new TestingModel();
        $storage->attach($model1);
        $model2 = new TestingModel();
        $storage->attach($model2);
        $subject = new TestingQueryResult($storage);

        $first = $subject->getFirst();

        self::assertSame($model1, $first);
    }

    /**
     * @test
     */
    public function toArrayConvertsStorageToArray(): void
    {
        /** @var ObjectStorage<TestingModel> $storage */
        $storage = new ObjectStorage();
        $model = new TestingModel();
        $storage->attach($model);

        $subject = new TestingQueryResult($storage);

        self::assertSame([$model], $subject->toArray());
    }
}
