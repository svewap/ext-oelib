<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Testing repository for the DirectPersist trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DirectPersistRepository extends Repository implements DirectPersist
{
    use \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist;
}
