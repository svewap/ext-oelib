<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Testing\CacheNullifyer
 */
final class CacheNullifyerTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var CacheNullifyer
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CacheNullifyer();
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: class-string<AbstractBackend>}>
     */
    public function coreCachesVersion9DataProvider(): array
    {
        return [
            'extbase_datamapfactory_datamap' => ['extbase_datamapfactory_datamap', SimpleFileBackend::class],
            'extbase_reflection' => ['extbase_reflection', SimpleFileBackend::class],
            'fluid_template' => ['fluid_template', SimpleFileBackend::class],
            'hash' => ['cache_hash', SimpleFileBackend::class],
            'imagesizes' => ['cache_imagesizes', NullBackend::class],
            'l10n' => ['l10n', SimpleFileBackend::class],
            'pages' => ['cache_pages', NullBackend::class],
            'pagesection' => ['cache_pagesection', NullBackend::class],
            'rootline' => ['cache_rootline', NullBackend::class],
            'runtime' => ['cache_runtime', TransientMemoryBackend::class],
        ];
    }

    /**
     * @test
     *
     * @param class-string<AbstractBackend> $backend
     * @dataProvider coreCachesVersion9DataProvider
     */
    public function disableCoreCachesSetsAllCoreCachesForVersion9(string $identifier, string $backend): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 10) {
            self::markTestSkipped('This test is specific to TYPO3 9LTS.');
        }

        $this->subject->disableCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf($backend, $cache->getBackend());
    }

    /**
     * @test
     *
     * @param class-string<AbstractBackend> $backend
     * @dataProvider coreCachesVersion9DataProvider
     */
    public function setAllCoreCachesSetsAllCoreCachesForVersion9(string $identifier, string $backend): void
    {
        if ((new Typo3Version())->getMajorVersion() >= 10) {
            self::markTestSkipped('This test is specific to TYPO3 9LTS.');
        }

        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf($backend, $cache->getBackend());
    }

    /**
     * @return array<non-empty-string, array{0: non-empty-string, 1: class-string<AbstractBackend>}>
     */
    public function coreCachesVersion10DataProvider(): array
    {
        return [
            'assets' => ['assets', SimpleFileBackend::class],
            'core' => ['extbase', SimpleFileBackend::class],
            'extbase' => ['extbase', SimpleFileBackend::class],
            'fluid_template' => ['fluid_template', SimpleFileBackend::class],
            'hash' => ['hash', SimpleFileBackend::class],
            'imagesizes' => ['imagesizes', NullBackend::class],
            'l10n' => ['l10n', SimpleFileBackend::class],
            'pages' => ['pages', NullBackend::class],
            'pagesection' => ['pagesection', NullBackend::class],
            'rootline' => ['rootline', NullBackend::class],
            'runtime' => ['runtime', TransientMemoryBackend::class],
        ];
    }

    /**
     * @test
     *
     * @param class-string<AbstractBackend> $backend
     * @dataProvider coreCachesVersion10DataProvider
     */
    public function disableCoreCachesSetsAllCoreCachesForVersion10(string $identifier, string $backend): void
    {
        if ((new Typo3Version())->getMajorVersion() <= 9) {
            self::markTestSkipped('This test is specific to TYPO3 10LTS.');
        }

        $this->subject->disableCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf($backend, $cache->getBackend());
    }

    /**
     * @test
     *
     * @param class-string<AbstractBackend> $backend
     * @dataProvider coreCachesVersion10DataProvider
     */
    public function setAllCoreCachesSetsAllCoreCachesForVersion10(string $identifier, string $backend): void
    {
        if ((new Typo3Version())->getMajorVersion() <= 9) {
            self::markTestSkipped('This test is specific to TYPO3 10LTS.');
        }

        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf($backend, $cache->getBackend());
    }
}
