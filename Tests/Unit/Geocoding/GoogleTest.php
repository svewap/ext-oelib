<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_Geocoding_GoogleTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_Geocoding_Google
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = Tx_Oelib_Geocoding_Google::getInstance();
    }

    protected function tearDown()
    {
        Tx_Oelib_Geocoding_Google::purgeInstance();
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceCreatesGoogleMapsLookupInstance()
    {
        self::assertInstanceOf(
            \Tx_Oelib_Geocoding_Google::class,
            Tx_Oelib_Geocoding_Google::getInstance()
        );
    }

    /**
     * @test
     */
    public function setInstanceSetsInstance()
    {
        Tx_Oelib_Geocoding_Google::purgeInstance();

        $instance = new Tx_Oelib_Geocoding_Dummy();
        Tx_Oelib_Geocoding_Google::setInstance($instance);

        self::assertSame(
            $instance,
            Tx_Oelib_Geocoding_Google::getInstance()
        );
    }

    /////////////////////
    // Tests for lookUp
    /////////////////////

    /**
     * @test
     */
    public function lookUpForEmptyAddressSetsCoordinatesError()
    {
        $geo = $this->getMock(
            \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo::class,
            ['setGeoError']
        );
        $geo->expects(self::once())->method('setGeoError');

        /* @var Tx_Oelib_Tests_Unit_Fixtures_TestingGeo $geo */
        $this->subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForEmptyAddressWithErrorSendsNoRequest()
    {
        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoError();

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest', 'throttle'],
            [],
            '',
            false
        );
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressSetsCoordinatesOfAddress()
    {
        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);
        $coordinates = $geo->getGeoCoordinates();

        self::assertEquals(
            50.7335500,
            $coordinates['latitude'],
            '',
            0.1
        );
        self::assertEquals(
            7.1014300,
            $coordinates['longitude'],
            '',
            0.1
        );
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithCoordinatesSendsNoRequest()
    {
        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoCoordinates(
            ['latitude' => 50.7335500, 'longitude' => 7.1014300]
        );

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest', 'throttle'],
            [],
            '',
            false
        );
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithErrorSendsNoRequest()
    {
        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoError();

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest', 'throttle'],
            [],
            '',
            false
        );
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithNoCoordinatesFoundSetsGeoProblem()
    {
        $jsonResult = '{ "status": "ZERO_RESULTS"}';

        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest', 'throttle'],
            [],
            '',
            false
        );
        $response = new \HTTP_Request2_Response('HTTP/1.1 200 OK');
        $response->appendBody($jsonResult);
        $subject->expects(self::any())->method('sendRequest')->will(self::returnValue($response));

        $subject->lookUp($geo);

        self::assertTrue($geo->hasGeoError());
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function lookUpForAFullGermanAddressWithNetworkErrorThrowsException()
    {
        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest', 'throttle'],
            [],
            '',
            false
        );
        $response = new \HTTP_Request2_Response('HTTP/1.1 500 Internal Server Error');
        $subject->expects(self::any())->method('sendRequest')->will(self::returnValue($response));

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpSetsCoordinatesFromSendRequest()
    {
        $jsonResult = '{ "results": [ { "address_components": [ { "long_name": "1", "short_name": "1", ' .
            '"types": [ "street_number" ] }, { "long_name": "Am Hof", "short_name": "Am Hof", ' .
            '"types": [ "route" ] }, { "long_name": "Bonn", "short_name": "Bonn", ' .
            '"types": [ "sublocality", "political" ] }, { "long_name": "Bonn", "short_name": "Bonn", ' .
            '"types": [ "locality", "political" ] }, { "long_name": "Bonn", "short_name": "BN", ' .
            '"types": [ "administrative_area_level_2", "political" ] }, { "long_name": "Nordrhein-Westfalen", ' .
            '"short_name": "Nordrhein-Westfalen", "types": [ "administrative_area_level_1", "political" ] }, ' .
            '{ "long_name": "Germany", "short_name": "DE", "types": [ "country", "political" ] }, ' .
            '{ "long_name": "53113", "short_name": "53113", "types": [ "postal_code" ] } ], ' .
            '"formatted_address": "Am Hof 1, 53113 Bonn, Germany", "geometry": { "location": ' .
            '{ "lat": 50.733550, "lng": 7.101430 }, "location_type": "ROOFTOP", ' .
            '"viewport": { "northeast": { "lat": 50.73489898029150, "lng": 7.102778980291502 }, ' .
            '"southwest": { "lat": 50.73220101970850, "lng": 7.100081019708497 } } }, ' .
            '"types": [ "street_address" ] } ], "status": "OK"}';

        $geo = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest', 'throttle'],
            [],
            '',
            false
        );
        $response = new \HTTP_Request2_Response('HTTP/1.1 200 OK');
        $response->appendBody($jsonResult);
        $subject->expects(self::any())->method('sendRequest')->will(self::returnValue($response));

        $subject->lookUp($geo);

        self::assertSame(
            [
                'latitude' => 50.7335500,
                'longitude' => 7.1014300,
            ],
            $geo->getGeoCoordinates()
        );
    }

    /**
     * @test
     */
    public function lookUpThrottlesRequestsByAtLeastOneSecond()
    {
        $jsonResult = '{ "results": [ { "address_components": [ { "long_name": "1", "short_name": "1", ' .
            '"types": [ "street_number" ] }, { "long_name": "Am Hof", "short_name": "Am Hof", ' .
            '"types": [ "route" ] }, { "long_name": "Bonn", "short_name": "Bonn", ' .
            '"types": [ "sublocality", "political" ] }, { "long_name": "Bonn", "short_name": "Bonn", ' .
            '"types": [ "locality", "political" ] }, { "long_name": "Bonn", "short_name": "BN", ' .
            '"types": [ "administrative_area_level_2", "political" ] }, { "long_name": "Nordrhein-Westfalen", ' .
            '"short_name": "Nordrhein-Westfalen", "types": [ "administrative_area_level_1", "political" ] }, ' .
            '{ "long_name": "Germany", "short_name": "DE", "types": [ "country", "political" ] }, ' .
            '{ "long_name": "53113", "short_name": "53113", "types": [ "postal_code" ] } ], ' .
            '"formatted_address": "Am Hof 1, 53113 Bonn, Germany", "geometry": { "location": ' .
            '{ "lat": 50.733550, "lng": 7.101430 }, "location_type": "ROOFTOP", ' .
            '"viewport": { "northeast": { "lat": 50.73489898029150, "lng": 7.102778980291502 }, ' .
            '"southwest": { "lat": 50.73220101970850, "lng": 7.100081019708497 } } }, ' .
            '"types": [ "street_address" ] } ], "status": "OK"}';

        $geo1 = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo1->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo2 = new Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo2->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var Tx_Oelib_Geocoding_Google|PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $response = new \HTTP_Request2_Response('HTTP/1.1 200 OK');
        $response->appendBody($jsonResult);
        $subject->expects(self::any())->method('sendRequest')->will(self::returnValue($response));

        $startTime = microtime(true);
        $subject->lookUp($geo1);
        $subject->lookUp($geo2);
        $endTime = microtime(true);

        $timePassed = $endTime - $startTime;
        self::assertGreaterThan(1.0, $timePassed);
    }
}
