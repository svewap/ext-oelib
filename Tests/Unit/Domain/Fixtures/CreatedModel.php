<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Fixtures;

use OliverKlee\Oelib\Domain\Model\Interfaces\CreationDate;

/**
 * Testing repository for the CreationDate trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CreatedModel implements CreationDate
{
    use \OliverKlee\Oelib\Domain\Model\Traits\CreationDate;
}
