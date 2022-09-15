<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Model\Interfaces\ChangeDate;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\ChangedModel;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\Traits\ChangeDate
 */
final class ChangeDateTest extends UnitTestCase
{
    /**
     * @var ChangedModel
     */
    private $subject;

    protected function setUp(): void
    {
        $this->subject = new ChangedModel();
    }

    /**
     * @test
     */
    public function implementsChangeDate(): void
    {
        self::assertInstanceOf(ChangeDate::class, $this->subject);
    }

    /**
     * @test
     */
    public function getChangeDateInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getChangeDate());
    }

    /**
     * @test
     */
    public function setChangeDateSetsChangeDate(): void
    {
        $date = new \DateTimeImmutable();
        $this->subject->setChangeDate($date);

        self::assertSame($date, $this->subject->getChangeDate());
    }
}
