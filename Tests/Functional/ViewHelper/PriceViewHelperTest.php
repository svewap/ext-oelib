<?php

namespace OliverKlee\Oelib\Tests\Unit\Visibility;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class PriceViewHelperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var \Tx_Oelib_ViewHelper_Price
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->importStaticData();

        $this->subject = new \Tx_Oelib_ViewHelper_Price();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     *
     * @return void
     *
     * @throws NimutException
     */
    private function importStaticData()
    {
        $tableName = 'static_currencies';
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        $connection = $connectionPool->getConnectionForTable($tableName);
        $count = $connection->count('*', $tableName, []);
        if ($count === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Currencies.xml');
        }
    }

    /**
     * @test
     */
    public function renderWithoutSettingValueOrCurrencyFirstRendersZeroWithTwoDigits()
    {
        self::assertSame(
            '0.00',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderWithValueWithoutSettingCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits()
    {
        $this->subject->setValue(12345.678);

        self::assertSame(
            '12345.68',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderAfterSettingAnInvalidCurrencyUsesDecimalPointAndTwoRoundedDecimalDigits()
    {
        $this->subject->setValue(12345.678);
        $this->subject->setCurrencyFromIsoAlpha3Code('FOO');

        self::assertSame(
            '12345.68',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithLeftSymbolRendersCurrencySymbolLeftOfPrice()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('EUR');

        self::assertSame(
            '€ 123,45',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithRightSymbolRendersCurrencySymbolRightOfPrice()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('CZK');

        self::assertSame(
            '123,45 Kč',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithoutDecimalDigitsReturnsPriceWithoutDecimalDigits()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('CLP');

        self::assertSame(
            '$ 123',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithOneDecimalDigitReturnsPriceWithOneDecimalDigit()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('MGA');

        self::assertSame(
            '123,5',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithTwoDecimalDigitsReturnsPriceWithTwoDecimalDigits()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('EUR');

        self::assertSame(
            '€ 123,45',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithThreeDecimalDigitsReturnsPriceWithThreeDecimalDigits()
    {
        $this->subject->setValue(123.456);
        $this->subject->setCurrencyFromIsoAlpha3Code('KWD');

        self::assertSame(
            'KD 123,456',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithCommaAsDecimalSeparatorReturnsPriceWithCommaAsDecimalSeparator()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('EUR');

        self::assertSame(
            '€ 123,45',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithPointAsDecimalSeparatorReturnsPriceWithPointAsDecimalSeparator()
    {
        $this->subject->setValue(123.45);
        $this->subject->setCurrencyFromIsoAlpha3Code('USD');

        self::assertSame(
            '$ 123.45',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithPointAsThousandsSeparatorReturnsPriceWithPointAsThousandsSeparator()
    {
        $this->subject->setValue(1234.56);
        $this->subject->setCurrencyFromIsoAlpha3Code('EUR');

        self::assertSame(
            '€ 1.234,56',
            $this->subject->render()
        );
    }

    /**
     * @test
     */
    public function renderForCurrencyWithCommaAsThousandsSeparatorReturnsPriceWithCommaAsThousandsSeparator()
    {
        $this->subject->setValue(1234.56);
        $this->subject->setCurrencyFromIsoAlpha3Code('USD');

        self::assertSame(
            '$ 1,234.56',
            $this->subject->render()
        );
    }
}
