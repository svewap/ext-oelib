<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures;

use OliverKlee\Oelib\Interfaces\Geo;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents a testing object that can have an address and geo coordinates.
 */
class TestingGeo extends AbstractModel implements Geo
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
     * @var array{latitude?: float, longitude?: float}
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
    public function getGeoAddress(): string
    {
        return $this->address;
    }

    /**
     * Sets this object's geo address.
     *
     * @param string $address the address to set, for example
     *        "Pariser Str. 50, 53117 Auerberg, Bonn, DE", may be empty
     */
    public function setGeoAddress(string $address): void
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
    public function hasGeoAddress(): bool
    {
        return $this->address !== '';
    }

    /**
     * @return array{latitude: float, longitude: float}
     *
     * @throws \BadMethodCallException
     */
    public function getGeoCoordinates(): array
    {
        if (!isset($this->coordinates['latitude'], $this->coordinates['longitude'])) {
            throw new \BadMethodCallException('Missing geo coordinates!', 1633018227);
        }

        return $this->coordinates;
    }

    /**
     * Sets this object's coordinates.
     *
     * @param array{latitude: float, longitude: float} $coordinates
     */
    public function setGeoCoordinates(array $coordinates): void
    {
        $this->coordinates = $coordinates;
    }

    /**
     * Checks whether this object has non-empty coordinates.
     *
     * @return bool TRUE if this object has both a non-empty longitude and
     *                 a non-empty latitude, FALSE otherwise
     */
    public function hasGeoCoordinates(): bool
    {
        return $this->coordinates !== [];
    }

    /**
     * Purges this object's geo coordinates.
     *
     * Note: Calling this function has no influence on this object's geo error
     * status.
     */
    public function clearGeoCoordinates(): void
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
    public function hasGeoError(): bool
    {
        return $this->hasGeoError;
    }

    /**
     * Marks this object as having an error with the geo coordinates.
     */
    public function setGeoError(string $reason = ''): void
    {
        $this->hasGeoError = true;
        $this->geoErrorReason = $reason;
    }

    /**
     * @return string
     */
    public function getGeoErrorReason(): string
    {
        return $this->geoErrorReason;
    }

    /**
     * Marks this object as not having an error with the geo coordinates.
     */
    public function clearGeoError(): void
    {
        $this->hasGeoError = false;
        $this->geoErrorReason = '';
    }
}
