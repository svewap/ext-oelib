<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures;

use OliverKlee\Oelib\Domain\Repository\Interfaces\PersistAll;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Testing repository for the PersistAll trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class PersistAllRepository extends Repository implements PersistAll
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\PersistAll;
}
