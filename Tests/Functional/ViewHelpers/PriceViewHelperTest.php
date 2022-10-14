<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\ViewHelpers;

use OliverKlee\Oelib\ViewHelpers\PriceViewHelper;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\PriceViewHelper
 */
class PriceViewHelperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var PriceViewHelper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->importStaticData();

        $this->subject = new PriceViewHelper();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     */
    private function importStaticData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('static_currencies');
        if ($connection->count('*', 'static_currencies', []) === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Currencies.xml');
        }
    }

    /**
     * @test
     */
    public function renderAfterSettingAnInvalidCurrencyUsesDecimalPointAndTwoDecimalDigits(): void
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
    public function renderForCurrencyWithLeftSymbolRendersCurrencySymbolLeftOfPrice(): void
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
    public function renderForCurrencyWithRightSymbolRendersCurrencySymbolRightOfPrice(): void
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
    public function renderForCurrencyWithoutDecimalDigitsReturnsPriceWithoutDecimalDigits(): void
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
    public function renderForCurrencyWithOneDecimalDigitReturnsPriceWithOneDecimalDigit(): void
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
    public function renderForCurrencyWithTwoDecimalDigitsReturnsPriceWithTwoDecimalDigits(): void
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
    public function renderForCurrencyWithCommaAsDecimalSeparatorReturnsPriceWithCommaAsDecimalSeparator(): void
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
    public function renderForCurrencyWithPointAsDecimalSeparatorReturnsPriceWithPointAsDecimalSeparator(): void
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
    public function renderForCurrencyWithPointAsThousandsSeparatorReturnsPriceWithPointAsThousandsSeparator(): void
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
    public function renderForCurrencyWithCommaAsThousandsSeparatorReturnsPriceWithCommaAsThousandsSeparator(): void
    {
        $this->subject->setValue(1234.56);
        $this->subject->setCurrencyFromIsoAlpha3Code('USD');

        self::assertSame(
            '$ 1,234.56',
            $this->subject->render()
        );
    }
}
