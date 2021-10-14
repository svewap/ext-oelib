<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Geocoding;

use OliverKlee\Oelib\Interfaces\Geo;
use OliverKlee\Oelib\Interfaces\GeocodingLookup;

/**
 * This class represents a faked service to look up geo coordinates.
 */
class DummyGeocodingLookup implements GeocodingLookup
{
    /**
     * faked coordinates with the keys "latitude" and "longitude" or empty if there are none
     *
     * @var array<string, float>
     */
    private $coordinates = [];

    /**
     * Looks up the geo coordinates of the address of an object and sets its geo coordinates.
     */
    public function lookUp(Geo $geoObject): void
    {
        if ($geoObject->hasGeoError() || $geoObject->hasGeoCoordinates()) {
            return;
        }
        if (!$geoObject->hasGeoAddress()) {
            $geoObject->setGeoError();
            return;
        }

        if (isset($this->coordinates['latitude'], $this->coordinates['longitude'])) {
            $geoObject->setGeoCoordinates($this->coordinates);
        } else {
            $geoObject->setGeoError();
        }
    }

    /**
     * Sets the coordinates lookUp() is supposed to return.
     *
     * @param float $latitude latitude coordinate
     * @param float $longitude longitude coordinate
     */
    public function setCoordinates(float $latitude, float $longitude): void
    {
        $this->coordinates = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Resets the fake coordinates.
     */
    public function clearCoordinates(): void
    {
        $this->coordinates = [];
    }
}
