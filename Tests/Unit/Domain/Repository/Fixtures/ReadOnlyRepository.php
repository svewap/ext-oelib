<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures;

use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\EmptyModel;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Testing repository for the `ReadOnlyRepository` trait.

 *
 * @extends Repository<EmptyModel>
 */
class ReadOnlyRepository extends Repository
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\ReadOnlyRepository;
}
