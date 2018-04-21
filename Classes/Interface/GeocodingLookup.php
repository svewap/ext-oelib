<?php

/**
 * This interface provides functions for looking up the coordinates of an
 * address.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Oelib_Interface_GeocodingLookup
{
    /**
     * Looks up the geo coordinates of the address of an object and sets its
     * geo coordinates.
     *
     * @param \Tx_Oelib_Interface_Geo $geoObject
     *        the object for which the geo coordinates will be looked up and set
     *
     * @return void
     */
    public function lookUp(\Tx_Oelib_Interface_Geo $geoObject);
}
