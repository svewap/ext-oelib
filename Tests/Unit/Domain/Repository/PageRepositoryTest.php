<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;

/**
 * @covers \OliverKlee\Oelib\Domain\Repository\PageRepository
 */
class PageRepositoryTest extends UnitTestCase
{
    /**
     * @var PageRepository
     */
    private $subject = null;

    protected function setUp(): void
    {
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
