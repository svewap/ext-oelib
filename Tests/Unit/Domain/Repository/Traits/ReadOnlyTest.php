<?php

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\ReadOnlyRepository;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ReadOnlyTest extends UnitTestCase
{
    /**
     * @var ReadOnlyRepository
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new ReadOnlyRepository();
    }

    /**
     * @test
     */
    public function addThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->add(new ReadOnlyRepository());
    }

    /**
     * @test
     */
    public function removeThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->remove(new ReadOnlyRepository());
    }

    /**
     * @test
     */
    public function updateThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->update(new ReadOnlyRepository());
    }

    /**
     * @test
     */
    public function removeAllThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->removeAll();
    }
}
