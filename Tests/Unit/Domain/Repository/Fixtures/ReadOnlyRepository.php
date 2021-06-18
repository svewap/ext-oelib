<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures;

use OliverKlee\Oelib\Domain\Repository\Traits\ReadOnly;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Testing repository for the ReadOnly trait.
 */
class ReadOnlyRepository extends Repository
{
    use ReadOnly;
}
