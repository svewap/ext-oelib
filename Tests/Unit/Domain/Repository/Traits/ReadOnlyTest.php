<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\EmptyModel;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\ReadOnlyRepository;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

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
        /** @var ObjectManagerInterface|ProphecySubjectInterface $objectManagerStub */
        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new ReadOnlyRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function addThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->add(new EmptyModel());
    }

    /**
     * @test
     */
    public function removeThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->remove(new EmptyModel());
    }

    /**
     * @test
     */
    public function updateThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->update(new EmptyModel());
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
