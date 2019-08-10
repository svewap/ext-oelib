<?php

namespace OliverKlee\Oelib\Tests\Functional\Model;

use Nimut\TestingFramework\Exception\Exception as NimutException;
use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\VersionNumberUtility;

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
     * Imports static records - but only if they aren't already available as static data.
     *
     * @return void
     *
     * @throws NimutException
     */
    private function importStaticData()
    {
        $tableName = 'static_countries';
        if (VersionNumberUtility::convertVersionNumberToInteger(TYPO3_version) >= 8004000) {
            $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
            $connection = $connectionPool->getConnectionForTable($tableName);
            $count = $connection->count('*', $tableName, []);
        } else {
            $count = \Tx_Oelib_Db::count($tableName);
        }
        if ($count === 0) {
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
        $this->importStaticData();

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
        $this->importStaticData();

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
        $this->importStaticData();

        $this->subject->setData(['static_info_country' => 'xyz']);

        self::assertFalse($this->subject->hasCountry());
    }

    /**
     * @test
     */
    public function hasCountryWithCountryReturnsTrue()
    {
        $this->importStaticData();

        /** @var \Tx_Oelib_Mapper_Country $mapper */
        $mapper = \Tx_Oelib_MapperRegistry::get(\Tx_Oelib_Mapper_Country::class);
        /** @var \Tx_Oelib_Model_Country $country */
        $country = $mapper->find(54);
        $this->subject->setCountry($country);

        self::assertTrue($this->subject->hasCountry());
    }
}
