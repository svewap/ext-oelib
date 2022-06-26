<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FederalStateMapper;
use OliverKlee\Oelib\Model\FederalState;

/**
 * @covers \OliverKlee\Oelib\Mapper\FederalStateMapper
 */
final class FederalStateMapperTest extends UnitTestCase
{
    /**
     * @var FederalStateMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FederalStateMapper();
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
    public function createsFederalStateModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(FederalState::class, $model);
    }
}
