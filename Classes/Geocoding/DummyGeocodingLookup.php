<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Geocoding;

use OliverKlee\Oelib\Interfaces\GeocodingLookup;
use OliverKlee\Oelib\Interfaces\Geo;

/**
 * This class represents a faked service to look up geo coordinates.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DummyGeocodingLookup implements GeocodingLookup
{
    /**
     * faked coordinates with the keys "latitude" and "longitude" or empty if there are none
     *
     * @var float[]
     */
    private $coordinates = [];

    /**
     * Looks up the geo coordinates of the address of an object and sets its
     * geo coordinates.
     *
     * @param Geo $geoObject
     *        the object for which the geo coordinates will be looked up and set
     *
     * @return void
     */
    public function lookUp(Geo $geoObject)
    {
        if ($geoObject->hasGeoError() || $geoObject->hasGeoCoordinates()) {
            return;
        }
        if (!$geoObject->hasGeoAddress()) {
            $geoObject->setGeoError();
            return;
        }

        if (!empty($this->coordinates)) {
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
     *
     * @return void
     */
    public function setCoordinates(float $latitude, float $longitude)
    {
        $this->coordinates = [
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];
    }

    /**
     * Resets the fake coordinates.
     *
     * @return void
     */
    public function clearCoordinates()
    {
        $this->coordinates = [];
    }
}
