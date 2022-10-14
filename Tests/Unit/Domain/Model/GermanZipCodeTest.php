<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Interfaces\Geo;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\GermanZipCode
 */
final class GermanZipCodeTest extends UnitTestCase
{
    /**
     * @var GermanZipCode
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new GermanZipCode();
    }

    /**
     * @test
     */
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    /**
     * @test
     */
    public function isGeo(): void
    {
        self::assertInstanceOf(Geo::class, $this->subject);
    }

    /**
     * @test
     */
    public function getZipCodeInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getZipCode());
    }

    /**
     * @test
     */
    public function setZipCodeSetsZipCode(): void
    {
        $value = '01234';
        $this->subject->setZipCode($value);

        self::assertSame($value, $this->subject->getZipCode());
    }

    /**
     * @test
     */
    public function getCityNameInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getCityName());
    }

    /**
     * @test
     */
    public function setCityNameSetsCityName(): void
    {
        $value = 'Köln';
        $this->subject->setCityName($value);

        self::assertSame($value, $this->subject->getCityName());
    }

    /**
     * @test
     */
    public function getGeoAddressReturnsZipCodeAndCityAndGermany(): void
    {
        $this->subject->setZipCode('53173');
        $this->subject->setCityName('Bonn');

        self::assertSame('53173 Bonn, DE', $this->subject->getGeoAddress());
    }

    /**
     * @test
     */
    public function hasGeoAddressWithEmptyDataReturnsTrue(): void
    {
        self::assertTrue($this->subject->hasGeoAddress());
    }

    /**
     * @test
     */
    public function hasGeoAddressWithZipCodeReturnsTrue(): void
    {
        $this->subject->setZipCode('53173');

        self::assertTrue($this->subject->hasGeoAddress());
    }

    /**
     * @test
     */
    public function hasGeoAddressWithCityNameReturnsTrue(): void
    {
        $this->subject->setCityName('Bonn');

        self::assertTrue($this->subject->hasGeoAddress());
    }

    /**
     * @test
     */
    public function getLongitudeInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getLongitude());
    }

    /**
     * @test
     */
    public function setLongitudeSetsLongitude(): void
    {
        $value = 1234.56;
        $this->subject->setLongitude($value);

        self::assertSame($value, $this->subject->getLongitude());
    }

    /**
     * @test
     */
    public function getLatitudeInitiallyReturnsZero(): void
    {
        self::assertSame(0.0, $this->subject->getLatitude());
    }

    /**
     * @test
     */
    public function setLatitudeSetsLatitude(): void
    {
        $value = 1234.56;
        $this->subject->setLatitude($value);

        self::assertSame($value, $this->subject->getLatitude());
    }

    /**
     * @test
     */
    public function getGeoCoordinatesReturnsCoordinates(): void
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
    public function setGeoCoordinatesAlwaysThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->setGeoCoordinates(['latitude' => 1.23, 'longitude' => 0.123]);
    }

    /**
     * @test
     */
    public function hasGeoCoordinatesAlwaysReturnsTrue(): void
    {
        self::assertTrue($this->subject->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function clearGeoCoordinatesAlwaysThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->clearGeoCoordinates();
    }

    /**
     * @test
     */
    public function hasGeoErrorAlwaysReturnsFalse(): void
    {
        self::assertFalse($this->subject->hasGeoError());
    }

    /**
     * @test
     */
    public function clearGeoErrorAlwaysThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->clearGeoError();
    }
}
