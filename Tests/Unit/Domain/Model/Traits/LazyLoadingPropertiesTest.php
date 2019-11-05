<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Domain\Model\Traits;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\EmptyModel;
use OliverKlee\Oelib\Tests\Unit\Domain\Fixtures\LazyLoadingModel;
use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class LazyLoadingPropertiesTest extends UnitTestCase
{
    /**
     * @var LazyLoadingModel
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new LazyLoadingModel();
    }

    /**
     * @test
     */
    public function loadLazyPropertyLoadsRealInstance()
    {
        $realInstance = new EmptyModel();
        $parentObject = new LazyLoadingModel();
        $parentObject->_setProperty('lazyProperty', $realInstance);
        $proxy = new LazyLoadingProxy($parentObject, 'lazyProperty', $realInstance);

        $this->subject->setLazyProperty($proxy);

        $result = $this->subject->getLazyProperty();

        self::assertSame($realInstance, $result);
    }
}
