<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Logging\Interfaces;

use TYPO3\CMS\Core\Log\LogManagerInterface;

/**
 * Interface for classes that can log things.
 *
 * The default implementation is the corresponding trait.
 *
 * @deprecated Will be removed in oelib 6.0. Use `\Psr\Log\LoggerAwareInterface` instead.
 */
interface LoggingAware
{
    public function injectLogManager(LogManagerInterface $logManager): void;
}
