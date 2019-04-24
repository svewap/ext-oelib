<?php

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndUserTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/static_info_tables', 'typo3conf/ext/oelib'];

    /**
     * @var \Tx_Oelib_Model_FrontEndUser
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->subject = new \Tx_Oelib_Model_FrontEndUser();
    }

    /**
     * Imports static countries - but only if they aren't already available as static data.
     *
     * @return void
     *
     * @throws \Nimut\TestingFramework\Exception\Exception
     */
    private function importCountries()
    {
        if (!\Tx_Oelib_Db::existsRecord('static_countries')) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Countries.xml');
        }
    }

    /**
     * @test
     */
    public function getCountryWithoutCountryReturnsNull()
    {
        $this->subject->setData([]);

        self::assertNull($this->subject->getCountry());
    }

    /**
     * @test
     */
    public function getCountryWithInvalidCountryCodeReturnsNull()
    {
        $this->subject->setData(['static_info_country' => 'xyz']);

        self::assertNull($this->subject->getCountry());
    }

    /**
     * @test
     */
    public function getCountryWithCountryReturnsCountryAsModel()
    {
        $this->importCountries();

        /** @var \Tx_Oelib_Mapper_Country $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Country::class);
        /** @var \Tx_Oelib_Model_Country $country */
        $country = $mapper->find(54);
        $this->subject->setData(['static_info_country' => $country->getIsoAlpha3Code()]);

        self::assertSame($country, $this->subject->getCountry());
    }

    /**
     * @test
     */
    public function setCountrySetsCountry()
    {
        $this->importCountries();

        /** @var \Tx_Oelib_Mapper_Country $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Country::class);
        /** @var \Tx_Oelib_Model_Country $country */
        $country = $mapper->find(54);

        $this->subject->setCountry($country);

        self::assertSame($country, $this->subject->getCountry());
    }

    /**
     * @test
     */
    public function countryCanBeSetToNull()
    {
        $this->subject->setCountry(null);

        self::assertNull($this->subject->getCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithoutCountryReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse($this->subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithInvalidCountryReturnsFalse()
    {
        $this->importCountries();

        $this->subject->setData(['static_info_country' => 'xyz']);

        self::assertFalse($this->subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithCountryReturnsTrue()
    {
        $this->importCountries();

        /** @var \Tx_Oelib_Mapper_Country $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Country::class);
        /** @var \Tx_Oelib_Model_Country $country */
        $country = $mapper->find(54);
        $this->subject->setCountry($country);

        self::assertTrue($this->subject->hasCountry());
    }
}
