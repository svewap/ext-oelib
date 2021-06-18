<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Repository;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\SingletonInterface;

class PageRepositoryTest extends UnitTestCase
{
    /**
     * @var PageRepository
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new PageRepository();
    }

    /**
     * @test
     */
    public function isSingleton()
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }
}
