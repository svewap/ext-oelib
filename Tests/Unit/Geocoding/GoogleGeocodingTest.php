<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Geocoding;

use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Geocoding\DummyGeocodingLookup;
use OliverKlee\Oelib\Geocoding\GoogleGeocoding;
use OliverKlee\Oelib\Tests\Unit\Geocoding\Fixtures\TestingGeo;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Geocoding\GoogleGeocoding
 */
final class GoogleGeocodingTest extends UnitTestCase
{
    /**
     * @var GoogleGeocoding
     */
    private $subject = null;

    /**
     * @var DummyConfiguration
     */
    private $configuration = null;

    protected function setUp(): void
    {
        parent::setUp();

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_oelib', $this->configuration);

        /** @var GoogleGeocoding $subject */
        $subject = GoogleGeocoding::getInstance();
        $subject->setMaximumDelay(1);
        $this->subject = $subject;
    }

    protected function tearDown(): void
    {
        GoogleGeocoding::purgeInstance();
        ConfigurationRegistry::purgeInstance();
        parent::tearDown();
    }

    // Tests for the basic functionality

    /**
     * @test
     */
    public function getInstanceCreatesGoogleMapsLookupInstance(): void
    {
        self::assertInstanceOf(GoogleGeocoding::class, GoogleGeocoding::getInstance());
    }

    /**
     * @test
     */
    public function setInstanceSetsInstance(): void
    {
        GoogleGeocoding::purgeInstance();

        $instance = new DummyGeocodingLookup();
        GoogleGeocoding::setInstance($instance);

        self::assertSame($instance, GoogleGeocoding::getInstance());
    }

    // Tests for lookUp

    /**
     * @test
     */
    public function lookUpForEmptyAddressSetsCoordinatesError(): void
    {
        $geo = $this->createPartialMock(TestingGeo::class, ['setGeoError']);
        $geo->expects(self::once())->method('setGeoError');

        $this->subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForEmptyAddressWithErrorSendsNoRequest(): void
    {
        $geo = new TestingGeo();
        $geo->setGeoError();

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithCoordinatesSendsNoRequest(): void
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoCoordinates(
            ['latitude' => 50.7335500, 'longitude' => 7.1014300]
        );

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithErrorSendsNoRequest(): void
    {
        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');
        $geo->setGeoError();

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->expects(self::never())->method('sendRequest');

        $subject->lookUp($geo);
    }

    /**
     * @return string[][]
     */
    public function noResultsStatusDataProvider(): array
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
    public function lookUpWithErrorSetsGeoProblem(string $status): void
    {
        $this->configuration->setAsString('googleGeocodingApiKey', 'iugo7t4zq3ewrdsxc');

        $jsonResult = '{ "status": "' . $status . '" }';

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->willReturn($jsonResult);

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
    public function lookUpWithErrorSetsGeoProblemAndLogsError(string $status): void
    {
        $this->configuration->setAsString('googleGeocodingApiKey', 'iugo7t4zq3ewrdsxc');

        $jsonResult = '{ "status": "' . $status . '" }';

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->willReturn($jsonResult);

        $subject->lookUp($geo);

        self::assertStringContainsString($status, $geo->getGeoErrorReason());
    }

    /**
     * @test
     *
     * @param string $status
     *
     * @dataProvider noResultsStatusDataProvider
     */
    public function lookUpWithErrorLogsErrorDetails(string $status): void
    {
        $this->configuration->setAsString('googleGeocodingApiKey', 'iugo7t4zq3ewrdsxc');

        $errorMessage = 'See you on the other side.';
        $jsonResult = '{ "status": "' . $status . '", "error_message": "' . $errorMessage . '" }';

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->willReturn($jsonResult);

        $subject->lookUp($geo);

        self::assertStringContainsString($errorMessage, $geo->getGeoErrorReason());
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressWithNetworkErrorSetsGeoProblemAndLogsError(): void
    {
        $this->configuration->setAsString('googleGeocodingApiKey', 'iugo7t4zq3ewrdsxc');

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->willReturn(false);

        $subject->lookUp($geo);

        self::assertTrue($geo->hasGeoError());
        self::assertStringContainsString('network problem', $geo->getGeoErrorReason());
    }

    /**
     * @test
     */
    public function lookUpSetsCoordinatesFromSendRequest(): void
    {
        $this->configuration->setAsString('googleGeocodingApiKey', 'iugo7t4zq3ewrdsxc');

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

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->setMaximumDelay(1);
        $subject->method('sendRequest')->willReturn($jsonResult);

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
    public function lookUpForEmptyApiKeyThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $this->configuration->setAsString('googleGeocodingApiKey', '');

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);
    }

    /**
     * @test
     */
    public function lookUpForMissingApiKeyThrowsException(): void
    {
        $this->expectException(\UnexpectedValueException::class);

        $geo = new TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function lookUpUsesApiKey(): void
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

        $geo = new TestingGeo();
        $geo->setGeoAddress($address);

        $subject = $this->getMockBuilder(GoogleGeocoding::class)->onlyMethods(['sendRequest'])
            ->disableOriginalConstructor()->getMock();
        $subject->method('sendRequest')->with($expectedUrl)->willReturn($jsonResult);

        $subject->lookUp($geo);
    }
}
