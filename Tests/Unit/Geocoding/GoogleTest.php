<?php
namespace OliverKlee\Oelib\Tests\Unit\Geocoding;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GoogleTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_Geocoding_Google
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    protected function setUp()
    {
        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
        $configurationRegistry->set('plugin.tx_oelib', $this->configuration);

        $this->subject = \Tx_Oelib_Geocoding_Google::getInstance();
        $this->subject->setMaximumDelay(1);
    }

    protected function tearDown()
    {
        \Tx_Oelib_Geocoding_Google::purgeInstance();
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();
    }

    /*
     * Tests for the basic functionality
     */

    /**
     * @test
     */
    public function getInstanceCreatesGoogleMapsLookupInstance()
    {
        self::assertInstanceOf(\Tx_Oelib_Geocoding_Google::class, \Tx_Oelib_Geocoding_Google::getInstance());
    }

    /**
     * @test
     */
    public function setInstanceSetsInstance()
    {
        \Tx_Oelib_Geocoding_Google::purgeInstance();

        $instance = new \Tx_Oelib_Geocoding_Dummy();
        \Tx_Oelib_Geocoding_Google::setInstance($instance);

        self::assertSame($instance, \Tx_Oelib_Geocoding_Google::getInstance());
    }

    /*
     * Tests for lookUp
     */

    /**
     * @test
     */
    public function lookUpForEmptyAddressSetsCoordinatesError()
    {
        $geo = $this->getMock(\Tx_Oelib_Tests_Unit_Fixtures_TestingGeo::class, ['setGeoError']);
        $geo->expects(self::once())->method('setGeoError');

        /* @var \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo $geo */
        $this->subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForEmptyAddressWithErrorSendsNoRequest()
    {
        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoError();

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithCoordinatesSendsNoRequest()
    {
        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoCoordinates(
            ['latitude' => 50.7335500, 'longitude' => 7.1014300]
        );

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithErrorSendsNoRequest()
    {
        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoError();

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @return string[][]
     */
    public function noResultsStatusDataProvider()
    {
        return [
            'zero results' => ['ZERO_RESULTS'],
            'over daily limit' => ['OVER_DAILY_LIMIT'],
            'over query limit' => ['OVER_QUERY_LIMIT'],
            'request denied' => ['REQUEST_DENIED'],
            'invalid request' => ['INVALID_REQUEST'],
            'unknown error' => ['UNKNOWN_ERROR'],
        ];
    }

    /**
     * @test
     *
     * @param string $status
     *
     * @dataProvider noResultsStatusDataProvider
     */
    public function lookUpWithErrorSetsGeoProblem($status)
    {
        $jsonResult = '{ "status": "' . $status . '" }';

        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->will(self::returnValue($jsonResult));

        $subject->lookUp($geo);

        self::assertTrue($geo->hasGeoError());
    }

    /**
     * @test
     *
     * @param string $status
     *
     * @dataProvider noResultsStatusDataProvider
     */
    public function lookUpWithErrorSetsGeoProblemAndLogsError($status)
    {
        $jsonResult = '{ "status": "' . $status . '" }';

        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->will(self::returnValue($jsonResult));

        $subject->lookUp($geo);

        self::assertContains($status, $geo->getGeoErrorReason());
    }

    /**
     * @test
     *
     * @param string $status
     *
     * @dataProvider noResultsStatusDataProvider
     */
    public function lookUpWithErrorLogsErrorDetails($status)
    {
        $errorMessage = 'See you on the other side.';
        $jsonResult = '{ "status": "' . $status . '", "error_message": "' . $errorMessage . '" }';

        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->will(self::returnValue($jsonResult));

        $subject->lookUp($geo);

        self::assertContains($errorMessage, $geo->getGeoErrorReason());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithNetworkErrorSetsGeoProblemAndLogsError()
    {
        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->will(self::returnValue(false));

        $subject->lookUp($geo);

        self::assertTrue($geo->hasGeoError());
        self::assertContains('network problem', $geo->getGeoErrorReason());
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

        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        /** @var \Tx_Oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            \Tx_Oelib_Geocoding_Google::class,
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->will(self::returnValue($jsonResult));

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
    public function lookUpUsesApiKey()
    {
        $apiKey = 'iugo7t4zq3ewrdsxc';
        $this->configuration->setAsString('googleGeocodingApiKey', $apiKey);

        $address = 'Am Hof 1, 53113 Zentrum, Bonn, DE';
        $expectedUrl = 'https://maps.googleapis.com/maps/api/geocode/json' .
            '?key=' . $apiKey .
            '&address=' . \urlencode($address);

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

        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress($address);

        /** @var \tx_oelib_Geocoding_Google|\PHPUnit_Framework_MockObject_MockObject $subject */
        $subject = $this->getMock(
            'tx_oelib_Geocoding_Google',
            ['sendRequest'],
            [],
            '',
            false
        );
        $subject->method('sendRequest')->with($expectedUrl)->will(self::returnValue($jsonResult));

        $subject->lookUp($geo);
    }
}
