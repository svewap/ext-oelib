<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Geocoding;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Geocoding\GeoCalculator;
use OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures\TestingGeo;
use TYPO3\CMS\Core\SingletonInterface;

class GeoCalculatorTest extends UnitTestCase
{
    /**
     * @var GeoCalculator
     */
    protected $subject = null;

    /**
     * @var TestingGeo
     */
    protected $geoObject = null;

    protected function setUp()
    {
        $this->subject = new GeoCalculator();

        $this->geoObject = new TestingGeo();
        $this->geoObject->setGeoCoordinates(['latitude' => 50.733585499999997, 'longitude' => 7.1012733999999993]);
    }

    /**
     * @test
     */
    public function classIsSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    // Tests concerning calculateDistanceInKilometers

    /**
     * @test
     */
    public function calculateDistanceInKilometersForFirstObjectWithoutCoordinatesThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $noCoordinates = new TestingGeo();
        $noCoordinates->clearGeoCoordinates();
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        $this->subject->calculateDistanceInKilometers($noCoordinates, $bonn);
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForSecondObjectWithoutCoordinatesThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $noCoordinates = new TestingGeo();
        $noCoordinates->clearGeoCoordinates();

        $this->subject->calculateDistanceInKilometers($bonn, $noCoordinates);
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForFirstObjectWithGeoErrorThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $brokenBonn = new TestingGeo();
        $brokenBonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $brokenBonn->setGeoError();
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        $this->subject->calculateDistanceInKilometers($brokenBonn, $bonn);
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForSecondObjectWithGeoErrorThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $brokenBonn = new TestingGeo();
        $brokenBonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $brokenBonn->setGeoError();

        $this->subject->calculateDistanceInKilometers($bonn, $brokenBonn);
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForSameObjectsReturnsZero()
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        self::assertSame(0.0, $this->subject->calculateDistanceInKilometers($bonn, $bonn));
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForBonnAndCologneReturnsActualDistance()
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(
            ['latitude' => 50.72254683, 'longitude' => 7.07519531]
        );
        $cologne = new TestingGeo();
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
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        self::assertSame(
            $this->subject->calculateDistanceInKilometers($bonn, $cologne),
            $this->subject->calculateDistanceInKilometers($cologne, $bonn)
        );
    }

    // Tests concerning filterByDistance

    /**
     * @test
     */
    public function filterByDistanceKeepsElementWithinDistance()
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        /** @var Collection<TestingGeo> $list */
        $list = new Collection();
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
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        /** @var Collection<TestingGeo> $list */
        $list = new Collection();
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
    public function filterByDistanceDropsElementWithoutCoordinates()
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $nowhere = new TestingGeo();

        /** @var Collection<TestingGeo> $list */
        $list = new Collection();
        $list->add($nowhere);

        $filteredList = $this->subject->filterByDistance($list, $bonn, 25.0);

        self::assertTrue($filteredList->isEmpty());
    }

    /**
     * @test
     */
    public function filterByDistanceForElementWithoutCoordinatesReturnsEmptyList()
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $nowhere = new TestingGeo();

        /** @var Collection<TestingGeo> $list */
        $list = new Collection();
        $list->add($bonn);

        $filteredList = $this->subject->filterByDistance($list, $nowhere, 25.0);

        self::assertTrue($filteredList->isEmpty());
    }

    /**
     * @test
     */
    public function filterByDistanceCanReturnTwoElements()
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        /** @var Collection<TestingGeo> $list */
        $list = new Collection();
        $list->add($bonn);
        $list->add($cologne);

        $filteredList = $this->subject->filterByDistance($list, $cologne, 27.0);

        self::assertSame(2, $filteredList->count());
    }

    /**
     * @test
     */
    public function moveWithoutCoordinatesNotSetsAnyCoordinates()
    {
        $geoObject = new TestingGeo();

        $this->subject->move($geoObject, 0, 100);

        self::assertFalse($geoObject->hasGeoCoordinates());
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
     * @test
     */
    public function moveNorthByValueOfOneDegreeLatitudeMovesByOneDegree()
    {
        $distance = 111.0;
        $north = 90;
        $latitudeBefore = $this->geoObject->getGeoCoordinates()['latitude'];

        $this->subject->move($this->geoObject, $north, $distance);

        $latitudeAfter = $this->geoObject->getGeoCoordinates()['latitude'];
        self::assertEquals(-1.0, $latitudeBefore - $latitudeAfter, 'The distance is not as expected.', 0.00001);
    }

    /**
     * @test
     */
    public function moveSouthByValueOfOneDegreeLatitudeMovesByOneDegree()
    {
        $distance = 111.0;
        $south = 270;
        $latitudeBefore = $this->geoObject->getGeoCoordinates()['latitude'];

        $this->subject->move($this->geoObject, $south, $distance);

        $latitudeAfter = $this->geoObject->getGeoCoordinates()['latitude'];
        self::assertEquals(1.0, $latitudeBefore - $latitudeAfter, 'The distance is not as expected.', 0.00001);
    }

    /**
     * @return int[][]
     */
    public function directionDataProvider(): array
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
    public function moveMovesByGivenDistanceWithPositiveDistance(int $direction)
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
    public function moveMovesByGivenDistanceWithNegativeDistance(int $direction)
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
     */
    public function moveByRandomDistanceWithNegativeNumberThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->moveByRandomDistance($this->geoObject, 0, -1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
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
    public function moveByRandomDistanceChangesCoordinates(int $direction)
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
    public function moveByRandomDistanceMovesAtMostByGivenDistanceWithPositiveDistance(int $direction)
    {
        $maximumDistance = 100.0;

        for ($i = 0; $i < 1000; $i++) {
            $otherGeoObject = clone $this->geoObject;
            $this->subject->moveByRandomDistance($otherGeoObject, $direction, $maximumDistance);

            self::assertLessThanOrEqual(
                $maximumDistance,
                $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject)
            );
        }
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
     */
    public function moveInRandomDirectionAndDistanceWithNegativeNumberThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, -1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
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

        for ($i = 0; $i < 1000; $i++) {
            $otherGeoObject = clone $this->geoObject;
            $this->subject->moveInRandomDirectionAndDistance($otherGeoObject, $maximumDistance);

            self::assertLessThanOrEqual(
                $maximumDistance,
                $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject)
            );
        }
    }
}
