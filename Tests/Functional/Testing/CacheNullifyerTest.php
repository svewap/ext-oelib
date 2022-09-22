<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use OliverKlee\Oelib\Testing\CacheNullifyer;
use TYPO3\CMS\Core\Cache\Backend\AbstractBackend;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Cache\Backend\TransientMemoryBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Testing\CacheNullifyer
 * @covers \OliverKlee\Oelib\Testing\ExtendedGeneralUtility
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
    public function setAllCoreCachesSetsAllCoreCachesForVersion10(string $identifier, string $backend): void
    {
        $this->subject->setAllCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf($backend, $cache->getBackend());
    }
}
