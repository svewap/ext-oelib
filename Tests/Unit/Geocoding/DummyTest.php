<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Geocoding;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures\TestingGeo;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DummyTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Geocoding_Dummy
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Geocoding_Dummy();
    }

    /////////////////////
    // Tests for lookUp
    /////////////////////

    /**
     * @test
     */
    public function lookUpForEmptyAddressSetsCoordinatesError()
    {
        $geo = $this->createPartialMock(
            TestingGeo::class,
            ['setGeoError']
        );
        $geo->expects(self::once())->method('setGeoError');

        /* @var \OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures\TestingGeo $geo */
        $this->subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressSetsCoordinatesFromSetCoordinates()
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
    public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsNoCoordinates()
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertFalse($geo->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithoutSetCoordinatesNotClearsExistingCoordinates()
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertFalse($geo->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithoutSetCoordinatesSetsGeoError()
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertTrue($geo->hasGeoError());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithCoordinatesNotOverwritesCoordinates()
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
    public function lookUpAfterClearCoordinatesSetsNoCoordinates()
    {
        $this->subject->setCoordinates(42.0, 42.0);
        $this->subject->clearCoordinates();

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);

        self::assertFalse($geo->hasGeoCoordinates());
    }
}
