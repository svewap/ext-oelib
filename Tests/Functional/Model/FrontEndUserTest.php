<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * @covers \OliverKlee\Oelib\Model\FrontEndUser
 */
final class FrontEndUserTest extends FunctionalTestCase
{
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
        $connection = $this->getConnectionPool()->getConnectionForTable('static_countries');
        if ($connection->count('*', 'static_countries', []) === 0) {
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

        $country = MapperRegistry::get(CountryMapper::class)->find(54);
        $this->subject->setData(['static_info_country' => $country->getIsoAlpha3Code()]);

        self::assertSame($country, $this->subject->getCountry());
    }

    /**
     * @test
     */
    public function setCountrySetsCountry(): void
    {
        $this->importStaticData();

        $country = MapperRegistry::get(CountryMapper::class)->find(54);

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

        $country = MapperRegistry::get(CountryMapper::class)->find(54);
        $this->subject->setCountry($country);

        self::assertTrue($this->subject->hasCountry());
    }
}
