<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Model\Country;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class CountryTest extends UnitTestCase
{
    /**
     * @var Country
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new Country();
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
    public function getLocalShortNameReturnsLocalShortName()
    {
        $name = 'Deutschland';
        $this->subject->setData(['cn_short_local' => $name]);

        self::assertSame($name, $this->subject->getLocalShortName());
    }

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2Code()
    {
        $code = 'DE';
        $this->subject->setData(['cn_iso_2' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha2Code());
    }

    /**
     * @test
     */
    public function getIsoAlpha3CodeReturnsIsoAlpha3Code()
    {
        $code = 'DEU';
        $this->subject->setData(['cn_iso_3' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha3Code());
    }
}
