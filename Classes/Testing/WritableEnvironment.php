<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use TYPO3\CMS\Core\Core\Environment;

/**
 * This class provides functions for overwriting the TYPO3 environment for tests.
 *
 * @internal
 */
final class WritableEnvironment extends Environment
{
    /**
     * @var string|null
     */
    private static $currentScriptBackup;

    /**
     * Sets the fake current PHP script.
     *
     * @param non-empty-string $currentScript the full path e.g., `'/var/www/html/public/index.php'`
     */
    public static function setCurrentScript(string $currentScript): void
    {
        if (!\is_string(self::$currentScriptBackup)) {
            self::$currentScriptBackup = self::getCurrentScript();
        }

        self::$currentScript = $currentScript;
    }

    /**
     * Restores the current script in case it has been overwritten.
     */
    public static function restoreCurrentScript(): void
    {
        if (\is_string(self::$currentScriptBackup)) {
            self::$currentScript = self::$currentScriptBackup;
            self::$currentScriptBackup = null;
        }
    }
}
