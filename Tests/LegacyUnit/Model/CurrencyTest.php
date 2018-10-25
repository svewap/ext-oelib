<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Model_CurrencyTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_Model_Currency
     */
    private $subject;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped('This tests needs the static_info_tables extension.');
        }

        $this->subject = new \Tx_Oelib_Model_Currency();
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

    //////////////////////////////////////////////////
    // Tests regarding getting the ISO alpha-3 code.
    //////////////////////////////////////////////////

    /**
     * @test
     */
    public function getIsoAlpha3CodeCanReturnIsoAlpha3CodeOfEuro()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertSame(
            'EUR',
            $subject->getIsoAlpha3Code()
        );
    }

    /**
     * @test
     */
    public function getIsoAlpha3CodeCanReturnIsoAlpha3CodeOfUsDollars()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(155);

        self::assertSame(
            'USD',
            $subject->getIsoAlpha3Code()
        );
    }

    /////////////////////////////////////
    // Tests regarding the left symbol.
    /////////////////////////////////////

    /**
     * @test
     */
    public function hasLeftSymbolForCurrencyWithLeftSymbolReturnsTrue()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertTrue(
            $subject->hasLeftSymbol()
        );
    }

    /**
     * @test
     */
    public function hasLeftSymbolForCurrencyWithoutLeftSymbolReturnsFalse()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(40);

        self::assertFalse(
            $subject->hasLeftSymbol()
        );
    }

    /**
     * @test
     */
    public function getLeftSymbolForCurrencyWithLeftSymbolReturnsLeftSymbol()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertSame(
            '€',
            $subject->getLeftSymbol()
        );
    }

    /**
     * @test
     */
    public function getLeftSymbolForCurrencyWithoutLeftSymbolReturnsEmptyString()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(40);

        self::assertSame(
            '',
            $subject->getLeftSymbol()
        );
    }

    //////////////////////////////////////
    // Tests regarding the right symbol.
    //////////////////////////////////////

    /**
     * @test
     */
    public function hasRightSymbolForCurrencyWithRightSymbolReturnsTrue()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(40);

        self::assertTrue(
            $subject->hasRightSymbol()
        );
    }

    /**
     * @test
     */
    public function hasRightSymbolForCurrencyWithoutRightSymbolReturnsFalse()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertFalse(
            $subject->hasRightSymbol()
        );
    }

    /**
     * @test
     */
    public function getRightSymbolForCurrencyWithRightSymbolReturnsRightSymbol()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(40);

        self::assertSame(
            'Kč',
            $subject->getRightSymbol()
        );
    }

    /**
     * @test
     */
    public function getRightSymbolForCurrencyWithoutRightSymbolReturnsEmptyString()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertSame(
            '',
            $subject->getRightSymbol()
        );
    }

    /////////////////////////////////////////////
    // Tests regarding the thousands separator.
    /////////////////////////////////////////////

    /**
     * @test
     */
    public function getThousandsSeparatorForEuroReturnsPoint()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertSame(
            '.',
            $subject->getThousandsSeparator()
        );
    }

    /**
     * @test
     */
    public function getThousandsSeparatorForUsDollarReturnsComma()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(155);

        self::assertSame(
            ',',
            $subject->getThousandsSeparator()
        );
    }

    ///////////////////////////////////////////
    // Tests regarding the decimal separator.
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function getDecimalSeparatorForEuroReturnsComma()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertSame(
            ',',
            $subject->getDecimalSeparator()
        );
    }

    /**
     * @test
     */
    public function getDecimalSeparatorForUsDollarReturnsPoint()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(155);

        self::assertSame(
            '.',
            $subject->getDecimalSeparator()
        );
    }

    /*
     * Tests regarding the decimal digits.
     */

    /**
     * @test
     */
    public function getDecimalDigitsForChileanPesoReturnsZero()
    {
        /** @var \Tx_Oelib_Mapper_Currency $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class);
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = $mapper->find(33);

        self::assertSame(
            0,
            $subject->getDecimalDigits()
        );
    }

    /**
     * @test
     */
    public function getDecimalDigitsForMalagasyAriaryReturnsOne()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(173);

        self::assertSame(
            1,
            $subject->getDecimalDigits()
        );
    }

    /**
     * @test
     */
    public function getDecimalDigitsForEuroReturnsTwo()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(49);

        self::assertSame(
            2,
            $subject->getDecimalDigits()
        );
    }

    /**
     * @test
     */
    public function getDecimalDigitsForKuwaitiDinarReturnsThree()
    {
        /** @var \Tx_Oelib_Model_Currency $subject */
        $subject = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Currency::class)->find(81);

        self::assertSame(
            3,
            $subject->getDecimalDigits()
        );
    }
}
