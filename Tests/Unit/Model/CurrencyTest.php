<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Model\Currency;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CurrencyTest extends UnitTestCase
{
    /**
     * @var Currency
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new Currency();
    }

    /**
     * @test
     */
    public function isReadOnlyIsTrue()
    {
        self::assertTrue($this->subject->isReadOnly());
    }

    /**
     * @test
     */
    public function getIsoAlpha3CodeReturnsIsoAlpha3Code()
    {
        $code = 'EUR';
        $this->subject->setData(['cu_iso_3' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha3Code());
    }

    /**
     * @test
     */
    public function hasLeftSymbolForCurrencyWithLeftSymbolReturnsTrue()
    {
        $this->subject->setData(['cu_symbol_left' => '€']);

        self::assertTrue($this->subject->hasLeftSymbol());
    }

    /**
     * @test
     */
    public function hasLeftSymbolForCurrencyWithoutLeftSymbolReturnsFalse()
    {
        $this->subject->setData(['cu_symbol_left' => '']);

        self::assertFalse($this->subject->hasLeftSymbol());
    }

    /**
     * @test
     */
    public function getLeftSymbolByDefaultReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertSame('', $this->subject->getLeftSymbol());
    }

    /**
     * @test
     */
    public function getLeftSymbolByDefaultReturnsLeftSymbol()
    {
        $symbol = '€';
        $this->subject->setData(['cu_symbol_left' => $symbol]);

        self::assertSame($symbol, $this->subject->getLeftSymbol());
    }

    /**
     * @test
     */
    public function hasRightSymbolForCurrencyWithRightSymbolReturnsTrue()
    {
        $this->subject->setData(['cu_symbol_right' => '€']);

        self::assertTrue($this->subject->hasRightSymbol());
    }

    /**
     * @test
     */
    public function hasRightSymbolForCurrencyWithoutRightSymbolReturnsFalse()
    {
        $this->subject->setData(['cu_symbol_right' => '']);

        self::assertFalse($this->subject->hasRightSymbol());
    }

    /**
     * @test
     */
    public function getRightSymbolByDefaultReturnsEmptyString()
    {
        $this->subject->setData([]);

        self::assertSame('', $this->subject->getRightSymbol());
    }

    /**
     * @test
     */
    public function getRightSymbolByDefaultReturnsRightSymbol()
    {
        $symbol = '€';
        $this->subject->setData(['cu_symbol_right' => $symbol]);

        self::assertSame($symbol, $this->subject->getRightSymbol());
    }

    /**
     * @test
     */
    public function getThousandsSeparatorReturnsThousandsSeparator()
    {
        $separator = '.';
        $this->subject->setData(['cu_thousands_point' => $separator]);

        self::assertSame($separator, $this->subject->getThousandsSeparator());
    }

    /**
     * @test
     */
    public function getDecimalSeparatorReturnsDecimalSeparator()
    {
        $separator = ',';
        $this->subject->setData(['cu_decimal_point' => $separator]);

        self::assertSame($separator, $this->subject->getDecimalSeparator());
    }

    /**
     * @test
     */
    public function getDecimalDigitsReturnsDecimalDigits()
    {
        $digits = 2;
        $this->subject->setData(['cu_decimal_digits' => (string)$digits]);

        self::assertSame($digits, $this->subject->getDecimalDigits());
    }
}
