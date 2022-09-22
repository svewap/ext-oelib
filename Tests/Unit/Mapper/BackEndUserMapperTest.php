<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use OliverKlee\Oelib\Mapper\AbstractDataMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Model\BackEndUser;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\BackEndUserMapper
 */
final class BackEndUserMapperTest extends UnitTestCase
{
    /**
     * @var BackEndUserMapper
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new BackEndUserMapper();
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
    public function createsBackEndUserModel(): void
    {
        $model = $this->subject->getNewGhost();

        self::assertInstanceOf(BackEndUser::class, $model);
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');
        $this->expectExceptionCode(1331319892);

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findByUserName('');
    }
}
