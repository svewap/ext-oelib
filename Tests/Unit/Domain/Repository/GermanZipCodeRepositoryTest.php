<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;

/**
 * Test case.
 */
class GermanZipCodeRepositoryTest extends UnitTestCase
{
    /**
     * @var GermanZipCodeRepository
     */
    private $subject = null;

    protected function setUp()
    {
        /** @var ObjectManagerInterface|ProphecySubjectInterface $objectManagerStub */
        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new GermanZipCodeRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function isRepository()
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    /**
     * @test
     */
    public function addThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->add(new GermanZipCode());
    }

    /**
     * @test
     */
    public function removeThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->remove(new GermanZipCode());
    }

    /**
     * @test
     */
    public function updateThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->update(new GermanZipCode());
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
