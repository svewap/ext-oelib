<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Model\Interfaces\ChangeDate;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\ChangedModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ChangeDateTest extends UnitTestCase
{
    /**
     * @var ChangedModel
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new ChangedModel();
    }

    /**
     * @test
     */
    public function implementsChangeDate()
    {
        static::assertInstanceOf(ChangeDate::class, $this->subject);
    }

    /**
     * @test
     */
    public function getChangeDateInitiallyReturnsNull()
    {
        static::assertNull($this->subject->getChangeDate());
    }

    /**
     * @test
     */
    public function setChangeDateSetsChangeDate()
    {
        $date = new \DateTime();
        $this->subject->setChangeDate($date);

        static::assertSame($date, $this->subject->getChangeDate());
    }
}
