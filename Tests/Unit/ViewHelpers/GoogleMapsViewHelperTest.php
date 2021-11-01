<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use Nimut\TestingFramework\TestCase\ViewHelperBaseTestcase;
use OliverKlee\Oelib\Configuration\ConfigurationRegistry;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Interfaces\MapPoint;
use OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures\TestingMapPoint;
use OliverKlee\Oelib\ViewHelpers\GoogleMapsViewHelper;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface;

/**
 * @covers \OliverKlee\Oelib\ViewHelpers\GoogleMapsViewHelper;
 */
class GoogleMapsViewHelperTest extends ViewHelperBaseTestcase
{
    /**
     * @var GoogleMapsViewHelper
     */
    private $subject;

    /**
     * @var DummyConfiguration
     */
    private $configuration;

    /**
     * @var MapPoint&MockObject
     */
    private $mapPointWithCoordinates;

    /**
     * @var TypoScriptFrontendController&MockObject
     */
    private $mockFrontEnd;

    protected function setUp(): void
    {
        parent::setUp();

        $configurationRegistry = ConfigurationRegistry::getInstance();
        $configurationRegistry->set('plugin', new DummyConfiguration());
        $this->configuration = new DummyConfiguration();
        $configurationRegistry->set('plugin.tx_oelib', $this->configuration);

        /** @var TypoScriptFrontendController&MockObject $mockFrontend */
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
        $this->subject->initializeArguments();
    }

    protected function tearDown(): void
    {
        ConfigurationRegistry::purgeInstance();
        unset($GLOBALS['TSFE']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isViewHelper(): void
    {
        self::assertInstanceOf(AbstractViewHelper::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsViewHelper(): void
    {
        self::assertInstanceOf(ViewHelperInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function twoMapsAfterRenderingHaveDifferentMapIds(): void
    {
        $map1 = new GoogleMapsViewHelper();
        $map1->initializeArguments();
        $map1->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);
        $map1->render();
        $map2 = new GoogleMapsViewHelper();
        $map2->initializeArguments();
        $map2->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);
        $map2->render();

        self::assertNotSame($map1->getMapId(), $map2->getMapId());
    }

    /**
     * @test
     */
    public function renderForEmptyMapPointsReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesReturnsEmptyString(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(false);
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        self::assertSame('', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesNotSetsAdditionalHeaderData(): void
    {
        /** @var MapPoint&MockObject $mapPoint */
        $mapPoint = $this->createMock(MapPoint::class);
        $mapPoint->method('hasGeoCoordinates')->willReturn(false);

        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);
        $this->subject->render();

        self::assertSame([], $this->mockFrontEnd->additionalHeaderData);
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithGeneralMapId(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        self::assertStringContainsString('<div id="tx_oelib_map_', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithSpecificMapId(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $result = $this->subject->render();

        self::assertStringContainsString('<div id="' . $this->subject->getMapId(), $result);
    }

    /**
     * @test
     */
    public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultWidth(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        self::assertStringContainsString('width: 600px;', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderWithoutWidthAndWithoutHeightReturnsStyleWithDefaultHeight(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        self::assertStringContainsString('height: 400px;', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderWithEmptyWidthThrowsException(): void
    {
        $this->subject->setArguments(
            ['mapPoints' => [$this->mapPointWithCoordinates], 'width' => '', 'height' => '42px']
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1319058935);
        $this->expectExceptionMessage('$width must be a valid CSS length, but actually is: ');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWithInvalidWidthThrowsException(): void
    {
        $this->subject->setArguments(
            ['mapPoints' => [$this->mapPointWithCoordinates], 'width' => 'foo', 'height' => '42px']
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1319058935);
        $this->expectExceptionMessage('$width must be a valid CSS length, but actually is: foo');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWithEmptyHeightThrowsException(): void
    {
        $this->subject->setArguments(
            ['mapPoints' => [$this->mapPointWithCoordinates], 'width' => '42px', 'height' => '']
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1319058966);
        $this->expectExceptionMessage('$height must be a valid CSS length, but actually is: ');

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderWithInvalidHeightThrowsException(): void
    {
        $this->subject->setArguments(
            ['mapPoints' => [$this->mapPointWithCoordinates], 'width' => '42px', 'height' => 'foo']
        );
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1319058966);
        $this->expectExceptionMessage('$height must be a valid CSS length, but actually is: foo');

        $this->subject->render();
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function renderWithWithAndHeightInPixelsNotThrowsException(): void
    {
        $this->subject->setArguments(
            ['mapPoints' => [$this->mapPointWithCoordinates], 'width' => '42px', 'height' => '91px']
        );

        $this->subject->render();
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function renderWithWithAndHeightInPercentNotThrowsException(): void
    {
        $this->subject->setArguments(
            ['mapPoints' => [$this->mapPointWithCoordinates], 'width' => '42%', 'height' => '91%']
        );

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderReturnsStyleWithGivenWidth(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates], 'width' => '142px']);

        self::assertStringContainsString('width: 142px;', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderReturnsStyleWithGivenHeight(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates], 'height' => '99px']);

        self::assertStringContainsString('height: 99px;', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderIncludesGoogleMapsLibraryInHeader(): void
    {
        $apiKey = 'iugo7t4adasfdsq3ewrdsxc';
        $this->configuration->setAsString('googleMapsApiKey', $apiKey);
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringContainsString(
            '<script src="https://maps.googleapis.com/maps/api/js?key=' . $apiKey . '></script>',
            $this->mockFrontEnd->additionalHeaderData['tx-oelib-googleMapsLibrary']
        );
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptInHeader(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertTrue(isset($this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]));
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptWithGoogleMapInitializationInHeader(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringContainsString(
            'new google.maps.Map(',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderReturnsInitializationCallWithMapNumber(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();
        self::assertRegExp('/initializeGoogleMap_\\d+/', $this->subject->render());
    }

    /**
     * @test
     */
    public function renderForMapPointsOfNonMapPointClassThrowsException(): void
    {
        $element = new \stdClass();
        $this->subject->setArguments(['mapPoints' => [$element]]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1318093613);
        $this->expectExceptionMessage(
            'All $mapPoints need to implement OliverKlee\\Oelib\\Interfaces\\MapPoint, but stdClass does not.'
        );

        $this->subject->render();
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesCreatesMapMarker(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringContainsString(
            'new google.maps.Marker(',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesCreatesMapPointCoordinates(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringContainsString(
            'new google.maps.LatLng(1.200000, 3.400000)',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithoutIdentityNotCreatesUidProperty(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringNotContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringNotContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
            'uid: ' . $uid,
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForElementWithCoordinatesWithoutIdentityNotCreatesEntryInMapMarkersByUid(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringNotContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringNotContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
            'mapMarkersByUid.' . $this->subject->getMapId() . '[' . $uid . '] = marker_',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }

    /**
     * @test
     */
    public function renderForOneElementWithCoordinatesUsesMapPointCoordinatesAsCenter(): void
    {
        $this->subject->setArguments(['mapPoints' => [$this->mapPointWithCoordinates]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint1, $mapPoint2]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint1, $mapPoint2]]);

        $this->subject->render();

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
        $this->subject->setArguments(['mapPoints' => [$mapPoint1, $mapPoint2]]);

        $this->subject->render();

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
        $this->subject->setArguments(['mapPoints' => [$mapPoint1, $mapPoint2]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringNotContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringContainsString(
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
        $this->subject->setArguments(['mapPoints' => [$mapPoint]]);

        $this->subject->render();

        self::assertStringNotContainsString(
            'new google.maps.InfoWindow',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }
}
