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

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_Model_LanguageTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_Model_Language
     */
    private $subject;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped('This tests needs the static_info_tables extension.');
        }

        $this->subject = new Tx_Oelib_Model_Language();
    }

    ////////////////////////////////////////////
    // Tests regarding getting the local name.
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getLocalNameReturnsLocalNameOfGerman()
    {
        $this->subject->setData(['lg_name_local' => 'Deutsch']);

        self::assertSame(
            'Deutsch',
            $this->subject->getLocalName()
        );
    }

    /**
     * @test
     */
    public function getLocalNameReturnsLocalNameOfEnglish()
    {
        $this->subject->setData(['lg_name_local' => 'English']);

        self::assertSame(
            'English',
            $this->subject->getLocalName()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding getting the ISO alpha-2 code.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGerman()
    {
        $this->subject->setData(['lg_iso_2' => 'DE']);

        self::assertSame(
            'DE',
            $this->subject->getIsoAlpha2Code()
        );
    }

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfEnglish()
    {
        $this->subject->setData(['lg_iso_2' => 'EN']);

        self::assertSame(
            'EN',
            $this->subject->getIsoAlpha2Code()
        );
    }

    ////////////////////////////////
    // Tests concerning isReadOnly
    ////////////////////////////////

    /**
     * @test
     */
    public function isReadOnlyIsTrue()
    {
        self::assertTrue(
            $this->subject->isReadOnly()
        );
    }
}
