<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Model\Country;

class CountryTest extends UnitTestCase
{
    /**
     * @var Country
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new Country();
    }

    /**
     * @test
     */
    public function isReadOnlyIsTrue(): void
    {
        self::assertTrue($this->subject->isReadOnly());
    }

    /**
     * @test
     */
    public function getLocalShortNameReturnsLocalShortName(): void
    {
        $name = 'Deutschland';
        $this->subject->setData(['cn_short_local' => $name]);

        self::assertSame($name, $this->subject->getLocalShortName());
    }

    /**
     * @test
     */
    public function getIsoAlpha2CodeReturnsIsoAlpha2Code(): void
    {
        $code = 'DE';
        $this->subject->setData(['cn_iso_2' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha2Code());
    }

    /**
     * @test
     */
    public function getIsoAlpha3CodeReturnsIsoAlpha3Code(): void
    {
        $code = 'DEU';
        $this->subject->setData(['cn_iso_3' => $code]);

        self::assertSame($code, $this->subject->getIsoAlpha3Code());
    }
}
