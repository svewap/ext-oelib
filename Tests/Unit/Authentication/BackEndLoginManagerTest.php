<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Authentication;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Authentication\BackEndLoginManager;

class BackEndLoginManagerTest extends UnitTestCase
{
    /**
     * @var BackEndLoginManager
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = BackEndLoginManager::getInstance();
    }

    /**
     * @test
     */
    public function getInstanceReturnsBackEndLoginManagerInstance()
    {
        self::assertInstanceOf(BackEndLoginManager::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame($this->subject, BackEndLoginManager::getInstance());
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        BackEndLoginManager::purgeInstance();

        self::assertNotSame($this->subject, BackEndLoginManager::getInstance());
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
