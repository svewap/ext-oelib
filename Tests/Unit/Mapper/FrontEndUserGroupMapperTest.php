<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Oelib\Model\FrontEndUserGroup;

/**
 * @covers \OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper
 */
final class FrontEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var FrontEndUserGroupMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new FrontEndUserGroupMapper();
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
    public function createsFrontEndUserGroupModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(FrontEndUserGroup::class, $model);
    }
}
