<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use TYPO3\CMS\Core\Core\Environment;

/**
 * This class provides functions for overwriting the TYPO3 environment for tests.
 */
final class WritableEnvironment extends Environment
{
    /**
     * Sets the fake current PHP script.
     *
     * @param non-empty-string $currentScript the full path e.g., `'/var/www/html/public/index.php'`
     */
    public static function setCurrentScript(string $currentScript): void
    {
        self::$currentScript = $currentScript;
    }
}
