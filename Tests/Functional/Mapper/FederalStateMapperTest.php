<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\FederalStateMapper;
use OliverKlee\Oelib\Model\FederalState;

class FederalStateMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var FederalStateMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importStaticData();

        $this->subject = new FederalStateMapper();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     *
     * @throws NimutException
     */
    private function importStaticData(): void
    {
        if ($this->getDatabaseConnection()->selectCount('*', 'static_country_zones') === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/CountryZones.xml');
        }
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsFederalStateInstance(): void
    {
        self::assertInstanceOf(
            FederalState::class,
            $this->subject->find(88)
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        /** @var FederalState $model */
        $model = $this->subject->find(88);
        self::assertSame(
            'NW',
            $model->getIsoAlpha2ZoneCode()
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCodeWithDataOfExistingReturnsFederalStateInstance(): void
    {
        self::assertInstanceOf(
            FederalState::class,
            $this->subject->findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode('DE', 'NW')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCodeWithDataOfExistingRecordReturnsRecordAsModel(): void
    {
        self::assertSame(
            'NW',
            $this->subject->findByIsoAlpha2CountryCodeAndIsoAlpha2ZoneCode('DE', 'NW')->getIsoAlpha2ZoneCode()
        );
    }
}
