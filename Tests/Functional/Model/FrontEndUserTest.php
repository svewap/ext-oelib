<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\Country;
use OliverKlee\Oelib\Model\FrontEndUser;

class FrontEndUserTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/static_info_tables', 'typo3conf/ext/oelib'];

    /**
     * @var FrontEndUser
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new FrontEndUser();
    }

    /**
     * Imports static records - but only if they aren't already available as static data.
     *
     * @throws NimutException
     */
    private function importStaticData(): void
    {
        if ($this->getDatabaseConnection()->selectCount('*', 'static_countries') === 0) {
            $this->importDataSet(__DIR__ . '/../Fixtures/Countries.xml');
        }
    }

    /**
     * @test
     */
    public function getCountryWithoutCountryReturnsNull(): void
    {
        $this->subject->setData([]);

        self::assertNull($this->subject->getCountry());
    }

    /**
     * @test
     */
    public function getCountryWithInvalidCountryCodeReturnsNull(): void
    {
        $this->subject->setData(['static_info_country' => 'xyz']);

        self::assertNull($this->subject->getCountry());
    }

    /**
     * @test
     */
    public function getCountryWithCountryReturnsCountryAsModel(): void
    {
        $this->importStaticData();

        /** @var CountryMapper $mapper */
        $mapper = MapperRegistry::get(CountryMapper::class);
        /** @var Country $country */
        $country = $mapper->find(54);
        $this->subject->setData(['static_info_country' => $country->getIsoAlpha3Code()]);

        self::assertSame($country, $this->subject->getCountry());
    }

    /**
     * @test
     */
    public function setCountrySetsCountry(): void
    {
        $this->importStaticData();

        /** @var CountryMapper $mapper */
        $mapper = MapperRegistry::get(CountryMapper::class);
        /** @var Country $country */
        $country = $mapper->find(54);

        $this->subject->setCountry($country);

        self::assertSame($country, $this->subject->getCountry());
    }

    /**
     * @test
     */
    public function countryCanBeSetToNull(): void
    {
        $this->subject->setCountry(null);

        self::assertNull($this->subject->getCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithoutCountryReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse($this->subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithInvalidCountryReturnsFalse(): void
    {
        $this->importStaticData();

        $this->subject->setData(['static_info_country' => 'xyz']);

        self::assertFalse($this->subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithCountryReturnsTrue(): void
    {
        $this->importStaticData();

        /** @var CountryMapper $mapper */
        $mapper = MapperRegistry::get(CountryMapper::class);
        /** @var Country $country */
        $country = $mapper->find(54);
        $this->subject->setCountry($country);

        self::assertTrue($this->subject->hasCountry());
    }
}
