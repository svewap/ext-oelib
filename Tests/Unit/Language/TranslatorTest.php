<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Language;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Language\Translator;

class TranslatorTest extends UnitTestCase
{
    /**
     * @test
     */
    public function translateForInexistentLabelReturnsLabelKey()
    {
        $subject = new Translator('default', '', []);

        self::assertSame(
            'label_test',
            $subject->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function translateWithLanguageEnglishReturnsEnglishLabel()
    {
        $localizedLabels = [
            'default' => ['label_test' => [0 => ['source' => 'English', 'target' => 'English']]],
            'de' => ['label_test' => [0 => ['source' => 'English', 'target' => 'Deutsch']]],
        ];
        $subject = new Translator('default', '', $localizedLabels);

        self::assertSame(
            'English',
            $subject->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function translateWithLanguageGermanReturnsGermanLabel()
    {
        $localizedLabels = [
            'default' => ['label_test' => [0 => ['source' => 'English', 'target' => 'English']]],
            'de' => ['label_test' => [0 => ['source' => 'English', 'target' => 'Deutsch']]],
        ];
        $subject = new Translator('de', '', $localizedLabels);

        self::assertSame(
            'Deutsch',
            $subject->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function translateForLabelInexistentInGermanWithEmptyAlternativeLanguageWithGermanReturnsEnglishLabel()
    {
        $localizedLabels = [
            'default' => ['label_test' => [0 => ['source' => 'English', 'target' => 'English']]],
        ];
        $subject = new Translator('de', '', $localizedLabels);

        self::assertSame(
            'English',
            $subject->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function translateForLabelInexistentInEnglishAndAlternativeLanguageGermanReturnsGermanLabel()
    {
        $localizedLabels = [
            'de' => ['label_test' => [0 => ['source' => 'English', 'target' => 'Deutsch']]],
        ];
        $subject = new Translator('default', 'de', $localizedLabels);

        self::assertSame(
            'Deutsch',
            $subject->translate('label_test')
        );
    }
}
