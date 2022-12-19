<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Persistence;

use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Testing query result that holds an object storage for its objects.
 *
 * This class is intended to be used in unit tests (usually for repositories).
 *
 * @template Model
 * @implements QueryResultInterface<Model>
 */
final class TestingQueryResult implements QueryResultInterface
{
    /**
     * @var ObjectStorage<Model>
     */
    private $objectStorage;

    /**
     * @param ObjectStorage<Model>|null $storage
     */
    public function __construct(?ObjectStorage $storage = null)
    {
        if (!$storage instanceof ObjectStorage) {
            /** @var ObjectStorage<Model> $storage */
            $storage = new ObjectStorage();
        }

        $this->objectStorage = $storage;
    }

    /**
     * @return Model|null
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->objectStorage->current();
    }

    public function next(): void
    {
        $this->objectStorage->next();
    }

    public function key(): string
    {
        return (string)$this->objectStorage->key();
    }

    public function valid(): bool
    {
        return $this->objectStorage->valid();
    }

    public function rewind(): void
    {
        $this->objectStorage->rewind();
    }

    /**
     * @param Model|int $offset
     */
    public function offsetExists($offset): bool
    {
        return $this->objectStorage->offsetExists($offset);
    }

    /**
     * @param Model|int $offset
     *
     * @return mixed
     */
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
        return $this->objectStorage->offsetGet($offset);
    }

    /**
     * @param Model $offset
     */
    public function offsetSet($offset, $value): void
    {
        $this->objectStorage->offsetSet($offset, $value);
    }

    /**
     * @param Model|int $offset
     */
    public function offsetUnset($offset): void
    {
        $this->objectStorage->offsetUnset($offset);
    }

    public function count(): int
    {
        return $this->objectStorage->count();
    }

    /**
     * @return never
     *
     * @throws \BadMethodCallException
     */
    public function getQuery(): QueryInterface
    {
        throw new \BadMethodCallException('Not implemented.', 1665661687);
    }

    /**
     * @return Model|null
     */
    public function getFirst()
    {
        // This works around a bug in lower Extbase versions.
        if (\count($this->objectStorage) === 0) {
            return null;
        }

        $this->objectStorage->rewind();

        return $this->objectStorage->current();
    }

    /**
     * @return array<int, Model>
     */
    public function toArray(): array
    {
        return $this->objectStorage->toArray();
    }
}
