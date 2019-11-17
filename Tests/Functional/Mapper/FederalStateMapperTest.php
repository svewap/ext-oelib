<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FederalStateMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var \Tx_Oelib_Mapper_FederalState
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->importStaticData();

        $this->subject = new \Tx_Oelib_Mapper_FederalState();
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
        if ($this->getDatabaseConnection()->selectCount('*', 'static_country_zones') === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/CountryZones.xml');
        }
    }

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
