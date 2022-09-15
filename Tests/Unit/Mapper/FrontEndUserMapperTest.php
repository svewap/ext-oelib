<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * @covers \OliverKlee\Oelib\Mapper\FrontEndUserMapper
 */
final class FrontEndUserMapperTest extends UnitTestCase
{
    /**
     * @var FrontEndUserMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserMapper();
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
    public function createsFrontEndUserModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(FrontEndUser::class, $model);
    }
}
