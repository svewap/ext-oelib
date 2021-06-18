<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Session;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Session\FakeSession;

/**
 * Test case.
 */
class FakeSessionTest extends UnitTestCase
{
    /**
     * @var FakeSession
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new FakeSession();
    }

    /*
     * Tests for the basic functions
     */

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function fakeSessionCanBeInstantiatedDirectly()
    {
        new FakeSession();
    }

    /*
     * Tests that the setters/getters work
     */

    /**
     * @test
     */
    public function getAsStringWithInexistentKeyReturnsEmptyString()
    {
        self::assertSame(
            '',
            $this->subject->getAsString('foo')
        );
    }

    /**
     * @test
     */
    public function getAsStringReturnsNonEmptyStringSetViaSetAsString()
    {
        $this->subject->setAsString('foo', 'bar');

        self::assertSame(
            'bar',
            $this->subject->getAsString('foo')
        );
    }

    /**
     * @test
     */
    public function getAsStringReturnsEmptyStringSetViaSetAsString()
    {
        $this->subject->setAsString('foo', '');

        self::assertSame(
            '',
            $this->subject->getAsString('foo')
        );
    }
}
