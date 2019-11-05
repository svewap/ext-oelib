<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Logging\Traits;

use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Log\LogManagerInterface;

/**
 * This is the default implementation of the LoggingAware interface.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
trait LoggingAware
{
    /**
     * @var LoggerInterface
     */
    private $logger = null;

    public function injectLogManager(LogManagerInterface $logManager)
    {
        $this->logger = $logManager->getLogger(__CLASS__);
    }
}
