<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Testing;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Testing\ExtendedGeneralUtility;
use OliverKlee\Oelib\Tests\Unit\Testing\Fixtures\TestingSubclass;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Testing\ExtendedGeneralUtility
 */
final class ExtendedGeneralUtilityTest extends UnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        ExtendedGeneralUtility::flushMakeInstanceCache();
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
        ExtendedGeneralUtility::flushMakeInstanceCache();

        $noSubclassInstanceAfterFlush = GeneralUtility::makeInstance(\stdClass::class);
        self::assertInstanceOf(\stdClass::class, $noSubclassInstanceAfterFlush);
        self::assertNotInstanceOf(TestingSubclass::class, $noSubclassInstanceAfterFlush);
    }
}
