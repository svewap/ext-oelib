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
class Tx_Oelib_Tests_Unit_Mapper_LanguageTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_Mapper_Language
     */
    private $subject;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped('This tests needs the static_info_tables extension.');
        }

        $this->subject = new Tx_Oelib_Mapper_Language();
    }

    ///////////////////////////
    // Tests concerning find.
    ///////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsLanguageInstance()
    {
        self::assertInstanceOf(
            Tx_Oelib_Model_Language::class,
            $this->subject->find(43)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        /** @var Tx_Oelib_Model_Language $model */
        $model = $this->subject->find(43);
        self::assertSame(
            'DE',
            $model->getIsoAlpha2Code()
        );
    }

    /////////////////////////////////////////
    // Tests regarding findByIsoAlpha2Code.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsLanguageInstance()
    {
        self::assertInstanceOf(
            Tx_Oelib_Model_Language::class,
            $this->subject->findByIsoAlpha2Code('DE')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel()
    {
        self::assertSame(
            'DE',
            $this->subject->findByIsoAlpha2Code('DE')->getIsoAlpha2Code()
        );
    }
}
