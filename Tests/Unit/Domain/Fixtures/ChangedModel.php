<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Fixtures;

use OliverKlee\Oelib\Domain\Model\Interfaces\ChangeDate;

/**
 * Testing repository for the ChangeDate trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ChangedModel implements ChangeDate
{
    use \OliverKlee\Oelib\Domain\Model\Traits\ChangeDate;
}
