<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\Traits\ReadOnlyRepository;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnostic;
use TYPO3\CMS\Extbase\Persistence\Repository;

class GermanZipCodeRepository extends Repository
{
    use ReadOnlyRepository;
    use StoragePageAgnostic;

    /**
     * @var array<string, GermanZipCode|null>
     */
    protected $cachedResults = [];

    /**
     * @param string $zipCode
     *
     * @return GermanZipCode|null
     */
    public function findOneByZipCode(string $zipCode)
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
