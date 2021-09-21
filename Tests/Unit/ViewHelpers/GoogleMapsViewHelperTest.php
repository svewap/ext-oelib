<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\Interfaces\MapPoint;
use OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures\TestingMapPoint;
use OliverKlee\Oelib\ViewHelpers\GoogleMapsViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class GoogleMapsViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var GoogleMapsViewHelper
     */
    private $subject = null;

    /**
     * @var TypoScriptConfiguration
     */
    private $configuration = null;

    /**
     * @var MapPoint&MockObject
     */
    private $mapPointWithCoordinates = null;

    /**
     * @var TypoScriptFrontendController&MockObject
     */
    private $mockFrontEnd = null;

    protected function setUp(): void
    {
        parent::setUp();

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new TypoScriptConfiguration());
        $this->configuration = new TypoScriptConfiguration();
        $configurationRegistry->set('plugin.tx_oelib', $this->configuration);

        /** @var TypoScriptFrontendController&MockObject  $mockFrontend */
        $mockFrontend = $this->createMock(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $mockFrontend;
        $this->mockFrontEnd = $mockFrontend;
        /** @var MapPoint&MockObject $mapPointWithCoordinates */
        $mapPointWithCoordinates = $this->createMock(MapPoint::class);
        $mapPointWithCoordinates
            ->method('hasGeoCoordinates')
            ->willReturn(true);
        $mapPointWithCoordinates->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $this->mapPointWithCoordinates = $mapPointWithCoordinates;

        $this->subject = new GoogleMapsViewHelper();
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
        parent::tearDown();
        unset($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function twoMapsAfterRenderingHaveDifferentMapIds(): void
    {
        $map1 = new GoogleMapsViewHelper();
        $map1->render([$this->mapPointWithCoordinates]);
        $map2 = new GoogleMapsViewHelper();
        $map2->render([$this->mapPointWithCoordinates]);

        self::assertNotSame($map1->getMapId(), $map2->getMapId());
    }

    /**
     * @test
     */
    public function renderForEmptyMapPointsReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->render([]));
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesReturnsEmptyString(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(false);

        self::assertSame('', $this->subject->render([$mapPoint]));
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesNotSetsAdditionalHeaderData(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(false);

        $this->subject->render([$mapPoint]);

        self::assertSame([], $this->mockFrontEnd->additionalHeaderData);
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithGeneralMapId(): void
    {
        self::assertStringContainsString(
            '<div id="tx_oelib_map_',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithSpecificMapId(): void
    {
        $result = $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertStringContainsString('<div id="' . $this->subject->getMapId(), $result);
    }

    /**
     * @test
     */
    public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultWidth(): void
    {
        self::assertStringContainsString(
            'width: 600px;',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultHeight(): void
    {
        self::assertStringContainsString('height: 400px;', $this->subject->render([$this->mapPointWithCoordinates]));
    }

    /**
     * @test
     */
    public function renderWithEmptyWidthThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], '', '42px');
    }

    /**
     * @test
     */
    public function renderWithInvalidWidthThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], 'foo', '42px');
    }

    /**
     * @test
     */
    public function renderWithEmptyHeightThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], '42px', '');
    }

    /**
     * @test
     */
    public function renderWithInvalidHeightThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], '42px', 'foo');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function renderWithWithAndHeightInPixelsNotThrowsException(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42px', '91px');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function renderWithWithAndHeightInPercentNotThrowsException(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42%', '91%');
    }

    /**
     * @test
     */
    public function renderReturnsStyleWithGivenWidth(): void
    {
        self::assertStringContainsString(
            'width: 142px;',
            $this->subject->render([$this->mapPointWithCoordinates], '142px')
        );
    }

    /**
     * @test
     */
    public function renderReturnsStyleWithGivenHeight(): void
    {
        self::assertStringContainsString(
            'height: 99px;',
            $this->subject->render([$this->mapPointWithCoordinates], '142px', '99px')
        );
    }

    /**
     * @test
     */
    public function renderIncludesGoogleMapsLibraryInHeader(): void
    {
        $apiKey = 'iugo7t4adasfdsq3ewrdsxc';
        $this->configuration->setAsString('googleMapsApiKey', $apiKey);

        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            '<script src="https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '></script>',
            $this->mockFrontEnd->additionalHeaderData['tx-oelib-googleMapsLibrary']
        );
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptInHeader(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertTrue(isset($this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]));
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptWithGoogleMapInitializationInHeader(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            'new google.maps.Map(',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderReturnsInitializationCallWithMapNumber(): void
    {
        self::assertRegExp(
            '/initializeGoogleMap_\\d+/',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderForMapPointsOfNonMapPointClassThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $element = new \stdClass();

        // @phpstan-ignore-next-line We explicitly check for contract violations here.
        $this->subject->render([$element]);
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesCreatesMapMarker(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            'new google.maps.Marker(',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesCreatesMapPointCoordinates(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            'new google.maps.LatLng(1.200000, 3.400000)',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithoutIdentityNotCreatesUidProperty(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertNotContains(
            'uid:',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithIdentityWithoutUidNotCreatesUidProperty(): void
    {
        $mapPoint = new TestingMapPoint();
        $mapPoint->setUid(0);
        $this->subject->render([$mapPoint]);

        self::assertNotContains(
            'uid:',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithIdentityWithUidCreatesUidPropertyWithUid(): void
    {
        $uid = 42;
        $mapPoint = new TestingMapPoint();
        $mapPoint->setUid($uid);
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'uid: ' . $uid,
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithoutIdentityNotCreatesEntryInMapMarkersByUid(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertNotContains(
            'mapMarkersByUid.' . $this->subject->getMapId() . '[',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithIdentityWithoutUidNotCreatesEntryInMapMarkersByUid(): void
    {
        $mapPoint = new TestingMapPoint();
        $mapPoint->setUid(0);
        $this->subject->render([$mapPoint]);

        self::assertNotContains(
            'mapMarkersByUid.' . $this->subject->getMapId() . '[',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithIdentityWithUidCreatesEntryInMapMarkersByUid(): void
    {
        $uid = 42;
        $mapPoint = new TestingMapPoint();
        $mapPoint->setUid($uid);
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'mapMarkersByUid.' . $this->subject->getMapId() . '[' . $uid . '] = marker_',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForOneElementWithCoordinatesUsesMapPointCoordinatesAsCenter(): void
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            'var center = new google.maps.LatLng(1.200000, 3.400000);',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForTwoElementWithCoordinatesUsesFirstMapPointCoordinatesAsCenter(): void
    {
        /** @var MapPoint&MockObject $mapPoint1 */
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        /** @var MapPoint&MockObject $mapPoint2 */
        $mapPoint2 = $this->createMock(MapPoint::class);
        $mapPoint2->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint2->method('getGeoCoordinates')
            ->willReturn(['latitude' => 5.6, 'longitude' => 7.8]);

        $this->subject->render([$mapPoint1, $mapPoint2]);

        self::assertContains(
            'var center = new google.maps.LatLng(1.200000, 3.400000);',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForTwoElementsWithCoordinatesCreatesTwoMapMarkers(): void
    {
        /** @var MapPoint&MockObject $mapPoint1 */
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        /** @var MapPoint&MockObject $mapPoint2 */
        $mapPoint2 = $this->createMock(MapPoint::class);
        $mapPoint2->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint2->method('getGeoCoordinates')
            ->willReturn(['latitude' => 5.6, 'longitude' => 7.8]);

        $this->subject->render([$mapPoint1, $mapPoint2]);

        self::assertSame(
            2,
            \substr_count(
                $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()],
                'new google.maps.Marker('
            )
        );
    }

    /**
     * @test
     */
    public function renderForTwoElementsWithCoordinatesExtendsBoundsTwoTimes(): void
    {
        /** @var MapPoint&MockObject $mapPoint1 */
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        /** @var MapPoint&MockObject $mapPoint2 */
        $mapPoint2 = $this->createMock(MapPoint::class);
        $mapPoint2->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint2->method('getGeoCoordinates')
            ->willReturn(['latitude' => 5.6, 'longitude' => 7.8]);
        $this->subject->render([$mapPoint1, $mapPoint2]);

        self::assertSame(
            2,
            \substr_count(
                $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()],
                'bounds.extend('
            )
        );
    }

    /**
     * @test
     */
    public function renderForTwoElementsWithCoordinatesFitsMapToBounds(): void
    {
        /** @var MapPoint&MockObject $mapPoint1 */
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        /** @var MapPoint&MockObject $mapPoint2 */
        $mapPoint2 = $this->createMock(MapPoint::class);
        $mapPoint2->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint2->method('getGeoCoordinates')
            ->willReturn(['latitude' => 5.6, 'longitude' => 7.8]);
        $this->subject->render([$mapPoint1, $mapPoint2]);

        self::assertContains(
            'map.fitBounds(',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleCreatesTitle(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn('Hello world!');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "Hello world!"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleEscapesQuotesInTitle(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn('The "B" side');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "The \\"B\\" side"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleEscapesLinefeedInTitle(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn("Here\nThere");
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "Here\\nThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleEscapesCarriageReturnsInTitle(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn("Here\rThere");
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "Here\\rThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleEscapesBackslashesInTitle(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn('Here\\There');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "Here\\\\There"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithoutTitleNotCreatesTitle(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(false);
        $this->subject->render([$mapPoint]);

        self::assertNotContains(
            'title: ',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentCreatesInfoWindow(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn('Hello world!');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'new google.maps.InfoWindow',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentEscapesQuotesInInfoWindowContent(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn('The "B" side');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            '"The \\"B\\" side"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentEscapesLinefeedInInfoWindowContent(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn("Here\nThere");
        $this->subject->render([$mapPoint]);

        self::assertContains(
            '"Here\\nThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentEscapesCarriageReturnInInfoWindowContent(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn("Here\rThere");
        $this->subject->render([$mapPoint]);

        self::assertContains(
            '"Here\\rThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentEscapesBackslashesInInfoWindowContent(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn('Here\\There');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            '"Here\\\\There"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithoutInfoWindowContentNotCreatesInfoWindow(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(false);
        $this->subject->render([$mapPoint]);

        self::assertNotContains(
            'new google.maps.InfoWindow',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }
}
