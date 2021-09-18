<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interface represents an object that can have geo coordinates.
 */
interface Geo
{
    /**
     * Returns this object's address formatted for a geo lookup, for example
     * "Pariser Str. 50, 53117 Auerberg, Bonn, DE". Any part of this address
     * might be missing, though.
     *
     * @return string this object's address formatted for a geo lookup,
     *                will be empty if this object has no address
     */
    public function getGeoAddress(): string;

    /**
     * Checks whether this object has a non-empty address suitable for a geo lookup.
     *
     * @return bool TRUE if this object has a non-empty address, FALSE otherwise
     */
    public function hasGeoAddress(): bool;

    /**
     * Retrieves this object's coordinates.
     *
     * @return array<string, float> this object's geo coordinates using the keys "latitude" and
     *         "longitude", will be empty if this object has no coordinates
     */
    public function getGeoCoordinates(): array;

    /**
     * Sets this object's coordinates.
     *
     * @param array<string, float> $coordinates the coordinates, using the keys "latitude" and "longitude",
     *        the array values must not be empty
     */
    public function setGeoCoordinates(array $coordinates): void;

    /**
     * Checks whether this object has non-empty coordinates.
     *
     * @return bool TRUE if this object has both a non-empty longitude and a non-empty latitude, FALSE otherwise
     */
    public function hasGeoCoordinates(): bool;

    /**
     * Purges this object's geo coordinates.
     *
     * Note: Calling this function has no influence on this object's geo error status.
     */
    public function clearGeoCoordinates(): void;

    /**
     * Checks whether there has been a problem with this object's geo
     * coordinates.
     *
     * Note: This function only checks whether there has been an error with the
     * coordinates, not whether this object actually has coordinates.
     *
     * @return bool TRUE if there has been an error, FALSE otherwise
     */
    public function hasGeoError(): bool;

    /**
     * Marks this object as having an error with the geo coordinates.
     */
    public function setGeoError(string $reason = ''): void;

    /**
     * Marks this object as not having an error with the geo coordinates.
     */
    public function clearGeoError(): void;
}
