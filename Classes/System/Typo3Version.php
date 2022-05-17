<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\System;

/**
 * Utility class for checking the current TYPO3 version.
 *
 * @deprecated Will be removed in oelib 6.0. Use `\TYPO3\CMS\Core\Information\Typo3Version` instead.
 */
final class Typo3Version
{
    /**
     * @var \TYPO3\CMS\Core\Information\Typo3Version|null
     */
    private static $version = null;

    /**
     * @return \TYPO3\CMS\Core\Information\Typo3Version
     */
    private static function getVersionUtility(): \TYPO3\CMS\Core\Information\Typo3Version
    {
        if (!self::$version instanceof \TYPO3\CMS\Core\Information\Typo3Version) {
            self::$version = new \TYPO3\CMS\Core\Information\Typo3Version();
        }

        return self::$version;
    }

    private static function getMajorVersion(): int
    {
        return self::getVersionUtility()->getMajorVersion();
    }

    /**
     * Checks whether the currently running TYPO3 version is at least the given version.
     *
     * @param int $version the version to check against, e.g., 9 for TYPO3 9.7
     *
     * @return bool
     */
    public static function isAtLeast(int $version): bool
    {
        return self::getMajorVersion() >= $version;
    }

    /**
     * Checks whether the currently running TYPO3 version is not higher than the given version.
     *
     * @param int $version the version to check against, e.g., 9 for TYPO3 9.7
     *
     * @return bool
     */
    public static function isNotHigherThan(int $version): bool
    {
        return self::getMajorVersion() <= $version;
    }
}
