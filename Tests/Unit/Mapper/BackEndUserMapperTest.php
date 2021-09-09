<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Model\BackEndUser;

/**
 * @covers \OliverKlee\Oelib\Mapper\BackEndUserMapper
 */
final class BackEndUserMapperTest extends UnitTestCase
{
    /**
     * @var BackEndUserMapper
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new BackEndUserMapper();
    }

    /**
     * @test
     */
    public function isMapper()
    {
        self::assertInstanceOf(AbstractDataMapper::class, $this->subject);
    }

    /**
     * @test
     */
    public function createsBackEndUserModel()
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(BackEndUser::class, $model);
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');
        $this->expectExceptionCode(1331319892);

        $this->subject->findByUserName('');
    }
}
