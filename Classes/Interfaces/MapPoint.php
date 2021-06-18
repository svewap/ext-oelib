<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interface represents an object that can be positioned on a map, e.g.,
 * on a Google Map.
 */
interface MapPoint
{
    /**
     * Returns this object's coordinates.
     *
     * @return array<string, float>
     *         this object's geo coordinates using the keys "latitude" and
     *         "longitude", will be empty if this object has no coordinates
     */
    public function getGeoCoordinates(): array;

    /**
     * Checks whether this object has non-empty coordinates.
     *
     * @return bool
     *         TRUE if this object has both a non-empty longitude and a
     *         non-empty latitude, FALSE otherwise
     */
    public function hasGeoCoordinates(): bool;

    /**
     * Gets the title for the tooltip of this object.
     *
     * @return string the tooltip title (plain text), might be empty
     */
    public function getTooltipTitle(): string;

    /**
     * Checks whether this object has a non-empty tooltip title.
     *
     * @return bool
     *         TRUE if this object has a non-empty tooltip title, FALSE otherwise
     */
    public function hasTooltipTitle(): bool;

    /**
     * Gets the info window content of this object.
     *
     * @return string the info window content (HTML), might be empty
     */
    public function getInfoWindowContent(): string;

    /**
     * Checks whether this object has a non-empty info window content.
     *
     * @return bool
     *         TRUE if this object has a non-empty info window content, FALSE otherwise
     */
    public function hasInfoWindowContent(): bool;
}
