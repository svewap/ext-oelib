<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Fixtures;

use OliverKlee\Oelib\Domain\Model\Interfaces\CreationDate;

/**
 * Testing repository for the CreationDate trait.
 */
class CreatedModel implements CreationDate
{
    use \OliverKlee\Oelib\Domain\Model\Traits\CreationDate;
}
