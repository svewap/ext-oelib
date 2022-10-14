<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Logging\Traits;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManagerInterface;

/**
 * This is the default implementation of the `LoggingAware` interface.
 *
 * @deprecated Will be removed in oelib 6.0. Use `\Psr\Log\LoggerAwareTrait` instead.
 */
trait LoggingAware
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function injectLogManager(LogManagerInterface $logManager): void
    {
        $this->logger = $logManager->getLogger(self::class);
    }
}
