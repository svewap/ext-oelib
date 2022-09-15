<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Geocoding;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Geocoding\GeoCalculator;
use OliverKlee\Oelib\Interfaces\Geo;
use OliverKlee\Oelib\Model\AbstractModel;
use OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures\TestingGeo;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @covers \OliverKlee\Oelib\Geocoding\GeoCalculator
 */
final class GeoCalculatorTest extends UnitTestCase
{
    /**
     * @var GeoCalculator
     */
    protected $subject = null;

    /**
     * @var TestingGeo
     */
    protected $geoObject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new GeoCalculator();

        $this->geoObject = new TestingGeo();
        $this->geoObject->setGeoCoordinates(['latitude' => 50.733585499999997, 'longitude' => 7.1012733999999993]);
    }

    /**
     * @test
     */
    public function classIsSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    // Tests concerning calculateDistanceInKilometers

    /**
     * @test
     */
    public function calculateDistanceInKilometersForFirstObjectWithoutCoordinatesThrowsException(): void
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
    public function calculateDistanceInKilometersForSecondObjectWithoutCoordinatesThrowsException(): void
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
    public function calculateDistanceInKilometersForFirstObjectWithGeoErrorThrowsException(): void
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
    public function calculateDistanceInKilometersForSecondObjectWithGeoErrorThrowsException(): void
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
    public function calculateDistanceInKilometersForSameObjectsReturnsZero(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);

        self::assertSame(0.0, $this->subject->calculateDistanceInKilometers($bonn, $bonn));
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersForBonnAndCologneReturnsActualDistance(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(
            ['latitude' => 50.72254683, 'longitude' => 7.07519531]
        );
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        self::assertEqualsWithDelta(
            26.0,
            $this->subject->calculateDistanceInKilometers($bonn, $cologne),
            2.0
        );
    }

    /**
     * @test
     */
    public function calculateDistanceInKilometersReturnsSameDistanceForSwappedArguments(): void
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
    public function filterByDistanceKeepsElementWithinDistance(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        /** @var Collection<Geo&AbstractModel> $list */
        $list = new Collection();
        $list->add($bonn);

        $filteredList = $this->subject->filterByDistance($list, $cologne, 27.0);

        self::assertCount(1, $filteredList);
        self::assertSame($bonn, $filteredList->first());
    }

    /**
     * @test
     */
    public function filterByDistanceDropsElementOutOfDistance(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        /** @var Collection<Geo&AbstractModel> $list */
        $list = new Collection();
        $list->add($bonn);

        $filteredList = $this->subject->filterByDistance($list, $cologne, 25.0);

        self::assertTrue($filteredList->isEmpty());
    }

    /**
     * @test
     */
    public function filterByDistanceDropsElementWithoutCoordinates(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $nowhere = new TestingGeo();

        /** @var Collection<Geo&AbstractModel> $list */
        $list = new Collection();
        $list->add($nowhere);

        $filteredList = $this->subject->filterByDistance($list, $bonn, 25.0);

        self::assertTrue($filteredList->isEmpty());
    }

    /**
     * @test
     */
    public function filterByDistanceForElementWithoutCoordinatesReturnsEmptyList(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $nowhere = new TestingGeo();

        /** @var Collection<Geo&AbstractModel> $list */
        $list = new Collection();
        $list->add($bonn);

        $filteredList = $this->subject->filterByDistance($list, $nowhere, 25.0);

        self::assertTrue($filteredList->isEmpty());
    }

    /**
     * @test
     */
    public function filterByDistanceCanReturnTwoElements(): void
    {
        $bonn = new TestingGeo();
        $bonn->setGeoCoordinates(['latitude' => 50.72254683, 'longitude' => 7.07519531]);
        $cologne = new TestingGeo();
        $cologne->setGeoCoordinates(['latitude' => 50.94458443, 'longitude' => 6.9543457]);

        /** @var Collection<Geo&AbstractModel> $list */
        $list = new Collection();
        $list->add($bonn);
        $list->add($cologne);

        $filteredList = $this->subject->filterByDistance($list, $cologne, 27.0);

        self::assertCount(2, $filteredList);
    }

    /**
     * @test
     */
    public function moveWithoutCoordinatesNotSetsAnyCoordinates(): void
    {
        $geoObject = new TestingGeo();

        $this->subject->move($geoObject, 0, 100);

        self::assertFalse($geoObject->hasGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveWithEastDirectionNotChangesLatitude(): void
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
    public function moveWithWestDirectionNotChangesLatitude(): void
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
    public function moveWithSouthDirectionNotChangesLongitude(): void
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
    public function moveWithNorthDirectionNotChangesLongitude(): void
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
    public function moveNorthByValueOfOneDegreeLatitudeMovesByOneDegree(): void
    {
        $distance = 111.0;
        $north = 90;
        $latitudeBefore = $this->geoObject->getGeoCoordinates()['latitude'];

        $this->subject->move($this->geoObject, $north, $distance);

        $latitudeAfter = $this->geoObject->getGeoCoordinates()['latitude'];
        self::assertEqualsWithDelta(-1.0, $latitudeBefore - $latitudeAfter, 0.00001);
    }

    /**
     * @test
     */
    public function moveSouthByValueOfOneDegreeLatitudeMovesByOneDegree(): void
    {
        $distance = 111.0;
        $south = 270;
        $latitudeBefore = $this->geoObject->getGeoCoordinates()['latitude'];

        $this->subject->move($this->geoObject, $south, $distance);

        $latitudeAfter = $this->geoObject->getGeoCoordinates()['latitude'];
        self::assertEqualsWithDelta(1.0, $latitudeBefore - $latitudeAfter, 0.00001);
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
    public function moveMovesByGivenDistanceWithPositiveDistance(int $direction): void
    {
        $distance = 100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->move($otherGeoObject, $direction, $distance);

        self::assertEqualsWithDelta(
            $distance,
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
            $distance / 10
        );
    }

    /**
     * @test
     * @dataProvider directionDataProvider
     *
     * @param int $direction
     */
    public function moveMovesByGivenDistanceWithNegativeDistance(int $direction): void
    {
        $distance = -100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->move($otherGeoObject, $direction, $distance);

        self::assertEqualsWithDelta(
            \abs($distance),
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
            \abs($distance) / 10
        );
    }

    /**
     * @test
     */
    public function moveByRandomDistanceWithNegativeNumberThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->moveByRandomDistance($this->geoObject, 0, -1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function moveByRandomDistanceWithZeroNotThrowsException(): void
    {
        $this->subject->moveByRandomDistance($this->geoObject, 0, 0);
    }

    /**
     * @test
     * @dataProvider directionDataProvider
     *
     * @param int $direction
     */
    public function moveByRandomDistanceChangesCoordinates(int $direction): void
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
    public function moveByRandomDistanceMovesAtMostByGivenDistanceWithPositiveDistance(int $direction): void
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
    public function moveByRandomDistanceCalledTwiceCreatesDifferentCoordinates(): void
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
    public function moveInRandomDirectionChangesCoordinates(): void
    {
        $originalCoordinates = $this->geoObject->getGeoCoordinates();

        $distance = 100.0;
        $this->subject->moveInRandomDirection($this->geoObject, $distance);

        self::assertNotSame($originalCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionCalledTwiceCreatesDifferentCoordinates(): void
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
    public function moveInRandomDirectionMovesByGivenDistanceWithPositiveDistance(): void
    {
        $distance = 100.0;
        $otherGeoObject = clone $this->geoObject;
        $this->subject->moveInRandomDirection($otherGeoObject, $distance);

        self::assertEqualsWithDelta(
            $distance,
            $this->subject->calculateDistanceInKilometers($this->geoObject, $otherGeoObject),
            $distance / 10
        );
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceWithNegativeNumberThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, -1);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function moveInRandomDirectionAndDistanceWithZeroNotThrowsException(): void
    {
        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, 0);
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceChangesCoordinates(): void
    {
        $originalCoordinates = $this->geoObject->getGeoCoordinates();

        $maximumDistance = 100.0;
        $this->subject->moveInRandomDirectionAndDistance($this->geoObject, $maximumDistance);

        self::assertNotSame($originalCoordinates, $this->geoObject->getGeoCoordinates());
    }

    /**
     * @test
     */
    public function moveInRandomDirectionAndDistanceCalledTwiceCreatesDifferentCoordinates(): void
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
    public function moveInRandomDirectionAndDistanceMovesAtMostByGivenDistanceWithPositiveDistance(): void
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
