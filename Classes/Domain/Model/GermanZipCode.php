<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a ZIP code within a city in Germany.
 *
 * The data comes from static tables.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
class GermanZipCode extends AbstractEntity implements \Tx_Oelib_Interface_Geo
{
    /**
     * @var string
     */
    protected $zipCode = '';

    /**
     * @var string
     */
    protected $cityName = '';

    /**
     * @var float
     */
    protected $longitude = 0.0;

    /**
     * @var float
     */
    protected $latitude = 0.0;

    /**
     * @return string
     */
    public function getZipCode(): string
    {
        return $this->zipCode;
    }

    /**
     * @param string $zipCode
     *
     * @return void
     */
    public function setZipCode(string $zipCode)
    {
        $this->zipCode = $zipCode;
    }

    /**
     * @return string
     */
    public function getCityName(): string
    {
        return $this->cityName;
    }

    /**
     * @param string $cityName
     *
     * @return void
     */
    public function setCityName(string $cityName)
    {
        $this->cityName = $cityName;
    }

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
        return $this->getZipCode() . ' ' . $this->getCityName() . ', DE';
    }

    /**
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     *
     * @return void
     */
    public function setLongitude(float $longitude)
    {
        $this->longitude = $longitude;
    }

    /**
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     *
     * @return void
     */
    public function setLatitude(float $latitude)
    {
        $this->latitude = $latitude;
    }

    /**
     * Checks whether this object has a non-empty address suitable for a geo
     * lookup.
     *
     * @return bool
     */
    public function hasGeoAddress()
    {
        return true;
    }

    /**
     * Retrieves this object's coordinates.
     *
     * @return float[] this object's geo coordinates using the keys "latitude" and "longitude"
     */
    public function getGeoCoordinates()
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }

    /**
     * This method must not be called.
     *
     * @param float[] $coordinates
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    public function setGeoCoordinates(array $coordinates)
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211338);
    }

    /**
     * Checks whether this object has non-empty coordinates.
     *
     * @return bool
     */
    public function hasGeoCoordinates()
    {
        return true;
    }

    /**
     * This method must not be called.
     *
     * @return void
     */
    public function clearGeoCoordinates()
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211386);
    }

    /**
     * Checks whether there has been a problem with this object's geo coordinates.
     *
     * @return bool
     */
    public function hasGeoError()
    {
        return false;
    }

    /**
     * This method must not be called.
     *
     * @param string $reason
     *
     * @return void
     */
    public function setGeoError($reason = '')
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211438);
    }

    /**
     * This method must not be called.
     *
     * @return void
     */
    public function clearGeoError()
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211447);
    }
}
