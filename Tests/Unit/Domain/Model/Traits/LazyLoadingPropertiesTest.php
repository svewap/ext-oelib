<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model\Traits;

use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\EmptyModel;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\LazyLoadingModel;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Domain\Model\Traits\LazyLoadingProperties
 */
final class LazyLoadingPropertiesTest extends UnitTestCase
{
    /**
     * @var LazyLoadingModel
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new LazyLoadingModel();
    }

    /**
     * @test
     */
    public function loadLazyPropertyLoadsRealInstance(): void
    {
        $realInstance = new EmptyModel();
        $parentObject = new LazyLoadingModel();
        $parentObject->_setProperty('lazyProperty', $realInstance);
        $dataMapper = $this->prophesize(DataMapper::class)->reveal();
        $proxy = new LazyLoadingProxy($parentObject, 'lazyProperty', $realInstance, $dataMapper);

        $this->subject->setLazyProperty($proxy);

        $result = $this->subject->getLazyProperty();

        self::assertSame($realInstance, $result);
    }
}
