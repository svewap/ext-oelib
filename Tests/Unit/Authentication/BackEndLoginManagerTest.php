<?php

namespace OliverKlee\Oelib\Tests\Unit\Authentication;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndLoginManagerTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_BackEndLoginManager
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = \Tx_Oelib_BackEndLoginManager::getInstance();
    }

    /**
     * @test
     */
    public function getInstanceReturnsBackEndLoginManagerInstance()
    {
        self::assertInstanceOf(\Tx_Oelib_BackEndLoginManager::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame($this->subject, \Tx_Oelib_BackEndLoginManager::getInstance());
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        \Tx_Oelib_BackEndLoginManager::purgeInstance();

        self::assertNotSame($this->subject, \Tx_Oelib_BackEndLoginManager::getInstance());
    }

    /**
     * @test
     */
    public function getLoggedInUserWithEmptyMapperNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$mapperName must not be empty.');

        $this->subject->getLoggedInUser('');
    }
}
