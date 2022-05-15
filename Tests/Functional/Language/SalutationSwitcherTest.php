<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Language;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Oelib\Tests\Functional\Language\Fixtures\TestingSalutationSwitcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * @covers \OliverKlee\Oelib\Language\SalutationSwitcher
 */
final class SalutationSwitcherTest extends FunctionalTestCase
{
    /**
     * @var non-empty-string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingFramework
     */
    private $testingFramework;

    /**
     * @var TestingSalutationSwitcher
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_oelib');
        $this->testingFramework->createFakeFrontEnd();

        $this->subject = new TestingSalutationSwitcher([]);
    }

    protected function tearDown(): void
    {
        GeneralUtility::purgeInstances();
        $this->testingFramework->cleanUpWithoutDatabase();
    }

    /**
     * @test
     */
    public function canBeSerialized(): void
    {
        self::assertNotSame('', serialize($this->subject));
    }

    // Tests for setting the language.

    /**
     * @test
     */
    public function initialLanguage(): void
    {
        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageDefault(): void
    {
        $this->subject->setLanguage('default');
        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageDe(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageDefaultEmpty(): void
    {
        $this->subject->setLanguage('');
        self::assertSame(
            '',
            $this->subject->getLanguage()
        );
    }

    // Tests for setting the salutation modes.

    /**
     * @test
     */
    public function setSalutationFormal(): void
    {
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'formal',
            $this->subject->getSalutationMode()
        );
    }

    /**
     * @test
     */
    public function setSalutationInformal(): void
    {
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'informal',
            $this->subject->getSalutationMode()
        );
    }

    //////////////////////////////////////
    // Tests for empty keys or languages.
    //////////////////////////////////////

    /**
     * @test
     */
    public function translateForEmptyKeyInDefaultLanguageThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');

        $this->subject->setLanguage('default');
        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->translate('');
    }

    /**
     * @test
     */
    public function translateForEmptyKeyInNonDefaultLanguageThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');

        $this->subject->setLanguage('de');
        // @phpstan-ignore-next-line We are explicitly checking for a contract violation here.
        $this->subject->translate('');
    }

    /**
     * @test
     */
    public function noLanguageAtAllWithKnownKey(): void
    {
        $this->subject->setLanguage('');

        self::assertSame(
            'in_both',
            $this->subject->translate('in_both')
        );
    }

    /**
     * @test
     */
    public function noLanguageAtAllWithUnknownKey(): void
    {
        $this->subject->setLanguage('');

        self::assertSame(
            'missing_key',
            $this->subject->translate('missing_key')
        );
    }

    /**
     * @test
     */
    public function translateForMissingLabelStillUsesDefaultAsLanguageKey(): void
    {
        $this->subject->setLanguage('de');

        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    ///////////////////////////////////////////////////////////
    // Tests for translating without setting salutation modes.
    ///////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function translateWithoutLanguageOnlyInDefault(): void
    {
        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    /**
     * @test
     */
    public function translateWithoutLanguageInBoth(): void
    {
        self::assertSame(
            'in both languages',
            $this->subject->translate('in_both')
        );
    }

    /**
     * @test
     */
    public function missingKeyDefault(): void
    {
        $this->subject->setLanguage('default');
        self::assertSame(
            'missing_key',
            $this->subject->translate('missing_key')
        );
    }

    /**
     * @test
     */
    public function missingKeyDe(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'missing_key',
            $this->subject->translate('missing_key')
        );
    }

    /**
     * @test
     */
    public function onlyInDefaultUsingDefault(): void
    {
        $this->subject->setLanguage('default');
        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    /**
     * @test
     */
    public function onlyInDefaultUsingNothing(): void
    {
        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    /**
     * @test
     */
    public function onlyInDefaultUsingDe(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    /**
     * @test
     */
    public function inBothUsingDefault(): void
    {
        $this->subject->setLanguage('default');
        self::assertSame(
            'in both languages',
            $this->subject->translate('in_both')
        );
    }

    /**
     * @test
     */
    public function inBothUsingDe(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'in beiden Sprachen',
            $this->subject->translate('in_both')
        );
    }

    /**
     * @test
     */
    public function emptyStringDefault(): void
    {
        $this->subject->setLanguage('default');
        self::assertSame(
            '',
            $this->subject->translate('empty_string_in_default')
        );
    }

    /**
     * @test
     */
    public function emptyStringDe(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            '',
            $this->subject->translate('empty_string_in_default')
        );
    }

    /**
     * @test
     */
    public function fallbackForInexistentLanguageIsLanguageKey(): void
    {
        $key = 'default_not_fallback';
        $inexistentLanguage = 'xy';
        $this->subject->setLanguage($inexistentLanguage);

        self::assertSame($key, $this->subject->translate($key));
    }

    /**
     * @test
     */
    public function fallbackToDefaultFromDe(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'only in french',
            $this->subject->translate('only_in_french')
        );
    }

    // Tests for translating with salutation modes in the default language.

    /**
     * @test
     */
    public function formalOnly(): void
    {
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'only formal',
            $this->subject->translate('formal_string_only')
        );
    }

    /**
     * @test
     */
    public function informalOnly(): void
    {
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'only informal',
            $this->subject->translate('informal_string_only')
        );
    }

    /**
     * @test
     */
    public function formalWithNormal(): void
    {
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'formal with normal, formal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingInformal(): void
    {
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'formal with normal, formal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingNothing(): void
    {
        self::assertSame(
            'formal with normal, normal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingInvalid(): void
    {
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'formal with normal, formal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormal(): void
    {
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'informal with normal, informal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingFormal(): void
    {
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'informal with normal, normal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingNothing(): void
    {
        self::assertSame(
            'informal with normal, normal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingInvalid(): void
    {
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'informal with normal, normal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingFormal(): void
    {
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'both without normal, formal',
            $this->subject->translate('both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingInformal(): void
    {
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'both without normal, informal',
            $this->subject->translate('both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingNothing(): void
    {
        self::assertSame(
            'both without normal',
            $this->subject->translate('both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingInvalid(): void
    {
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'both without normal, formal',
            $this->subject->translate('both_without_normal')
        );
    }

    //////////////////////////////////////////////////////////////////////
    // Tests for translating with salutation modes in the German, always
    // falling back to the default language as the corresponding German
    // labels are missing.
    //////////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function formalOnlyNoGermanLabel(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'only formal',
            $this->subject->translate('formal_string_only')
        );
    }

    /**
     * @test
     */
    public function informalOnlyNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'only informal',
            $this->subject->translate('informal_string_only')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'formal with normal, formal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingInformalNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'formal with normal, formal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingNothingNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'formal with normal, normal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'informal with normal, informal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingFormalNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'informal with normal, normal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingNothingNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'informal with normal, normal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingFormalNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'both without normal, formal',
            $this->subject->translate('both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingInformalNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'both without normal, informal',
            $this->subject->translate('both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingNothingNoGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'both without normal',
            $this->subject->translate('both_without_normal')
        );
    }

    //////////////////////////////////////////////////////////////////
    // Tests for translating with salutation modes in the German for
    // existing labels.
    //////////////////////////////////////////////////////////////////

    /**
     * @test
     */
    public function formalOnlyWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'de only formal',
            $this->subject->translate('de_formal_string_only')
        );
    }

    /**
     * @test
     */
    public function informalOnlyWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'de only informal',
            $this->subject->translate('de_informal_string_only')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'de formal with normal, formal',
            $this->subject->translate('de_formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingInformalWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'de formal with normal, formal',
            $this->subject->translate('de_formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingNothingWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'de formal with normal, normal',
            $this->subject->translate('de_formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingInvalidWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'de formal with normal, formal',
            $this->subject->translate('de_formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'de informal with normal, informal',
            $this->subject->translate('de_informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingFormalWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'de informal with normal, normal',
            $this->subject->translate('de_informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingNothingWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'de informal with normal, normal',
            $this->subject->translate('de_informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingInvalidWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'de informal with normal, normal',
            $this->subject->translate('de_informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingFormalWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('formal');
        self::assertSame(
            'de both without normal, formal',
            $this->subject->translate('de_both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingInformalWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('informal');
        self::assertSame(
            'de both without normal, informal',
            $this->subject->translate('de_both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingNothingWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        self::assertSame(
            'de_both_without_normal',
            $this->subject->translate('de_both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingInvalidWithGermanLabels(): void
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'de both without normal, formal',
            $this->subject->translate('de_both_without_normal')
        );
    }
}
