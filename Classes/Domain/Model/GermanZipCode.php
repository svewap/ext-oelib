<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model;

use OliverKlee\Oelib\Interfaces\Geo;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * This class represents a ZIP code within a city in Germany.
 *
 * The data comes from static tables.
 */
class GermanZipCode extends AbstractEntity implements Geo
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

    public function setZipCode(string $zipCode): void
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

    public function setCityName(string $cityName): void
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
    public function getGeoAddress(): string
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

    public function setLongitude(float $longitude): void
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

    public function setLatitude(float $latitude): void
    {
        $this->latitude = $latitude;
    }

    /**
     * Checks whether this object has a non-empty address suitable for a geo
     * lookup.
     *
     * @return bool
     */
    public function hasGeoAddress(): bool
    {
        return true;
    }

    /**
     * Retrieves this object's coordinates.
     *
     * @return array<string, float> this object's geo coordinates using the keys "latitude" and "longitude"
     */
    public function getGeoCoordinates(): array
    {
        return [
            'latitude' => $this->getLatitude(),
            'longitude' => $this->getLongitude(),
        ];
    }

    /**
     * This method must not be called.
     *
     * @param array<string, float> $coordinates
     *
     * @throws \BadMethodCallException
     */
    public function setGeoCoordinates(array $coordinates): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211338);
    }

    /**
     * Checks whether this object has non-empty coordinates.
     *
     * @return bool
     */
    public function hasGeoCoordinates(): bool
    {
        return true;
    }

    /**
     * This method must not be called.
     */
    public function clearGeoCoordinates(): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211386);
    }

    /**
     * Checks whether there has been a problem with this object's geo coordinates.
     *
     * @return bool
     */
    public function hasGeoError(): bool
    {
        return false;
    }

    /**
     * This method must not be called.
     */
    public function setGeoError(string $reason = ''): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211438);
    }

    /**
     * This method must not be called.
     */
    public function clearGeoError(): void
    {
        throw new \BadMethodCallException('This method must not be called.', 1542211447);
    }
}
