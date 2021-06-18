<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Interfaces;

/**
 * This interfaces represents a postal address.
 */
interface Address
{
    /**
     * Returns the city of the current address.
     *
     * @return string the city of the current address, will be empty if no city
     *                was set
     */
    public function getCity();

    /**
     * Returns the street of the current address.
     *
     * @return string the street of the current address, may be multi-line,
     *                will be empty if no street was set
     */
    public function getStreet();

    /**
     * Returns the ZIP code of the current address
     *
     * @return string the ZIP code of the current address, will be empty if no
     *                ZIP code was set
     */
    public function getZip();

    /**
     * Returns the homepage of the current address.
     *
     * @return string the homepage of the current address, will be empty if no
     *                homepage was set
     */
    public function getHomepage();

    /**
     * Returns the telephone number of the current address.
     *
     * @return string the telephone number of the current address, will be empty
     *                if no telephone number was set
     */
    public function getPhoneNumber();
}
