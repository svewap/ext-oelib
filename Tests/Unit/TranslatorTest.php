<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_TranslatorTest extends Tx_Phpunit_TestCase
{
    /////////////////////////////////
    // Tests regarding translate().
    /////////////////////////////////

    /**
     * @test
     */
    public function translateForInexistentLabelReturnsLabelKey()
    {
        $subject = new Tx_Oelib_Translator('default', '', []);

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
        $subject = new Tx_Oelib_Translator('default', '', $localizedLabels);

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
        $subject = new Tx_Oelib_Translator('de', '', $localizedLabels);

        self::assertSame(
            'Deutsch',
            $subject->translate('label_test')
        );
    }

    /**
     * @test
     */
    public function translateForLabelInexistentInGermanWithEmptyAlternativeLanguageWithLanguageGermanReturnsEnglishLabel()
    {
        $localizedLabels = [
            'default' => ['label_test' => [0 => ['source' => 'English', 'target' => 'English']]],
        ];
        $subject = new Tx_Oelib_Translator('de', '', $localizedLabels);

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
        $subject = new Tx_Oelib_Translator('default', 'de', $localizedLabels);

        self::assertSame(
            'Deutsch',
            $subject->translate('label_test')
        );
    }
}
