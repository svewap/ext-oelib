<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Repository\Interfaces\DirectPersist;
use OliverKlee\Oelib\Tests\Unit\Domain\Repository\Fixtures\DirectPersistRepository;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophecy\ProphecySubjectInterface;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

class DirectPersistTest extends UnitTestCase
{
    /**
     * @var DirectPersistRepository
     */
    private $subject = null;

    /**
     * @var ObjectProphecy
     */
    private $persistenceManagerProphecy = null;

    protected function setUp()
    {
        /** @var ObjectManagerInterface&ProphecySubjectInterface $objectManagerStub */
        $objectManagerStub = $this->prophesize(ObjectManagerInterface::class)->reveal();
        $this->subject = new DirectPersistRepository($objectManagerStub);

        $this->persistenceManagerProphecy = $this->prophesize(PersistenceManagerInterface::class);
        /** @var PersistenceManagerInterface&ProphecySubjectInterface $persistenceManager */
        $persistenceManager = $this->persistenceManagerProphecy->reveal();
        $this->subject->injectPersistenceManager($persistenceManager);
    }

    /**
     * @test
     */
    public function implementsDirectPersist()
    {
        self::assertInstanceOf(DirectPersist::class, $this->subject);
    }

    /**
     * @test
     */
    public function persistAllPersistsAll()
    {
        // @phpstan-ignore-next-line PHPStan does not know Prophecy (at least not without the corresponding plugin).
        $this->persistenceManagerProphecy->persistAll()->shouldBeCalled();

        $this->subject->persistAll();
    }
}
