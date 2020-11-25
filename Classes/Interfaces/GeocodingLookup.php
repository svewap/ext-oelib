<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interface provides functions for looking up the coordinates of an
 * address.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface GeocodingLookup
{
    /**
     * Looks up the geo coordinates of the address of an object and sets its
     * geo coordinates.
     *
     * @param Geo $geoObject
     *        the object for which the geo coordinates will be looked up and set
     *
     * @return void
     */
    public function lookUp(Geo $geoObject);
}
