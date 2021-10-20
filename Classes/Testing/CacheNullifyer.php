<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use OliverKlee\Oelib\System\Typo3Version;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Cache\FluidTemplateCache;

/**
 * This class can disable all core caches (by setting them to a `NullBackend`) for tests.
 */
final class CacheNullifyer
{
    /**
     * Sets all Core caches to the `NullBackend`, except for: assets, core, di.
     */
    public function disableCoreCaches(): void
    {
        if (Typo3Version::isNotHigherThan(9)) {
            $this->disableCoreCachesForVersion9();
        } else {
            $this->disableCoreCachesForVersion10();
        }
    }

    private function disableCoreCachesForVersion9(): void
    {
        $this->getCacheManager()->setCacheConfigurations(
            [
                'cache_hash' => ['backend' => NullBackend::class],
                'cache_pages' => ['backend' => NullBackend::class],
                'cache_pagesection' => ['backend' => NullBackend::class],
                'cache_rootline' => ['backend' => NullBackend::class],
                'cache_runtime' => ['backend' => NullBackend::class],
                'l10n' => ['backend' => NullBackend::class],
                'pages' => ['backend' => NullBackend::class],
            ]
        );
    }

    private function disableCoreCachesForVersion10(): void
    {
        $this->getCacheManager()->setCacheConfigurations(
            [
                'assets' => ['backend' => NullBackend::class],
                'core' => ['backend' => NullBackend::class],
                'extbase' => ['backend' => NullBackend::class],
                'hash' => ['backend' => NullBackend::class],
                'l10n' => ['backend' => NullBackend::class],
                'pages' => ['backend' => NullBackend::class],
                'pagesection' => ['backend' => NullBackend::class],
                'rootline' => ['backend' => NullBackend::class],
                'runtime' => ['backend' => NullBackend::class],
            ]
        );
        $this->registerNullFluidCache();
    }

    private function getCacheManager(): CacheManager
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }

    private function registerNullFluidCache(): void
    {
        $this->registerNullCache('fluid_template', FluidTemplateCache::class);
    }

    /**
     * Registers a `NullCache` for the given key using a frontend of the given class.
     *
     * Use this method for caches that do not work without a frontend, e.g., the pages cache or the Fluid template
     * cache.
     *
     * @param non-empty-string $cacheKey
     * @param class-string<FrontendInterface> $frontEndClass
     */
    private function registerNullCache(string $cacheKey, string $frontEndClass): void
    {
        $cacheManager = $this->getCacheManager();
        if ($cacheManager->hasCache($cacheKey)) {
            return;
        }

        $backEnd = GeneralUtility::makeInstance(NullBackend::class, 'Testing');
        $frontEnd = GeneralUtility::makeInstance($frontEndClass, $cacheKey, $backEnd);
        $cacheManager->registerCache($frontEnd);
    }
}
