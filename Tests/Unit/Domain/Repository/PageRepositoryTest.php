<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository;

use OliverKlee\Oelib\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Repository\PageRepository
 */
final class PageRepositoryTest extends UnitTestCase
{
    /**
     * @var PageRepository
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new PageRepository();
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }
}
