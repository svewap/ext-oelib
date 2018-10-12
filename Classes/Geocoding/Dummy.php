<?php

/**
 * This class represents a faked service to look up geo coordinates.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Geocoding_Dummy implements \Tx_Oelib_Interface_GeocodingLookup
{
    /**
     * faked coordinates with the keys "latitude" and "longitude" or empty if there are none
     *
     * @var float[]
     */
    private $coordinates = [];

    /**
     * The constructor.
     */
    public function __construct()
    {
    }

    /**
     * The destructor.
     */
    public function __destruct()
    {
    }

    /**
     * Looks up the geo coordinates of the address of an object and sets its
     * geo coordinates.
     *
     * @param \Tx_Oelib_Interface_Geo $geoObject
     *        the object for which the geo coordinates will be looked up and set
     *
     * @return void
     */
    public function lookUp(\Tx_Oelib_Interface_Geo $geoObject)
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
    public function setCoordinates($latitude, $longitude)
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
