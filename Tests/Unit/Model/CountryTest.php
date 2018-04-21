<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_Model_CountryTest extends Tx_Phpunit_TestCase
{
    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped('This tests needs the static_info_tables extension.');
        }
    }

    protected function tearDown()
    {
        Tx_Oelib_MapperRegistry::purgeInstance();
    }

    //////////////////////////////////////////////////
    // Tests regarding getting the local short name.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getLocalShortNameReturnsLocalShortNameOfGermany()
    {
        /** @var Tx_Oelib_Model_Country $subject */
        $subject = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class)->find(54);

        self::assertSame(
            'Deutschland',
            $subject->getLocalShortName()
        );
    }

    /**
     * @test
     */
    public function getLocalShortNameReturnsLocalShortNameOfUnitedKingdomOfGreatBritain()
    {
        /** @var Tx_Oelib_Model_Country $subject */
        $subject = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class)->find(74);

        self::assertSame(
            'United Kingdom',
            $subject->getLocalShortName()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding getting the ISO alpha-2 code.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfGermany()
    {
        /** @var Tx_Oelib_Model_Country $subject */
        $subject = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class)->find(54);

        self::assertSame(
            'DE',
            $subject->getIsoAlpha2Code()
        );
    }

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2CodeOfUnitedKingdomOfGreatBritain()
    {
        /** @var Tx_Oelib_Model_Country $subject */
        $subject = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class)->find(74);

        self::assertSame(
            'GB',
            $subject->getIsoAlpha2Code()
        );
    }

    //////////////////////////////////////////////////
    // Tests regarding getting the ISO alpha-3 code.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getIsoAlpha3CodeReturnsIsoAlpha3CodeOfGermany()
    {
        /** @var Tx_Oelib_Model_Country $subject */
        $subject = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class)->find(54);

        self::assertSame(
            'DEU',
            $subject->getIsoAlpha3Code()
        );
    }

    /**
     * @test
     */
    public function getIsoAlpha3CodeReturnsIsoAlpha3CodeOfUnitedKingdomOfGreatBritain()
    {
        /** @var Tx_Oelib_Model_Country $subject */
        $subject = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_Country::class)->find(74);

        self::assertSame(
            'GBR',
            $subject->getIsoAlpha3Code()
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
        $model = new Tx_Oelib_Model_Country();

        self::assertTrue(
            $model->isReadOnly()
        );
    }
}
