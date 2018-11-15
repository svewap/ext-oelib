<?php

namespace OliverKlee\Oelib\Domain\Repository;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\Traits\ReadOnlyTrait;
use OliverKlee\Oelib\Domain\Repository\Traits\StoragePageAgnosticTrait;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Repository for GermanZipCode models.
 *
 * @method GermanZipCode|null findOneByZipCode(string $zipCode)
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
class GermanZipCodeRepository extends Repository
{
    use ReadOnlyTrait;
    use StoragePageAgnosticTrait;
}
