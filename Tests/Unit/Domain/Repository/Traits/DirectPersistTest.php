<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\DirectPersistRepository;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

/**
 * @covers \OliverKlee\Oelib\Domain\Repository\Traits\DirectPersist
 */
final class DirectPersistTest extends UnitTestCase
{
    /**
     * @var DirectPersistRepository
     */
    private $subject = null;

    /**
     * @var ObjectProphecy<PersistenceManagerInterface>
     */
    private $persistenceManagerProphecy = null;

    protected function setUp(): void
    {
        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new DirectPersistRepository($objectManagerStub);

        $this->persistenceManagerProphecy = $this->prophesize(PersistenceManagerInterface::class);
        $persistenceManager = $this->persistenceManagerProphecy->reveal();
        $this->subject->injectPersistenceManager($persistenceManager);
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
        $this->persistenceManagerProphecy->persistAll()->shouldBeCalled();

        $this->subject->persistAll();
    }
}
