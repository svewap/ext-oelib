<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Model\Country;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\CountryMapper
 * @covers \OliverKlee\Oelib\Model\Country
 */
final class CountryMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var CountryMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->importStaticData();

        $this->subject = new CountryMapper();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     */
    private function importStaticData(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable('static_countries');
        if ($connection->count('*', 'static_countries', []) === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Countries.xml');
        }
    }

    ///////////////////////////
    // Tests concerning find.
    ///////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel(): void
    {
        /** @var Country $model */
        $model = $this->subject->find(54);
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
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsCountryInstance(): void
    {
        self::assertInstanceOf(
            Country::class,
            $this->subject->findByIsoAlpha2Code('DE')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel(): void
    {
        self::assertSame(
            'DE',
            $this->subject->findByIsoAlpha2Code('DE')->getIsoAlpha2Code()
        );
    }

    /////////////////////////////////////////
    // Tests regarding findByIsoAlpha3Code.
    /////////////////////////////////////////

    /**
     * @test
     */
    public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsCountryInstance(): void
    {
        self::assertInstanceOf(
            Country::class,
            $this->subject->findByIsoAlpha3Code('DEU')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsRecordAsModel(): void
    {
        self::assertSame(
            'DE',
            $this->subject->findByIsoAlpha3Code('DEU')->getIsoAlpha2Code()
        );
    }
}
