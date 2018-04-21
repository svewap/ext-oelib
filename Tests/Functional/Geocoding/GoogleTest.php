<?php
namespace OliverKlee\Oelib\Tests\Functional\Geocoding;

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

    protected function setUp()
    {
        $this->subject = \Tx_Oelib_Geocoding_Google::getInstance();
    }

    protected function tearDown()
    {
        \Tx_Oelib_Geocoding_Google::purgeInstance();
    }

    /**
     * @test
     */
    public function lookUpForAFullGermanAddressSetsCoordinatesOfAddress()
    {
        $geo = new \Tx_Oelib_Tests_Unit_Fixtures_TestingGeo();
        $geo->setGeoAddress('Am Hof 1, 53113 Zentrum, Bonn, DE');

        $this->subject->lookUp($geo);
        $coordinates = $geo->getGeoCoordinates();

        self::assertEquals(50.7335500, $coordinates['latitude'], '', 0.1);
        self::assertEquals(7.1014300, $coordinates['longitude'], '', 0.1);
    }
}
