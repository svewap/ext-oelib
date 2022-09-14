<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\Core\Cache\FluidTemplateCache;

/**
 * This class sets all core caches for tests.
 */
final class CacheNullifyer
{
    /**
     * Sets all Core caches to make testing easier, either to a null backend (for page, page section, rootline)
     * or a simple file backend.
     */
    public function setAllCoreCaches(): void
    {
        if ((new Typo3Version())->getMajorVersion() <= 9) {
            $this->disableCoreCachesForVersion9();
        } else {
            $this->disableCoreCachesForVersion10();
        }
    }

    /**
     * @deprecated will be removed in oelib 5.0; use `setAllCoreCaches` instead
     */
    public function disableCoreCaches(): void
    {
        $this->setAllCoreCaches();
    }

    private function disableCoreCachesForVersion9(): void
    {
        $this->getCacheManager()->setCacheConfigurations(
            [
                'cache_core' => ['backend' => SimpleFileBackend::class, 'frontend' => PhpFrontend::class],
                'cache_hash' => ['backend' => SimpleFileBackend::class],
                'cache_imagesizes' => ['backend' => NullBackend::class],
                'cache_pages' => ['backend' => NullBackend::class],
                'cache_pagesection' => ['backend' => NullBackend::class],
                'cache_rootline' => ['backend' => NullBackend::class],
                'cache_runtime' => ['backend' => TransientMemoryBackend::class],
                'extbase_datamapfactory_datamap' => ['backend' => SimpleFileBackend::class],
                'extbase_reflection' => ['backend' => SimpleFileBackend::class],
                'fluid_template' => ['backend' => SimpleFileBackend::class, 'frontend' => FluidTemplateCache::class],
                'l10n' => ['backend' => SimpleFileBackend::class],
                'pages' => ['backend' => SimpleFileBackend::class],
            ]
        );
    }

    private function disableCoreCachesForVersion10(): void
    {
        $this->getCacheManager()->setCacheConfigurations(
            [
                'assets' => ['backend' => SimpleFileBackend::class],
                'core' => ['backend' => SimpleFileBackend::class, 'frontend' => PhpFrontend::class],
                'extbase' => ['backend' => SimpleFileBackend::class],
                'fluid_template' => ['backend' => SimpleFileBackend::class, 'frontend' => FluidTemplateCache::class],
                'hash' => ['backend' => SimpleFileBackend::class],
                'imagesizes' => ['backend' => NullBackend::class],
                'l10n' => ['backend' => SimpleFileBackend::class],
                'pages' => ['backend' => NullBackend::class],
                'pagesection' => ['backend' => NullBackend::class],
                'rootline' => ['backend' => NullBackend::class],
                'runtime' => ['backend' => TransientMemoryBackend::class],
            ]
        );
    }

    private function getCacheManager(): CacheManager
    {
        return GeneralUtility::makeInstance(CacheManager::class);
    }

    /**
     * Flushes the class name cache for `GeneralUtility::makeInstance()`.
     */
    public function flushMakeInstanceCache(): void
    {
        ExtendedGeneralUtility::flushMakeInstanceCache();
    }
}
