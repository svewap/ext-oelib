<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\Interfaces\MapPoint;
use OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures\TestingMapPoint;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GoogleMapsViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var \Tx_Oelib_ViewHelpers_GoogleMapsViewHelper
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_Configuration
     */
    private $configuration = null;

    /**
     * @var MapPoint|MockObject
     */
    private $mapPointWithCoordinates = null;

    /**
     * @var TypoScriptFrontendController
     */
    private $mockFrontEnd = null;

    protected function setUp()
    {
        parent::setUp();

        $configurationRegistry = \Tx_Oelib_ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new \Tx_Oelib_Configuration());
        $this->configuration = new \Tx_Oelib_Configuration();
        $configurationRegistry->set('plugin.tx_oelib', $this->configuration);

        $this->mockFrontEnd = $this->createMock(TypoScriptFrontendController::class);
        $GLOBALS['TSFE'] = $this->mockFrontEnd;
        $this->mapPointWithCoordinates = $this->createMock(MapPoint::class);
        $this->mapPointWithCoordinates
            ->method('hasGeoCoordinates')
            ->willReturn(true);
        $this->mapPointWithCoordinates->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);

        $this->subject = new \Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
    }

    protected function tearDown()
    {
        \Tx_Oelib_ConfigurationRegistry::purgeInstance();
        parent::tearDown();
        unset($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function twoMapsAfterRenderingHaveDifferentMapIds()
    {
        $map1 = new \Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
        $map1->render([$this->mapPointWithCoordinates]);
        $map2 = new \Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
        $map2->render([$this->mapPointWithCoordinates]);

        self::assertNotSame($map1->getMapId(), $map2->getMapId());
    }

    /**
     * @test
     */
    public function renderForEmptyMapPointsReturnsEmptyString()
    {
        self::assertSame('', $this->subject->render([]));
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesReturnsEmptyString()
    {
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(false);

        self::assertSame('', $this->subject->render([$mapPoint]));
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesNotSetsAdditionalHeaderData()
    {
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(false);

        $this->subject->render([$mapPoint]);

        self::assertSame([], $this->mockFrontEnd->additionalHeaderData);
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithGeneralMapId()
    {
        self::assertContains(
            '<div id="' . \Tx_Oelib_ViewHelpers_GoogleMapsViewHelper::MAP_HTML_ID_PREFIX,
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithSpecificMapId()
    {
        $result = $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains('<div id="' . $this->subject->getMapId(), $result);
    }

    /**
     * @test
     */
    public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultWidth()
    {
        self::assertContains(
            'width: 600px;',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultHeight()
    {
        self::assertContains('height: 400px;', $this->subject->render([$this->mapPointWithCoordinates]));
    }

    /**
     * @test
     */
    public function renderWithEmptyWidthThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], '', '42px');
    }

    /**
     * @test
     */
    public function renderWithInvalidWidthThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], 'foo', '42px');
    }

    /**
     * @test
     */
    public function renderWithEmptyHeightThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], '42px', '');
    }

    /**
     * @test
     */
    public function renderWithInvalidHeightThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->render([$this->mapPointWithCoordinates], '42px', 'foo');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function renderWithWithAndHeightInPixelsNotThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42px', '91px');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function renderWithWithAndHeightInPercentNotThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42%', '91%');
    }

    /**
     * @test
     */
    public function renderReturnsStyleWithGivenWidth()
    {
        self::assertContains(
            'width: 142px;',
            $this->subject->render([$this->mapPointWithCoordinates], '142px')
        );
    }

    /**
     * @test
     */
    public function renderReturnsStyleWithGivenHeight()
    {
        self::assertContains(
            'height: 99px;',
            $this->subject->render([$this->mapPointWithCoordinates], '142px', '99px')
        );
    }

    /**
     * @test
     */
    public function renderIncludesGoogleMapsLibraryInHeader()
    {
        $apiKey = 'iugo7t4adasfdsq3ewrdsxc';
        $this->configuration->setAsString('googleMapsApiKey', $apiKey);

        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            '<script src="https://maps.googleapis.com/maps/api/js?key=' . $apiKey
            . '" type="text/javascript"></script>',
            $this->mockFrontEnd
                ->additionalHeaderData[\Tx_Oelib_ViewHelpers_GoogleMapsViewHelper::LIBRARY_JAVASCRIPT_HEADER_KEY]
        );
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptInHeader()
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertTrue(isset($this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]));
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptWithGoogleMapInitializationInHeader()
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
    public function renderReturnsInitializationCallWithMapNumber()
    {
        self::assertRegExp(
            '/initializeGoogleMap_\\d+/',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderForMapPointsOfNonMapPointClassThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $element = new \stdClass();

        $this->subject->render([$element]);
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesCreatesMapMarker()
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
    public function renderForElementWithCoordinatesCreatesMapPointCoordinates()
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
    public function renderForElementWithCoordinatesWithoutIdentityNotCreatesUidProperty()
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
    public function renderForElementWithCoordinatesWithIdentityWithoutUidNotCreatesUidProperty()
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
    public function renderForElementWithCoordinatesWithIdentityWithUidCreatesUidPropertyWithUid()
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
    public function renderForElementWithCoordinatesWithoutIdentityNotCreatesEntryInMapMarkersByUid()
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
    public function renderForElementWithCoordinatesWithIdentityWithoutUidNotCreatesEntryInMapMarkersByUid()
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
    public function renderForElementWithCoordinatesWithIdentityWithUidCreatesEntryInMapMarkersByUid()
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
    public function renderForOneElementWithCoordinatesUsesMapPointCoordinatesAsCenter()
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
    public function renderForTwoElementWithCoordinatesUsesFirstMapPointCoordinatesAsCenter()
    {
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
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
    public function renderForTwoElementsWithCoordinatesCreatesTwoMapMarkers()
    {
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
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
    public function renderForTwoElementsWithCoordinatesExtendsBoundsTwoTimes()
    {
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
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
    public function renderForTwoElementsWithCoordinatesFitsMapToBounds()
    {
        $mapPoint1 = $this->createMock(MapPoint::class);
        $mapPoint1->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint1->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
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
    public function renderForElementWithTitleCreatesTitle()
    {
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
    public function renderForElementWithTitleEscapesQuotesInTitle()
    {
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
    public function renderForElementWithTitleEscapesLinefeedsInTitle()
    {
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn('Here' . LF . 'There');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "Here\\nThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleEscapesCarriageReturnsInTitle()
    {
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasTooltipTitle')->willReturn(true);
        $mapPoint->method('getTooltipTitle')->willReturn('Here' . CR . 'There');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            'title: "Here\\rThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithTitleEscapesBackslashesInTitle()
    {
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
    public function renderForElementWithoutTitleNotCreatesTitle()
    {
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
    public function renderForElementWithInfoWindowContentCreatesInfoWindow()
    {
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
    public function renderForElementWithInfoWindowContentEscapesQuotesInInfoWindowContent()
    {
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
    public function renderForElementWithInfoWindowContentEscapesLinefeedsInInfoWindowContent()
    {
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn('Here' . LF . 'There');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            '"Here\\nThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentEscapesCarriageReturnsInInfoWindowContent()
    {
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(true);
        $mapPoint->method('getGeoCoordinates')
            ->willReturn(['latitude' => 1.2, 'longitude' => 3.4]);
        $mapPoint->method('hasInfoWindowContent')->willReturn(true);
        $mapPoint->method('getInfoWindowContent')->willReturn('Here' . CR . 'There');
        $this->subject->render([$mapPoint]);

        self::assertContains(
            '"Here\\rThere"',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithInfoWindowContentEscapesBackslashesInInfoWindowContent()
    {
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
    public function renderForElementWithoutInfoWindowContentNotCreatesInfoWindow()
    {
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
