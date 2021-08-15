<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository\Traits;

/**
 * This trait marks repositories as read-only.
 *
 * @deprecated will be removed in oelib 4.0 (as the trait name breaks compatibility with PHP 8.1)
 */
trait ReadOnly
{
    use ReadOnlyRepository;
}
