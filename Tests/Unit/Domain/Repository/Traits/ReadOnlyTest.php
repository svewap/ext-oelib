<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\EmptyModel;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\ReadOnlyRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

class ReadOnlyTest extends UnitTestCase
{
    /**
     * @var ReadOnlyRepository
     */
    private $subject = null;

    protected function setUp(): void
    {
        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new ReadOnlyRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function addThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->add(new EmptyModel());
    }

    /**
     * @test
     */
    public function removeThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->remove(new EmptyModel());
    }

    /**
     * @test
     */
    public function updateThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->update(new EmptyModel());
    }

    /**
     * @test
     */
    public function removeAllThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->removeAll();
    }
}
