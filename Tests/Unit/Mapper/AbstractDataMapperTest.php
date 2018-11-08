<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;
use OliverKlee\Oelib\Tests\Unit\Model\Fixtures\TestingModel;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class AbstractDataMapperTest extends UnitTestCase
{
    /**
     * @var TestingMapper
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingMapper();
    }

    /**
     * @test
     */
    public function canUseNamespacedModel()
    {
        $result = $this->subject->getNewGhost();

        static::assertInstanceOf(TestingModel::class, $result);
    }
}
