<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Language;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Language\Fixtures\TestingSalutationSwitcher;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class SalutationSwitcherTest extends UnitTestCase
{
    /**
     * @var TestingSalutationSwitcher
     */
    private $subject = null;

    protected function setUp()
    {
        /** @var CacheManager $cacheManager */
        $cacheManager = GeneralUtility::makeInstance(CacheManager::class);
        $cacheManager->setCacheConfigurations(['l10n' => ['backend' => NullBackend::class]]);

        $GLOBALS['TSFE'] = $this->prophesize(TypoScriptFrontendController::class)->reveal();

        $this->subject = new TestingSalutationSwitcher([]);
    }

    protected function tearDown()
    {
        GeneralUtility::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canBeSerialized()
    {
        self::assertNotSame('', serialize($this->subject));
    }

    // Tests for setting the language.

    /**
     * @test
     */
    public function initialLanguage()
    {
        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setLanguageDefault()
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
    public function setLanguageDe()
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
    public function setLanguageDefaultEmpty()
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
    public function setSalutationFormal()
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
    public function setSalutationInformal()
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
    public function emptyKeyDefault()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setLanguage('default');
        $this->subject->translate('');
    }

    /**
     * @test
     */
    public function emptyKeyDe()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setLanguage('de');
        $this->subject->translate('');
    }

    /**
     * @test
     */
    public function noLanguageAtAllWithKnownKey()
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
    public function noLanguageAtAllWithUnknownKey()
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
    public function translateForMissingLabelStillUsesDefaultAsLanguageKey()
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
    public function translateWithoutLanguageOnlyInDefault()
    {
        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    /**
     * @test
     */
    public function translateWithoutLanguageInBoth()
    {
        self::assertSame(
            'in both languages',
            $this->subject->translate('in_both')
        );
    }

    /**
     * @test
     */
    public function missingKeyDefault()
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
    public function missingKeyDe()
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
    public function onlyInDefaultUsingDefault()
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
    public function onlyInDefaultUsingNothing()
    {
        self::assertSame(
            'only in default',
            $this->subject->translate('only_in_default')
        );
    }

    /**
     * @test
     */
    public function onlyInDefaultUsingDe()
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
    public function inBothUsingDefault()
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
    public function inBothUsingDe()
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
    public function emptyStringDefault()
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
    public function emptyStringDe()
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
    public function fallbackForInexistentLanguageIsLanguageKey()
    {
        $key = 'default_not_fallback';
        $inexistentLanguage = 'xy';
        $this->subject->setLanguage($inexistentLanguage);

        self::assertSame($key, $this->subject->translate($key));
    }

    /**
     * @test
     */
    public function fallbackToDefaultFromDe()
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
    public function formalOnly()
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
    public function informalOnly()
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
    public function formalWithNormal()
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
    public function formalWithNormalTryingInformal()
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
    public function formalWithNormalTryingNothing()
    {
        self::assertSame(
            'formal with normal, normal',
            $this->subject->translate('formal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function formalWithNormalTryingInvalid()
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
    public function informalWithNormal()
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
    public function informalWithNormalTryingFormal()
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
    public function informalWithNormalTryingNothing()
    {
        self::assertSame(
            'informal with normal, normal',
            $this->subject->translate('informal_string_with_normal')
        );
    }

    /**
     * @test
     */
    public function informalWithNormalTryingInvalid()
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
    public function bothWithoutNormalTryingFormal()
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
    public function bothWithoutNormalTryingInformal()
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
    public function bothWithoutNormalTryingNothing()
    {
        self::assertSame(
            'both without normal',
            $this->subject->translate('both_without_normal')
        );
    }

    /**
     * @test
     */
    public function bothWithoutNormalTryingInvalid()
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
    public function formalOnlyNoGermanLabel()
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
    public function informalOnlyNoGermanLabels()
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
    public function formalWithNormalNoGermanLabels()
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
    public function formalWithNormalTryingInformalNoGermanLabels()
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
    public function formalWithNormalTryingNothingNoGermanLabels()
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
    public function informalWithNormalNoGermanLabels()
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
    public function informalWithNormalTryingFormalNoGermanLabels()
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
    public function informalWithNormalTryingNothingNoGermanLabels()
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
    public function bothWithoutNormalTryingFormalNoGermanLabels()
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
    public function bothWithoutNormalTryingInformalNoGermanLabels()
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
    public function bothWithoutNormalTryingNothingNoGermanLabels()
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
    public function formalOnlyWithGermanLabels()
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
    public function informalOnlyWithGermanLabels()
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
    public function formalWithNormalWithGermanLabels()
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
    public function formalWithNormalTryingInformalWithGermanLabels()
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
    public function formalWithNormalTryingNothingWithGermanLabels()
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
    public function formalWithNormalTryingInvalidWithGermanLabels()
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
    public function informalWithNormalWithGermanLabels()
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
    public function informalWithNormalTryingFormalWithGermanLabels()
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
    public function informalWithNormalTryingNothingWithGermanLabels()
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
    public function informalWithNormalTryingInvalidWithGermanLabels()
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
    public function bothWithoutNormalTryingFormalWithGermanLabels()
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
    public function bothWithoutNormalTryingInformalWithGermanLabels()
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
    public function bothWithoutNormalTryingNothingWithGermanLabels()
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
    public function bothWithoutNormalTryingInvalidWithGermanLabels()
    {
        $this->subject->setLanguage('de');
        $this->subject->setSalutationMode('foobar');
        self::assertSame(
            'de both without normal, formal',
            $this->subject->translate('de_both_without_normal')
        );
    }
}
