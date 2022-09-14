<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class to provide access to protected static members of `GeneralUtility`.
 *
 * @internal use `CacheNullifyer` instead
 */
final class ExtendedGeneralUtility extends GeneralUtility
{
    /**
     * Flushes the class name cache for `GeneralUtility::makeInstance()`.
     *
     * @internal use `CacheNullifyer::flushMakeInstanceCache()` instead
     */
    public static function flushMakeInstanceCache(): void
    {
        self::$finalClassNameCache = [];
    }
}
