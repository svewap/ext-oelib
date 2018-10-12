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
     * @var string[]
     */
    protected static $statusCodesForRetry = ['OVER_QUERY_LIMIT', 'UNKNOWN_ERROR'];

    /**
     * the base URL of the Google Maps geo coding service
     *
     * @var string
     */
    const BASE_URL = 'https://maps.googleapis.com/maps/api/geocode/json';

    /**
     * the Singleton instance
     *
     * @var \Tx_Oelib_Interface_GeocodingLookup
     */
    private static $instance = null;

    /**
     * the amount of time (in microseconds) that need to pass between subsequent geocoding requests
     *
     * @see https://developers.google.com/maps/documentation/geocoding/web-service-best-practices
     *
     * @var int
     */
    const INITIAL_DELAY_IN_MICROSECONDS = 100000;

    /**
     * 120 seconds
     *
     * @var int
     */
    private $maximumDelayInMicroseconds = 120000000;

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
     * Sets the maximum delay.
     *
     * @param int $delay
     *
     * @return void
     */
    public function setMaximumDelay($delay)
    {
        $this->maximumDelayInMicroseconds = $delay;
    }

    /**
     * Looks up the geo coordinates of the address of an object and sets its geo coordinates.
     *
     * @param \Tx_Oelib_Interface_Geo $geoObject
     *
     * @return void
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
        $configuration = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib');
        $apiKey = $configuration->getAsString('googleGeocodingApiKey');
        $url = self::BASE_URL . '?key=' . \urlencode($apiKey) . '&address=' . \urlencode($address);
        $delayInMicroseconds = self::INITIAL_DELAY_IN_MICROSECONDS;

        while (true) {
            \usleep($delayInMicroseconds);
            $response = $this->sendRequest($url);
            if ($response === false) {
                $status = 'General network problem.';
            } else {
                $resultParts = \json_decode($response, true);
                $status = $resultParts['status'];
                if ($status === self::STATUS_OK) {
                    $coordinates = $resultParts['results'][0]['geometry']['location'];
                    $geoObject->setGeoCoordinates(
                        [
                            'latitude' => (float)$coordinates['lat'],
                            'longitude' => (float)$coordinates['lng'],
                        ]
                    );
                    break;
                }
                if (!\in_array($status, static::$statusCodesForRetry, true)) {
                    $geoObject->setGeoError('Error: ' . $status);
                    break;
                }
            }

            if ($delayInMicroseconds * 2 > $this->maximumDelayInMicroseconds) {
                $geoObject->setGeoError(
                    'Maximum retries reached after ' . ($delayInMicroseconds / 1000000) .
                    ' seconds delay. Last status: ' . $status
                );
                break;
            }
            $delayInMicroseconds *= 2;
        }
    }

    /**
     * Sends a geocoding request to the Google Maps server.
     *
     * @param string $url
     *
     * @return string|bool string with the JSON result from the Google Maps server, or false if an error has occurred
     */
    protected function sendRequest($url)
    {
        return GeneralUtility::getUrl($url);
    }
}
