<?php

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FederalStateTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Model_FederalState
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Model_FederalState();
    }

    /**
     * @test
     */
    public function isReadOnlyIsTrue()
    {
        self::assertTrue($this->subject->isReadOnly());
    }

    /**
     * @test
     */
    public function getLocalNameReturnsLocalName()
    {
        $name = 'Nordrhein-Westfalen';
        $this->subject->setData(['zn_name_local' => $name]);

        self::assertSame($name, $this->subject->getLocalName());
    }

    /**
     * @test
     */
    public function getEnglishNameReturnsEnglishName()
    {
        $name = 'North Rhine-Westphalia';
        $this->subject->setData(['zn_name_en' => $name]);

        self::assertSame($name, $this->subject->getEnglishName());
    }

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2Code()
    {
        $code = 'DE';
        $this->subject->setData(['zn_country_iso_2' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha2Code());
    }

    /**
     * @test
     */
    public function getIsoAlpha2ZoneCodeReturnsIsoAlpha2ZoneCode()
    {
        $code = 'NW';
        $this->subject->setData(['zn_code' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha2ZoneCode());
    }
}
