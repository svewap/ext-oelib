<?php

namespace OliverKlee\Oelib\Domain\Repository;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\Traits\ReadOnly;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for GermanZipCode models.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
class GermanZipCodeRepository extends Repository
{
    use ReadOnly;
    use StoragePageAgnostic;

    /**
     * @var GermanZipCode[]|null[]
     */
    protected $cachedResults = [];

    /**
     * @param string $zipCode
     *
     * @return GermanZipCode|null
     */
    public function findOneByZipCode($zipCode)
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
}
