<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Session;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FakeSessionTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_FakeSession
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_FakeSession();
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
        new \Tx_Oelib_FakeSession();
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
