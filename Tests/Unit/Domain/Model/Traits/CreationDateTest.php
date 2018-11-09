<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Model\Interfaces\CreationDate;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\CreatedModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class CreationDateTest extends UnitTestCase
{
    /**
     * @var CreatedModel
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new CreatedModel();
    }

    /**
     * @test
     */
    public function implementsCreationDate()
    {
        static::assertInstanceOf(CreationDate::class, $this->subject);
    }

    /**
     * @test
     */
    public function getCreationDateInitiallyReturnsNull()
    {
        static::assertNull($this->subject->getCreationDate());
    }

    /**
     * @test
     */
    public function setCreationDateSetsCreationDate()
    {
        $date = new \DateTime();
        $this->subject->setCreationDate($date);

        static::assertSame($date, $this->subject->getCreationDate());
    }
}
