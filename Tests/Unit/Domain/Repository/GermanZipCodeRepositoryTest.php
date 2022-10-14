<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository;

use OliverKlee\Oelib\Domain\Model\GermanZipCode;
use OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Repository;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Repository\GermanZipCodeRepository
 */
final class GermanZipCodeRepositoryTest extends UnitTestCase
{
    /**
     * @var GermanZipCodeRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new GermanZipCodeRepository($objectManagerStub);
    }

    /**
     * @test
     */
    public function isRepository(): void
    {
        self::assertInstanceOf(Repository::class, $this->subject);
    }

    /**
     * @test
     */
    public function addThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->add(new GermanZipCode());
    }

    /**
     * @test
     */
    public function removeThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->remove(new GermanZipCode());
    }

    /**
     * @test
     */
    public function updateThrowsException(): void
    {
        $this->expectException(\BadMethodCallException::class);

        $this->subject->update(new GermanZipCode());
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
