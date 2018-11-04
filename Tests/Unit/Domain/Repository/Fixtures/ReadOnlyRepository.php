<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures;

use OliverKlee\Oelib\Domain\Repository\Traits\ReadOnlyTrait;

/**
 * Testing repository for the ReadOnly trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ReadOnlyRepository
{
    use ReadOnlyTrait;
}
