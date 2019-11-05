<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Logging\Interfaces;

use TYPO3\CMS\Core\Log\LogManagerInterface;

/**
 * Interface for classes that can log things.
 *
 * The default implementation is the corresponding trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface LoggingAware
{
    public function injectLogManager(LogManagerInterface $logManager);
}
