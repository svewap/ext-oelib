<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Session;

use OliverKlee\Oelib\Session\FakeSession;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Session\FakeSession
 * @covers \OliverKlee\Oelib\Session\Session
 */
final class FakeSessionTest extends UnitTestCase
{
    /**
     * @var FakeSession
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FakeSession();
    }

    // Tests for the basic functions

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function fakeSessionCanBeInstantiatedDirectly(): void
    {
        new FakeSession();
    }

    // Tests that the setters/getters work

    /**
     * @test
     */
    public function getAsStringWithInexistentKeyReturnsEmptyString(): void
    {
        self::assertSame(
            '',
            $this->subject->getAsString('foo')
        );
    }

    /**
     * @test
     */
    public function getAsStringReturnsNonEmptyStringSetViaSetAsString(): void
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
    public function getAsStringReturnsEmptyStringSetViaSetAsString(): void
    {
        $this->subject->setAsString('foo', '');

        self::assertSame(
            '',
            $this->subject->getAsString('foo')
        );
    }
}
