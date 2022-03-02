<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * @extends Repository<GermanZipCode>
 */
class GermanZipCodeRepository extends Repository
{
    use StoragePageAgnostic;

    /**
     * @var array<string, GermanZipCode|null>
     */
    protected $cachedResults = [];

    public function findOneByZipCode(string $zipCode): ?GermanZipCode
    {
        if (!\preg_match('/^\\d{5}$/', $zipCode)) {
            return null;
        }
        if (\array_key_exists($zipCode, $this->cachedResults)) {
            return $this->cachedResults[$zipCode];
        }

        $query = $this->createQuery();
        $result = $query->matching($query->equals('zipCode', $zipCode))->setLimit(1)->execute();

        /** @var GermanZipCode|null $firstMatch */
        $firstMatch = $result->getFirst();
        $this->cachedResults[$zipCode] = $firstMatch;

        return $firstMatch;
    }

    /**
     * Adds an object to this repository.
     *
     * @param object $object
     *
     * @throws \BadMethodCallException
     */
    public function add($object): void
    {
        $this->preventWriteOperation();
    }

    /**
     * Removes an object from this repository.
     *
     * @param object $object
     *
     * @throws \BadMethodCallException
     */
    public function remove($object): void
    {
        $this->preventWriteOperation();
    }

    /**
     * Replaces an existing object with the same identifier by the given object.
     *
     * @param object $modifiedObject
     *
     * @throws \BadMethodCallException
     */
    public function update($modifiedObject): void
    {
        $this->preventWriteOperation();
    }

    /**
     * Removes all objects of this repository as if `remove()` was called for all of them.
     *
     * @throws \BadMethodCallException
     */
    public function removeAll(): void
    {
        $this->preventWriteOperation();
    }

    /**
     * @throws \BadMethodCallException
     */
    private function preventWriteOperation(): void
    {
        throw new \BadMethodCallException(
            'This is a read-only repository in which the removeAll method must not be called.',
            1537544385
        );
    }
}
