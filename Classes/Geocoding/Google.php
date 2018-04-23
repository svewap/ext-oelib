<?php

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a service to look up geo coordinates via Google Maps.
 *
 * @see https://developers.google.com/maps/documentation/javascript/geocoding?#GeocodingStatusCodes
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Geocoding_Google implements \Tx_Oelib_Interface_GeocodingLookup
{
    /**
     * @var string
     */
    const STATUS_OK = 'OK';

    /**
     * @var string
     */
    const STATUS_ZERO_RESULTS = 'ZERO_RESULTS';

    /**
     * @var string
     */
    const STATUS_INVALID_REQUEST = 'INVALID_REQUEST';

    /**
     * the base URL of the Google Maps geo coding service
     *
     * @var string
     */
    const BASE_URL = 'https://maps.google.com/maps/api/geocode/json?sensor=false';

    /**
     * the Singleton instance
     *
     * @var \Tx_Oelib_Interface_GeocodingLookup
     */
    private static $instance = null;

    /**
     * the amount of time (in seconds) that need to pass between subsequent geocoding requests
     *
     * @see https://developers.google.com/maps/documentation/javascript/geocoding#UsageLimits
     *
     * @var int
     */
    const THROTTLING_IN_SECONDS = 1;

    /**
     * @var int
     */
    const MAXIMUM_ATTEMPTS = 5;

    /**
     * the timestamp of the last geocoding request (will be 0.0 before the first request)
     *
     * @var float
     */
    private static $lastGeocodingTimestamp = 0.0;

    /**
     * The constructor. Do not call this constructor directly. Use getInstance() instead.
     */
    protected function __construct()
    {
    }

    /**
     * Retrieves the Singleton instance of the GoogleMaps look-up.
     *
     * @return \Tx_Oelib_Interface_GeocodingLookup the Singleton GoogleMaps look-up
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new \Tx_Oelib_Geocoding_Google();
        }

        return self::$instance;
    }

    /**
     * Sets the Singleton GoogleMaps look-up instance.
     *
     * Note: This function is to be used for testing only.
     *
     * @param \Tx_Oelib_Interface_GeocodingLookup $instance the instance which getInstance() should return
     *
     * @return void
     */
    public static function setInstance(\Tx_Oelib_Interface_GeocodingLookup $instance)
    {
        self::$instance = $instance;
    }

    /**
     * Purges the current GoogleMaps look-up instance.
     *
     * @return void
     */
    public static function purgeInstance()
    {
        self::$instance = null;
    }

    /**
     * Looks up the geo coordinates of the address of an object and sets its geo coordinates.
     *
     * @param \Tx_Oelib_Interface_Geo $geoObject
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function lookUp(\Tx_Oelib_Interface_Geo $geoObject)
    {
        if ($geoObject->hasGeoError() || $geoObject->hasGeoCoordinates()) {
            return;
        }
        if (!$geoObject->hasGeoAddress()) {
            $geoObject->setGeoError();
            return;
        }

        $address = $geoObject->getGeoAddress();

        $attempts = 0;
        do {
            $this->throttle();
            $lookupError = false;

            $retry = false;
            $response = $this->sendRequest($address);

            $httpError = $response === false;
            if (!$httpError) {
                $resultParts = json_decode($response, true);
                $status = $resultParts['status'];
                $lookupError = $status !== self::STATUS_OK;
                $addressIsInvalid = \in_array($status, [self::STATUS_ZERO_RESULTS, self::STATUS_INVALID_REQUEST], true);
                if ($addressIsInvalid) {
                    break;
                }
            }

            if ($httpError || $lookupError) {
                $attempts++;
                if ($attempts < static::MAXIMUM_ATTEMPTS) {
                    $retry = true;
                }
            }
        } while ($retry);

        if ($httpError) {
            throw new \RuntimeException('There was an error connecting to the Google geocoding server.', 1331488446);
        }

        if (!$lookupError) {
            $coordinates = $resultParts['results'][0]['geometry']['location'];
            $geoObject->setGeoCoordinates(
                [
                    'latitude' => (float)$coordinates['lat'],
                    'longitude' => (float)$coordinates['lng'],
                ]
            );
        } else {
            $geoObject->setGeoError();
        }
    }

    /**
     * Sends a geocoding request to the Google Maps server.
     *
     * @param string $address the address to look up, must not be empty
     *
     * @return string|bool string with the JSON result from the Google Maps server, or false if an error has occurred
     */
    protected function sendRequest($address)
    {
        $baseUrlWithAddress = self::BASE_URL . '&address=';

        return GeneralUtility::getUrl($baseUrlWithAddress . urlencode($address));
    }

    /**
     * Makes sure the necessary amount of time has passed since the last
     * geocoding request.
     *
     * @return void
     */
    protected function throttle()
    {
        if (self::$lastGeocodingTimestamp > 0.0) {
            $secondsSinceLastRequest = (microtime(true) - self::$lastGeocodingTimestamp);
            if ($secondsSinceLastRequest < self::THROTTLING_IN_SECONDS) {
                sleep((int)ceil(self::THROTTLING_IN_SECONDS - $secondsSinceLastRequest));
            }
        }

        self::$lastGeocodingTimestamp = microtime(true);
    }
}
