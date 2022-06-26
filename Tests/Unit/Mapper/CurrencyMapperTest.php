<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\CurrencyMapper;
use OliverKlee\Oelib\Model\Currency;

/**
 * @covers \OliverKlee\Oelib\Mapper\CurrencyMapper
 */
final class CurrencyMapperTest extends UnitTestCase
{
    /**
     * @var CurrencyMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new CurrencyMapper();
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
    public function createsCurrencyModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(Currency::class, $model);
    }
}
