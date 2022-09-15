<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Model\Country;

/**
 * @covers \OliverKlee\Oelib\Mapper\CountryMapper
 */
final class CountryMapperTest extends UnitTestCase
{
    /**
     * @var CountryMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CountryMapper();
    }

    /**
     * @test
     */
    public function isMapper(): void
    {
        self::assertInstanceOf(AbstractDataMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createsCountryModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(Country::class, $model);
    }
}
