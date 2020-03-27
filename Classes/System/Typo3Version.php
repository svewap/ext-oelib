<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\System;

/**
 * Utility class for checking the current TYPO3 version.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Typo3Version
{
    /**
     * @var \TYPO3\CMS\Core\Information\Typo3Version|null
     */
    private static $version = null;

    /**
     * @return \TYPO3\CMS\Core\Information\Typo3Version
     *
     * @throws \BadMethodCallException
     */
    private static function getVersionUtility(): \TYPO3\CMS\Core\Information\Typo3Version
    {
        if (!\class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)) {
            throw new \BadMethodCallException(
                'The class ' . \TYPO3\CMS\Core\Information\Typo3Version::class . ' does not exist.',
                1585310637
            );
        }

        if (!self::$version instanceof \TYPO3\CMS\Core\Information\Typo3Version) {
            self::$version = new \TYPO3\CMS\Core\Information\Typo3Version();
        }

        return self::$version;
    }

    private static function getMajorVersion(): int
    {
        if (\class_exists(\TYPO3\CMS\Core\Information\Typo3Version::class)) {
            $majorVersion = self::getVersionUtility()->getMajorVersion();
        } else {
            $explodedVersion = \explode('.', TYPO3_version);
            $majorVersion = (int)$explodedVersion[0];
        }

        return $majorVersion;
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
