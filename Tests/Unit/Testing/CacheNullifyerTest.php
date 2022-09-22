<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Testing;

use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\Oelib\Testing\ExtendedGeneralUtility;
use OliverKlee\Oelib\Tests\Unit\Testing\Fixtures\TestingSubclass;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Testing\CacheNullifyer
 * @covers \OliverKlee\Oelib\Testing\ExtendedGeneralUtility
 */
final class CacheNullifyerTest extends UnitTestCase
{
    /**
     * @var CacheNullifyer
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CacheNullifyer();
    }

    protected function tearDown(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\stdClass::class]);
        ExtendedGeneralUtility::flushMakeInstanceCache();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function flushMakeInstanceCacheFlushesMakeInstanceCache(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\stdClass::class] = ['className' => TestingSubclass::class];
        $subclassInstanceBeforeFlush = GeneralUtility::makeInstance(\stdClass::class);
        self::assertInstanceOf(TestingSubclass::class, $subclassInstanceBeforeFlush);

        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][\stdClass::class]);
        $this->subject->flushMakeInstanceCache();

        $noSubclassInstanceAfterFlush = GeneralUtility::makeInstance(\stdClass::class);
        self::assertInstanceOf(\stdClass::class, $noSubclassInstanceAfterFlush);
        self::assertNotInstanceOf(TestingSubclass::class, $noSubclassInstanceAfterFlush);
    }
}
