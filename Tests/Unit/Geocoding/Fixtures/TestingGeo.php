<?php

namespace OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures;

/**
 * This class represents an testing object that can have an address and geo coordinates.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingGeo extends \Tx_Oelib_Model implements \Tx_Oelib_Interface_Geo
{
    /**
     * whether this object has a geo error
     *
     * @var bool
     */
    private $hasGeoError = false;

    /**
     * @var string
     */
    private $geoErrorReason = '';

    /**
     * the address of this object
     *
     * @var string
     */
    private $address = '';

    /**
     * the geo coordinates of this object
     *
     * @var float[]
     */
    private $coordinates = [];

    /**
     * Returns this object's address formatted for a geo lookup, for example
     * "Pariser Str. 50, 53117 Auerberg, Bonn, DE". Any part of this address
     * might be missing, though.
     *
     * @return string this object's address formatted for a geo lookup,
     *                will be empty if this object has no address
     */
    public function getGeoAddress()
    {
        return $this->address;
    }

    /**
     * Sets this object's geo address.
     *
     * @param string $address
     *        the address to set, for example
     *        "Pariser Str. 50, 53117 Auerberg, Bonn, DE", may be empty
     *
     * @return void
     */
    public function setGeoAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Checks whether this object has a non-empty address suitable for a geo
     * lookup.
     *
     * @return bool TRUE if this object has a non-empty address, FALSE
     *                 otherwise
     */
    public function hasGeoAddress()
    {
        return $this->address !== '';
    }

    /**
     * Retrieves this object's coordinates.
     *
     * @return float[]
     *         this object's geo coordinates using the keys "latitude" and
     *         "longitude", will be empty if this object has no coordinates
     */
    public function getGeoCoordinates()
    {
        return $this->coordinates;
    }

    /**
     * Sets this object's coordinates.
     *
     * @param float[] $coordinates
     *        the coordinates, using the keys "latitude" and "longitude",
     *        the array values must not be empty
     *
     * @return void
     */
    public function setGeoCoordinates(array $coordinates)
    {
        $this->coordinates = $coordinates;
    }

    /**
     * Checks whether this object has non-empty coordinates.
     *
     * @return bool TRUE if this object has both a non-empty longitude and
     *                 a non-empty latitude, FALSE otherwise
     */
    public function hasGeoCoordinates()
    {
        return !empty($this->coordinates);
    }

    /**
     * Purges this object's geo coordinates.
     *
     * Note: Calling this function has no influence on this object's geo error
     * status.
     *
     * @return void
     */
    public function clearGeoCoordinates()
    {
        $this->coordinates = [];
    }

    /**
     * Checks whether there has been a problem with this object's geo
     * coordinates.
     *
     * Note: This function only checks whether there has been an error with the
     * coordinates, not whether this object actually has coordinates.
     *
     * @return bool TRUE if there has been an error, FALSE otherwise
     */
    public function hasGeoError()
    {
        return $this->hasGeoError;
    }

    /**
     * Marks this object as having an error with the geo coordinates.
     *
     * @param string $reason
     *
     * @return void
     */
    public function setGeoError($reason = '')
    {
        $this->hasGeoError = true;
        $this->geoErrorReason = $reason;
    }

    /**
     * @return string
     */
    public function getGeoErrorReason()
    {
        return $this->geoErrorReason;
    }

    /**
     * Marks this object as not having an error with the geo coordinates.
     *
     * @return void
     */
    public function clearGeoError()
    {
        $this->hasGeoError = false;
        $this->geoErrorReason = '';
    }
}
