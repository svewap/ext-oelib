<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Geocoding;

use OliverKlee\Oelib\Geocoding\DummyGeocodingLookup;
use OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures\TestingGeo;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Geocoding\DummyGeocodingLookup
 */
final class DummyGeocodingLookupTest extends UnitTestCase
{
    /**
     * @var DummyGeocodingLookup
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new DummyGeocodingLookup();
    }

    /////////////////////
    // Tests for lookUp
    /////////////////////

    /**
     * @test
     */
    public function lookUpForEmptyAddressSetsCoordinatesError(): void
    {
        $geo = $this->createPartialMock(TestingGeo::class, ['setGeoError']);
        $geo->expects(self::once())->method('setGeoError');

        $this->subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressSetsCoordinatesFromSetCoordinates(): void
    {
        $coordinates = ['latitude' => 50.7335500, 'longitude' => 7.1014300];
        $this->subject->setCoordinates(
            $coordinates['latitude'],
            $coordinates['longitude']
        );

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertSame($coordinates, $geo->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsNoCoordinates(): void
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertFalse($geo->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithoutSetCoordinatesNotClearsExistingCoordinates(): void
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertFalse($geo->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsGeoError(): void
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertTrue($geo->hasGeoError());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithCoordinatesNotOverwritesCoordinates(): void
    {
        $this->subject->setCoordinates(42.0, 42.0);

        $coordinates = ['latitude' => 50.7335500, 'longitude' => 7.1014300];
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoCoordinates($coordinates);

        $this->subject->lookUp($geo);

        self::assertSame($coordinates, $geo->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function lookUpAfterClearCoordinatesSetsNoCoordinates(): void
    {
        $this->subject->setCoordinates(42.0, 42.0);
        $this->subject->clearCoordinates();

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertFalse($geo->hasGeoCoordinates());
    }
}
