<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\CurrencyMapper;
use OliverKlee\Oelib\Model\Currency;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

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
        parent::setUp();

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
