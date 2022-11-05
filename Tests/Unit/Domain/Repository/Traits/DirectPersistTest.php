<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\DirectPersistRepository;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist
 */
final class DirectPersistTest extends UnitTestCase
{
    /**
     * @var DirectPersistRepository
     */
    private $subject;

    /**
     * @var PersistenceManagerInterface&MockObject
     */
    private $persistenceManagerMock;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManagerStub = $this->createMock(ObjectManagerInterface::class);
        $this->subject = new DirectPersistRepository($objectManagerStub);

        $this->persistenceManagerMock = $this->createMock(PersistenceManagerInterface::class);
        $this->subject->injectPersistenceManager($this->persistenceManagerMock);
    }

    /**
     * @test
     */
    public function implementsDirectPersist(): void
    {
        self::assertInstanceOf(DirectPersist::class, $this->subject);
    }

    /**
     * @test
     */
    public function persistAllPersistsAll(): void
    {
        $this->persistenceManagerMock->expects(self::atLeastOnce())->method('persistAll');

        $this->subject->persistAll();
    }
}
