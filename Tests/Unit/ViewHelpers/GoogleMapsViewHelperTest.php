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
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Testcase for the Tx_Oelib_ViewHelpers_GoogleMapsViewHelper class.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ViewHelpers_GoogleMapsViewHelperTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_ViewHelpers_GoogleMapsViewHelper
     */
    private $subject = null;

    /**
     * @var Tx_Oelib_Interface_MapPoint|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mapPointWithCoordinates = null;

    /**
     * @var TypoScriptFrontendController
     */
    private $mockFrontEnd = null;

    protected function setUp()
    {
        $this->mockFrontEnd = $this->getMock(TypoScriptFrontendController::class, ['dummy'], [], '', false);
        $GLOBALS['TSFE'] = $this->mockFrontEnd;
        $this->mapPointWithCoordinates = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $this->mapPointWithCoordinates->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $this->mapPointWithCoordinates->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));

        $this->subject = new Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
    }

    protected function tearDown()
    {
        unset($GLOBALS['TSFE']);
    }

    /**
     * @test
     */
    public function twoMapsAfterRenderingHaveDifferentMapIds()
    {
        $map1 = new Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
        $map1->render([$this->mapPointWithCoordinates]);
        $map2 = new Tx_Oelib_ViewHelpers_GoogleMapsViewHelper();
        $map2->render([$this->mapPointWithCoordinates]);

        self::assertNotSame(
            $map1->getMapId(),
            $map2->getMapId()
        );
    }

    /**
     * @test
     */
    public function renderForEmptyMapPointsReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->render([])
        );
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesReturnsEmptyString()
    {
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(false));

        self::assertSame(
            '',
            $this->subject->render([$mapPoint])
        );
    }

    /**
     * @test
     */
    public function renderForElementWithoutCoordinatesNotSetsAdditionalHeaderData()
    {
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(false));

        $this->subject->render([$mapPoint]);

        self::assertSame(
            [],
            $this->mockFrontEnd->additionalHeaderData
        );
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithGeneralMapId()
    {
        self::assertContains(
            '<div id="' . Tx_Oelib_ViewHelpers_GoogleMapsViewHelper::MAP_HTML_ID_PREFIX,
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     */
    public function renderReturnsDivWithIdWithSpecificMapId()
    {
        $result = $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            '<div id="' . $this->subject->getMapId(),
            $result
        );
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
        self::assertContains(
            'height: 400px;',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function renderWithEmptyWidthThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], '', '42px');
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function renderWithInvalidWidthThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], 'foo', '42px');
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function renderWithEmptyHeightThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42px', '');
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function renderWithInvalidHeightThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42px', 'foo');
    }

    /**
     * @test
     */
    public function renderWithWithAndHeightInPixelsNotThrowsException()
    {
        $this->subject->render([$this->mapPointWithCoordinates], '42px', '91px');
    }

    /**
     * @test
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
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertContains(
            '<script src="https://maps.google.com/maps/api/js?sensor=false" type="text/javascript"></script>',
            $this->mockFrontEnd->additionalHeaderData[Tx_Oelib_ViewHelpers_GoogleMapsViewHelper::LIBRARY_JAVASCRIPT_HEADER_KEY]
        );
    }

    /**
     * @test
     */
    public function renderIncludesJavaScriptInHeader()
    {
        $this->subject->render([$this->mapPointWithCoordinates]);

        self::assertTrue(
            isset($this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()])
        );
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
            '/initializeGoogleMap_\d+/',
            $this->subject->render([$this->mapPointWithCoordinates])
        );
    }

    /**
     * @test
     *
     * @expectedException InvalidArgumentException
     */
    public function renderForMapPointsOfNonMapPointClassThrowsException()
    {
        $element = new stdClass();

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
        $mapPoint = new Tx_Oelib_Tests_Unit_Fixtures_TestingMapPoint();
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
        $mapPoint = new Tx_Oelib_Tests_Unit_Fixtures_TestingMapPoint();
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
        $mapPoint = new Tx_Oelib_Tests_Unit_Fixtures_TestingMapPoint();
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
        $mapPoint = new Tx_Oelib_Tests_Unit_Fixtures_TestingMapPoint();
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
        $mapPoint1 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint1->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint1->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint2 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint2->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint2->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 5.6, 'longitude' => 7.8]));

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
        $mapPoint1 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint1->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint1->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint2 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint2->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint2->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 5.6, 'longitude' => 7.8]));

        $this->subject->render([$mapPoint1, $mapPoint2]);

        self::assertSame(
            2,
            substr_count(
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
        $mapPoint1 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint1->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint1->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint2 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint2->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint2->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 5.6, 'longitude' => 7.8]));
        $this->subject->render([$mapPoint1, $mapPoint2]);

        self::assertSame(
            2,
            substr_count(
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
        $mapPoint1 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint1->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint1->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint2 = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint2->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint2->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 5.6, 'longitude' => 7.8]));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasTooltipTitle')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getTooltipTitle')->will(self::returnValue('Hello world!'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasTooltipTitle')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getTooltipTitle')->will(self::returnValue('The "B" side'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasTooltipTitle')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getTooltipTitle')->will(self::returnValue('Here' . LF . 'There'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasTooltipTitle')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getTooltipTitle')->will(self::returnValue('Here' . CR . 'There'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasTooltipTitle')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getTooltipTitle')->will(self::returnValue('Here\\There'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasTooltipTitle')->will(self::returnValue(false));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasInfoWindowContent')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getInfoWindowContent')->will(self::returnValue('Hello world!'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasInfoWindowContent')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getInfoWindowContent')->will(self::returnValue('The "B" side'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasInfoWindowContent')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getInfoWindowContent')->will(self::returnValue('Here' . LF . 'There'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasInfoWindowContent')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getInfoWindowContent')->will(self::returnValue('Here' . CR . 'There'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasInfoWindowContent')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getInfoWindowContent')->will(self::returnValue('Here\\There'));
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
        $mapPoint = $this->getMock(Tx_Oelib_Interface_MapPoint::class);
        $mapPoint->expects(self::any())->method('hasGeoCoordinates')->will(self::returnValue(true));
        $mapPoint->expects(self::any())->method('getGeoCoordinates')
            ->will(self::returnValue(['latitude' => 1.2, 'longitude' => 3.4]));
        $mapPoint->expects(self::any())->method('hasInfoWindowContent')->will(self::returnValue(false));
        $this->subject->render([$mapPoint]);

        self::assertNotContains(
            'new google.maps.InfoWindow',
            $this->mockFrontEnd->additionalJavaScript[$this->subject->getMapId()]
        );
    }
}
