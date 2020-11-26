<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Model\Country;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CountryMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/static_info_tables'];

    /**
     * @var CountryMapper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->importStaticData();

        $this->subject = new CountryMapper();
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
        if ($this->getDatabaseConnection()->selectCount('*', 'static_countries') === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Countries.xml');
        }
    }

    ///////////////////////////
    // Tests concerning find.
    ///////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsCountryInstance()
    {
        self::assertInstanceOf(Country::class, $this->subject->find(54));
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsRecordAsModel()
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
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsCountryInstance()
    {
        self::assertInstanceOf(
            Country::class,
            $this->subject->findByIsoAlpha2Code('DE')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha2CodeWithIsoAlpha2CodeOfExistingRecordReturnsRecordAsModel()
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
    public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsCountryInstance()
    {
        self::assertInstanceOf(
            Country::class,
            $this->subject->findByIsoAlpha3Code('DEU')
        );
    }

    /**
     * @test
     */
    public function findByIsoAlpha3CodeWithIsoAlpha3CodeOfExistingRecordReturnsRecordAsModel()
    {
        self::assertSame(
            'DE',
            $this->subject->findByIsoAlpha3Code('DEU')->getIsoAlpha2Code()
        );
    }
}
