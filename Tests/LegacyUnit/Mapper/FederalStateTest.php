<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Mapper_FederalStateTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_Mapper_FederalState
     */
    private $subject = null;

    protected function setUp()
    {
        if (!ExtensionManagementUtility::isLoaded('static_info_tables')) {
            self::markTestSkipped('This tests needs the static_info_tables extension.');
        }

        $this->subject = new \Tx_Oelib_Mapper_FederalState();
    }

    /*
     * Tests concerning find
     */

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsFederalStateInstance()
    {
        self::assertInstanceOf(
            \Tx_Oelib_Model_FederalState::class,
            $this->subject->find(88)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
    {
        /** @var \Tx_Oelib_Model_FederalState $model */
        $model = $this->subject->find(88);
        self::assertSame(
            'NW',
            $model->getIsoAlpha2ZoneCode()
        );
    }

    /**
     * Tests concerning findByIsoAlpha2Code
     *
     *
     * /**
     * @test
     */
    public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCodeWithIsoAlpha2CodeOfExistingRecordReturnsFederalStateInstance(
    ) {
        self::assertInstanceOf(
            \Tx_Oelib_Model_FederalState::class,
            $this->subject->findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode('DE', 'NW')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel(
    ) {
        self::assertSame(
            'NW',
            $this->subject->findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode('DE', 'NW')->getIsoAlpha2ZoneCode()
        );
    }
}
