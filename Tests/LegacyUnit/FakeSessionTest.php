<?php

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_FakeSessionTest extends TestCase
{
    /**
     * @var \Tx_Oelib_FakeSession the object to test
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_FakeSession();
    }

    /////////////////////////////////////////////////////////
    // Tests for the basic functions
    /////////////////////////////////////////////////////////

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function fakeSessionCanBeInstantiatedDirectly()
    {
        new \Tx_Oelib_FakeSession();
    }

    ////////////////////////////////////////
    // Tests that the setters/getters work
    ////////////////////////////////////////

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
