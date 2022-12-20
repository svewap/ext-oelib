<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Testing;

use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
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
        $this->setCoreCachesForVersion10();
    }

    private function setCoreCachesForVersion10(): void
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
     *
     * @deprecated will be removed in oelib 6.0
     */
    public function flushMakeInstanceCache(): void
    {
        ExtendedGeneralUtility::flushMakeInstanceCache();
    }
}
