<?php

use TYPO3\CMS\Core\SingletonInterface;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Geocoding_CalculatorTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_Geocoding_Calculator
     */
    protected $subject = null;

    /**
     * @var \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo
     */
    protected $geoObject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Geocoding_Calculator();

        $this->geoObject = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $this->geoObject->setGeoCoordinates(['latitude' => 50.733585499999997, 'longitude' => 7.1012733999999993]);
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /*
     * Tests concerning calculateDistanceInKilometers
     */

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function calculateDistanceInKilometersForFirstObjectWithoutCoordinatesThrowsException()
    {
        $noCoordinates = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $noCoordinates->clearGeoCoordinates();
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        $this->subject->calculateDistanceInKilometers($noCoordinates, $bonn);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function calculateDistanceInKilometersForSecondObjectWithoutCoordinatesThrowsException()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $noCoordinates = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $noCoordinates->clearGeoCoordinates();

        $this->subject->calculateDistanceInKilometers($bonn, $noCoordinates);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function calculateDistanceInKilometersForFirstObjectWithGeoErrorThrowsException()
    {
        $brokenBonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $brokenBonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $brokenBonn->setGeoError();
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        $this->subject->calculateDistanceInKilometers($brokenBonn, $bonn);
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function calculateDistanceInKilometersForSecondObjectWithGeoErrorThrowsException()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $brokenBonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $brokenBonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $brokenBonn->setGeoError();

        $this->subject->calculateDistanceInKilometers($bonn, $brokenBonn);
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForSameObjectsReturnsZero()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        self::assertSame(0.0, $this->subject->calculateDistanceInKilometers($bonn, $bonn));
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForBonnAndCologneReturnsActualDistance()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(
            ['latitude' => 50.72254683, 'longitude' => 7.07519531]
        );
        $cologne = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        self::assertEquals(
            26.0,
            $this->subject->calculateDistanceInKilometers($bonn, $cologne),
            '',
            2.0
        );
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersReturnsSameDistanceForSwappedArguments()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        self::assertSame(
            $this->subject->calculateDistanceInKilometers($bonn, $cologne),
            $this->subject->calculateDistanceInKilometers($cologne, $bonn)
        );
    }

    /*
     * Tests concerning filterByDistance
     */

    /**
     * @test
     */
    public function filterByDistanceKeepsElementWithinDistance()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        $list = new \Tx_Oelib_List();
        $list->add($bonn);

        $filteredList = $this->subject->filterByDistance(
            $list,
            $cologne,
            27.0
        );

        self::assertSame(1, $filteredList->count());
        self::assertSame($bonn, $filteredList->first());
    }

    /**
     * @test
     */
    public function filterByDistanceDropsElementOutOfDistance()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        $list = new \Tx_Oelib_List();
        $list->add($bonn);

        $filteredList = $this->subject->filterByDistance(
            $list,
            $cologne,
            25.0
        );

        self::assertTrue($filteredList->isEmpty());
    }

    /**
     * @test
     */
    public function filterByDistanceCanReturnTwoElements()
    {
        $bonn = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new \Tx_Oelib_Tests_LegacyUnit_Fixtures_TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        $list = new \Tx_Oelib_List();
        $list->add($bonn);
        $list->add($cologne);

        $filteredList = $this->subject->filterByDistance($list, $cologne, 27.0);

        self::assertSame(2, $filteredList->count());
    }

    /**
     * @test
     */
    public function moveWithEastDirectionNotChangesLatitude()
    {
        $otherGeoObject = clone $this->geoObject;
        $distance = 100;
        $this->subject->move($otherGeoObject, 0, $distance);

        $originalCoordinates = $this->geoObject->getGeoCoordinates();
        $changedCoordinates = $otherGeoObject->getGeoCoordinates();

        self::assertSame($originalCoordinates['latitude'], $changedCoordinates['latitude']);
    }

    /**
     * @test
     */
    public function moveWithWestDirectionNotChangesLatitude()
    {
        $otherGeoObject = clone $this->geoObject;
        $distance = 100;
        $this->subject->move($otherGeoObject, 180, $distance);

        $originalCoordinates = $this->geoObject->getGeoCoordinates();
        $changedCoordinates = $otherGeoObject->getGeoCoordinates();

        self::assertSame($originalCoordinates['latitude'], $changedCoordinates['latitude']);
    }

    /**
     * @test
     */
    public function moveWithSouthDirectionNotChangesLongitude()
    {
        $otherGeoObject = clone $this->geoObject;
        $distance = 100;
        $this->subject->move($otherGeoObject, 270, $distance);

        $originalCoordinates = $this->geoObject->getGeoCoordinates();
        $changedCoordinates = $otherGeoObject->getGeoCoordinates();

        self::assertSame($originalCoordinates['longitude'], $changedCoordinates['longitude']);
    }

    /**
     * @test
     */
    public function moveWithNorthDirectionNotChangesLongitude()
    {
        $otherGeoObject = clone $this->geoObject;
        $distance = 100;
        $this->subject->move($otherGeoObject, 90, $distance);

        $originalCoordinates = $this->geoObject->getGeoCoordinates();
        $changedCoordinates = $otherGeoObject->getGeoCoordinates();

        self::assertSame($originalCoordinates['longitude'], $changedCoordinates['longitude']);
    }

    /**
     * @return int[][]
     */
    public function directionDataProvider()
    {
        return [
            'E' => [0],
            'NE' => [45],
            'N' => [90],
            'NW' => [135],
            'W' => [180],
            'SW' => [225],
            'S' => [270],
            'SE' => [315],
        ];
    }

    /**
     * @test
     * @dataProvider directionDataProvider
     *
     * @param int $direction
     */
    public function moveMovesByGivenDistanceWithPositiveDistance($direction)
    {
        $distance = 100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->move($otherGeoObject, $direction, $distance);

        self::assertEquals(
            $distance,
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
            'The distance is not as expected.',
            $distance / 10
        );
    }

    /**
     * @test
     * @dataProvider directionDataProvider
     *
     * @param int $direction
     */
    public function moveMovesByGivenDistanceWithNegativeDistance($direction)
    {
        $distance = -100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->move($otherGeoObject, $direction, $distance);

        self::assertEquals(
            \abs($distance),
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
            'The distance is not as expected.',
            \abs($distance) / 10
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function moveByRandomDistanceWithNegativeNumberThrowsException()
    {
        $this->subject->moveByRandomDistance($this->geoObject, 0, -1);
    }

    /**
     * @test
     */
    public function moveByRandomDistanceWithZeroNotThrowsException()
    {
        $this->subject->moveByRandomDistance($this->geoObject, 0, 0);
    }

    /**
     * @test
     * @dataProvider directionDataProvider
     *
     * @param int $direction
     */
    public function moveByRandomDistanceChangesCoordinates($direction)
    {
        $originalCoordinates = $this->geoObject->getGeoCoordinates();

        $maximumDistance = 100.0;
        $this->subject->moveByRandomDistance($this->geoObject, $direction, $maximumDistance);

        self::assertNotSame($originalCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     * @dataProvider directionDataProvider
     *
     * @param int $direction
     */
    public function moveByRandomDistanceMovesAtMostByGivenDistanceWithPositiveDistance($direction)
    {
        $maximumDistance = 100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->moveByRandomDistance($otherGeoObject, $direction, $maximumDistance);

        self::assertLessThanOrEqual(
            $maximumDistance,
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject)
        );
    }

    /**
     * @test
     */
    public function moveByRandomDistanceCalledTwiceCreatesDifferentCoordinates()
    {
        $maximumDistance = 100.0;
        $this->subject->moveByRandomDistance($this->geoObject, 0, $maximumDistance);
        $firstCoordinates = $this->geoObject->getGeoCoordinates();

        $this->subject->moveByRandomDistance($this->geoObject, 0, $maximumDistance);

        self::assertNotSame($firstCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionChangesCoordinates()
    {
        $originalCoordinates = $this->geoObject->getGeoCoordinates();

        $distance = 100.0;
        $this->subject->moveInRandomDirection($this->geoObject, $distance);

        self::assertNotSame($originalCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionCalledTwiceCreatesDifferentCoordinates()
    {
        $distance = 100.0;
        $this->subject->moveInRandomDirection($this->geoObject, $distance);
        $firstCoordinates = $this->geoObject->getGeoCoordinates();

        $this->subject->moveInRandomDirection($this->geoObject, $distance);

        self::assertNotSame($firstCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionMovesByGivenDistanceWithPositiveDistance()
    {
        $distance = 100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->moveInRandomDirection($otherGeoObject, $distance);

        self::assertEquals(
            $distance,
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
            'The distance is not as expected.',
            $distance / 10
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function moveInRandomDirectionAndDistanceWithNegativeNumberThrowsException()
    {
        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, -1);
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceWithZeroNotThrowsException()
    {
        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, 0);
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceChangesCoordinates()
    {
        $originalCoordinates = $this->geoObject->getGeoCoordinates();

        $maximumDistance = 100.0;
        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);

        self::assertNotSame($originalCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceCalledTwiceCreatesDifferentCoordinates()
    {
        $maximumDistance = 100.0;
        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);
        $firstCoordinates = $this->geoObject->getGeoCoordinates();

        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);

        self::assertNotSame($firstCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceMovesAtMostByGivenDistanceWithPositiveDistance()
    {
        $maximumDistance = 100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->moveInRandomDirectionAndDistance($otherGeoObject, $maximumDistance);

        self::assertLessThanOrEqual(
            $maximumDistance,
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject)
        );
    }
}
