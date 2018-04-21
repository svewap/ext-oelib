<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_Mapper_CurrencyTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_Mapper_Currency
     */
    private $subject;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped('This tests needs the static_info_tables extension.');
        }

        $this->subject = new Tx_Oelib_Mapper_Currency();
    }

    ///////////////////////////
    // Tests concerning find.
    ///////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsCurrencyInstance()
    {
        self::assertInstanceOf(
            Tx_Oelib_Model_Currency::class,
            $this->subject->find(49)
        );
    }

    /////////////////////////////////////////
    // Tests regarding findByIsoAlpha3Code.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsCurrencyInstance()
    {
        self::assertInstanceOf(
            Tx_Oelib_Model_Currency::class,
            $this->subject->findByIsoAlpha3Code('EUR')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsRecordAsModel()
    {
        self::assertSame(
            49,
            $this->subject->findByIsoAlpha3Code('EUR')->getUid()
        );
    }
}
