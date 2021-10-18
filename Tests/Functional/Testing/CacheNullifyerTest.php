<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\System\Typo3Version;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Testing\CacheNullifyer
 */
final class CacheNullifyerTest extends FunctionalTestCase
{
    /**
     * @var array<int, string>
     */
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
     * @return array<non-empty-string, array<int, non-empty-string>>
     */
    public function coreCachesVersion9DataProvider(): array
    {
        return [
            'hash' => ['cache_hash'],
            'l10n' => ['l10n'],
            'pages' => ['pages'],
            'pagesection' => ['cache_pagesection'],
            'rootline' => ['cache_rootline'],
            'runtime' => ['cache_runtime'],
        ];
    }

    /**
     * @test
     * @dataProvider coreCachesVersion9DataProvider
     */
    public function disableCoreCachesDisablesAllCoreCachesForVersion9(string $identifier): void
    {
        if (Typo3Version::isAtLeast(10)) {
            self::markTestSkipped('This test is specific to TYPO3 9LTS.');
        }

        $this->subject->disableCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf(NullBackend::class, $cache->getBackend());
    }

    /**
     * @return array<non-empty-string, array<int, non-empty-string>>
     */
    public function coreCachesVersion10DataProvider(): array
    {
        return [
            'extbase' => ['extbase'],
            'fluid_template' => ['fluid_template'],
            'hash' => ['hash'],
            'l10n' => ['l10n'],
            'pages' => ['pages'],
            'pagesection' => ['pagesection'],
            'rootline' => ['rootline'],
            'runtime' => ['runtime'],
        ];
    }

    /**
     * @test
     * @dataProvider coreCachesVersion10DataProvider
     */
    public function disableCoreCachesDisablesAllCoreCachesForVersion10(string $identifier): void
    {
        if (Typo3Version::isNotHigherThan(9)) {
            self::markTestSkipped('This test is specific to TYPO3 10LTS.');
        }

        $this->subject->disableCoreCaches();

        $cache = GeneralUtility::makeInstance(CacheManager::class)->getCache($identifier);
        self::assertInstanceOf(NullBackend::class, $cache->getBackend());
    }
}
