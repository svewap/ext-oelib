<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Geocoding;

use OliverKlee\Oelib\Interfaces\Geo;
use OliverKlee\Oelib\Interfaces\GeocodingLookup;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a service to look up geo coordinates via Google Maps.
 *
 * @see https://developers.google.com/maps/documentation/javascript/geocoding?#GeocodingStatusCodes
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GoogleGeocoding implements GeocodingLookup
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
     * @var GeocodingLookup
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
     * @return GeocodingLookup the Singleton GoogleMaps look-up
     */
    public static function getInstance(): GeocodingLookup
    {
        if (self::$instance === null) {
            self::$instance = new GoogleGeocoding();
        }

        return self::$instance;
    }

    /**
     * Sets the Singleton GoogleMaps look-up instance.
     *
     * Note: This function is to be used for testing only.
     *
     * @param GeocodingLookup $instance the instance which getInstance() should return
     *
     * @return void
     */
    public static function setInstance(GeocodingLookup $instance)
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
    public function setMaximumDelay(int $delay)
    {
        $this->maximumDelayInMicroseconds = $delay;
    }

    /**
     * Looks up the geo coordinates of the address of an object and sets its geo coordinates.
     *
     * @param Geo $geoObject
     *
     * @return void
     *
     * @throws \UnexpectedValueException if the API key is empty or not set
     */
    public function lookUp(Geo $geoObject)
    {
        if ($geoObject->hasGeoError() || $geoObject->hasGeoCoordinates()) {
            return;
        }
        if (!$geoObject->hasGeoAddress()) {
            $geoObject->setGeoError();
            return;
        }

        $apiKey = \Tx_Oelib_ConfigurationRegistry::get('plugin.tx_oelib')->getAsString('googleGeocodingApiKey');
        if ($apiKey === '') {
            throw new \UnexpectedValueException(
                'Please set the Google geocoding API key using TypoScrip setup plugin.tx_oelib.googleGeocodingApiKey',
                1550690438
            );
        }

        $address = $geoObject->getGeoAddress();
        $url = self::BASE_URL . '?key=' . \urlencode($apiKey) . '&address=' . \urlencode($address);
        $delayInMicroseconds = self::INITIAL_DELAY_IN_MICROSECONDS;

        while (true) {
            \usleep($delayInMicroseconds);
            $response = $this->sendRequest($url);
            if ($response !== false) {
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
                    $errorText = 'Error: ' . $status;
                    if (isset($resultParts['error_message'])) {
                        $errorText .= ' with additional details: ' . $resultParts['error_message'];
                    }
                    $geoObject->setGeoError($errorText);
                    break;
                }
            } else {
                $resultParts = [];
                $status = 'General network problem.';
            }

            if ($delayInMicroseconds * 2 > $this->maximumDelayInMicroseconds) {
                $errorText = 'Maximum retries reached after ' . ($delayInMicroseconds / 1000000) .
                    ' seconds delay. Last status: ' . $status;
                if (isset($resultParts['error_message'])) {
                    $errorText .= ' with additional details: ' . $resultParts['error_message'];
                }
                $geoObject->setGeoError($errorText);
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
    protected function sendRequest(string $url)
    {
        return GeneralUtility::getUrl($url);
    }
}
