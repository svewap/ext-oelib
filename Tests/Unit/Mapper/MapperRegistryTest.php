<?php

namespace OliverKlee\Oelib\Tests\Unit\Mapper;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\Mapper\Fixtures\TestingMapper;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class MapperRegistryTest extends UnitTestCase
{
    protected function tearDown()
    {
        \Tx_Oelib_MapperRegistry::purgeInstance();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function canReturnNamespacedMapper()
    {
        $result = \Tx_Oelib_MapperRegistry::get(TestingMapper::class);

        static::assertInstanceOf(TestingMapper::class, $result);
    }
}
