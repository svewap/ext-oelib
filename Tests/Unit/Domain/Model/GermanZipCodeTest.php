<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GermanZipCodeTest extends UnitTestCase
{
    /**
     * @var GermanZipCode
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new GermanZipCode();
    }

    /**
     * @test
     */
    public function isAbstractEntity()
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    /**
     * @test
     */
    public function hasNonNamespacedAlias()
    {
        self::assertInstanceOf(\Tx_Oelib_Domain_Model_GermanZipCode::class, $this->subject);
    }

    /**
     * @test
     */
    public function isGeo()
    {
        self::assertInstanceOf(\Tx_Oelib_Interface_Geo::class, $this->subject);
    }

    /**
     * @test
     */
    public function getZipCodeInitiallyReturnsEmptyString()
    {
        self::assertSame('', $this->subject->getZipCode());
    }

    /**
     * @test
     */
    public function setZipCodeSetsZipCode()
    {
        $value = '01234';
        $this->subject->setZipCode($value);

        self::assertSame($value, $this->subject->getZipCode());
    }

    /**
     * @test
     */
    public function getCityNameInitiallyReturnsEmptyString()
    {
        self::assertSame('', $this->subject->getCityName());
    }

    /**
     * @test
     */
    public function setCityNameSetsCityName()
    {
        $value = 'Köln';
        $this->subject->setCityName($value);

        self::assertSame($value, $this->subject->getCityName());
    }

    /**
     * @test
     */
    public function getGeoAddressReturnsZipCodeAndCityAndGermany()
    {
        $this->subject->setZipCode('53173');
        $this->subject->setCityName('Bonn');

        self::assertSame('53173 Bonn, DE', $this->subject->getGeoAddress());
    }

    /**
     * @test
     */
    public function hasGeoAddressWithEmptyDataReturnsTrue()
    {
        self::assertTrue($this->subject->hasGeoAddress());
    }

    /**
     * @test
     */
    public function hasGeoAddressWithZipCodeReturnsTrue()
    {
        $this->subject->setZipCode('53173');

        self::assertTrue($this->subject->hasGeoAddress());
    }

    /**
     * @test
     */
    public function hasGeoAddressWithCityNameReturnsTrue()
    {
        $this->subject->setCityName('Bonn');

        self::assertTrue($this->subject->hasGeoAddress());
    }

    /**
     * @test
     */
    public function getLongitudeInitiallyReturnsZero()
    {
        self::assertSame(0.0, $this->subject->getLongitude());
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude()
    {
        $value = 1234.56;
        $this->subject->setLongitude($value);

        self::assertSame($value, $this->subject->getLongitude());
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero()
    {
        self::assertSame(0.0, $this->subject->getLatitude());
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude()
    {
        $value = 1234.56;
        $this->subject->setLatitude($value);

        self::assertSame($value, $this->subject->getLatitude());
    }

    /**
     * @test
     */
    public function getGeoCoordinatesReturnsCoordinates()
    {
        $latitude = 12.234;
        $this->subject->setLatitude($latitude);
        $longitude = 1.235;
        $this->subject->setLongitude($longitude);

        self::assertSame(['latitude' => $latitude, 'longitude' => $longitude], $this->subject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function setGeoCoordinatesAlwaysThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->setGeoCoordinates(['latitude' => 1.23, 'longitude' => 0.123]);
    }

    /**
     * @test
     */
    public function hasGeoCoordinatesAlwaysReturnsTrue()
    {
        self::assertTrue($this->subject->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function clearGeoCoordinatesAlwaysThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->clearGeoCoordinates();
    }

    /**
     * @test
     */
    public function hasGeoErrorAlwaysReturnsFalse()
    {
        self::assertFalse($this->subject->hasGeoError());
    }

    /**
     * @test
     */
    public function clearGeoErrorAlwaysThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->clearGeoError();
    }
}
