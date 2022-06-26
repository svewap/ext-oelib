<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper;
use OliverKlee\Oelib\Model\BackEndUserGroup;

/**
 * @covers \OliverKlee\Oelib\Mapper\BackEndUserGroupMapper
 */
final class BackEndUserGroupMapperTest extends UnitTestCase
{
    /**
     * @var BackEndUserGroupMapper
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new BackEndUserGroupMapper();
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
    public function createsBackEndUserGroupModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(BackEndUserGroup::class, $model);
    }
}
