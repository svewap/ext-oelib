<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interface provides functions for looking up the coordinates of an
 * address.
 */
interface GeocodingLookup
{
    /**
     * Looks up the geo coordinates of the address of an object and sets its geo coordinates.
     */
    public function lookUp(Geo $geoObject): void;
}
