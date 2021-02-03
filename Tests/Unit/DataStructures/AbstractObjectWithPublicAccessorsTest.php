<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\DataStructures\Fixtures\TestingObjectWithPublicAccessors;

/**
 * @covers \OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractObjectWithPublicAccessorsTest extends UnitTestCase
{
    /**
     * @var TestingObjectWithPublicAccessors
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new TestingObjectWithPublicAccessors();
    }

    /**
     * @test
     */
    public function checkForNonEmptyKeyWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->checkForNonEmptyKey('');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function checkForNonEmptyKeyWithNonEmptyKeyIsAllowed()
    {
        $this->subject->checkForNonEmptyKey('foo');
    }

    /**
     * @test
     */
    public function getAsStringWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsString('');
    }

    /**
     * @test
     */
    public function setAsStringWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setAsString('', 'bar');
    }

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
    public function getAsStringReturnsTrimmedValue()
    {
        $this->subject->setAsString('foo', ' bar ');

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

    /**
     * @test
     */
    public function getAsIntegerWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsInteger('');
    }

    /**
     * @test
     */
    public function setAsIntegerWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setAsInteger('', 42);
    }

    /**
     * @test
     */
    public function getAsIntegerWithInexistentKeyReturnsZero()
    {
        self::assertSame(
            0,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsPositiveIntegerSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', 42);

        self::assertSame(
            42,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsNegativeIntegerSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', -42);

        self::assertSame(
            -42,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsZeroSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', 0);

        self::assertSame(
            0,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsZeroForStringSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', 'bar');

        self::assertSame(
            0,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsRoundedValueForFloatSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', 12.34);

        self::assertSame(
            12,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsTrimmedArray('');
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsIntegerArray('');
    }

    /**
     * @test
     */
    public function setAsArrayWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setAsArray('', ['bar']);
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithInexistentKeyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getAsTrimmedArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithInexistentKeyReturnsEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getAsIntegerArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsNonEmptyArraySetViaSetAsArray()
    {
        $this->subject->setAsArray('foo', ['foo', 'bar']);

        self::assertSame(
            ['foo', 'bar'],
            $this->subject->getAsTrimmedArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerArrayReturnsNonEmptyArraySetViaSetAsArray()
    {
        $this->subject->setAsArray('foo', [1, -2]);

        self::assertSame(
            [1, -2],
            $this->subject->getAsIntegerArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsEmptyArraySetViaSetAsArray()
    {
        $this->subject->setAsArray('foo', []);

        self::assertSame(
            [],
            $this->subject->getAsTrimmedArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerArrayReturnsEmptyArraySetViaSetAsArray()
    {
        $this->subject->setAsArray('foo', []);

        self::assertSame(
            [],
            $this->subject->getAsIntegerArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsTrimmedValues()
    {
        $this->subject->setAsArray('foo', [' foo ']);

        self::assertSame(
            ['foo'],
            $this->subject->getAsTrimmedArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerArrayReturnsIntvaledValues()
    {
        $this->subject->setAsArray('foo', ['asdf']);

        self::assertSame(
            [0],
            $this->subject->getAsIntegerArray('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsBoolean('');
    }

    /**
     * @test
     */
    public function setAsBooleanWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setAsBoolean('', false);
    }

    /**
     * @test
     */
    public function getAsBooleanWithInexistentKeyReturnsFalse()
    {
        self::assertFalse(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueSetViaSetAsBoolean()
    {
        $this->subject->setAsBoolean('foo', true);

        self::assertTrue(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsFalseSetViaSetAsBoolean()
    {
        $this->subject->setAsBoolean('foo', false);

        self::assertFalse(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueForNonEmptyStringSetViaSetAsBoolean()
    {
        $this->subject->setAsBoolean('foo', 'bar');

        self::assertTrue(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsFalseForEmptyStringSetViaSetAsBoolean()
    {
        $this->subject->setAsBoolean('foo', '');

        self::assertFalse(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsOneForTrueSetViaSetAsBoolean()
    {
        $this->subject->setAsBoolean('foo', true);

        self::assertSame(
            1,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsZeroForFalseSetViaSetAsBoolean()
    {
        $this->subject->setAsBoolean('foo', false);

        self::assertSame(
            0,
            $this->subject->getAsInteger('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueForPositiveIntegerSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', 42);

        self::assertTrue(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueForNegativeIntegerSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', -42);

        self::assertTrue(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsFalseForZeroSetViaSetAsInteger()
    {
        $this->subject->setAsInteger('foo', 0);

        self::assertFalse(
            $this->subject->getAsBoolean('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->getAsFloat('');
    }

    /**
     * @test
     */
    public function setAsFloatWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->setAsFloat('', 42.5);
    }

    /**
     * @test
     */
    public function getAsFloatWithInexistentKeyReturnsZero()
    {
        self::assertSame(
            0.0,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatCanReturnPositiveFloatFromFloat()
    {
        $this->subject->setData(['foo' => 42.5]);

        self::assertSame(
            42.5,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatReturnsPositiveFloatSetViaSetAsFloat()
    {
        $this->subject->setAsFloat('foo', 42.5);

        self::assertSame(
            42.5,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatReturnsPositiveFloatSetAsStringViaSetAsFloat()
    {
        $this->subject->setAsFloat('foo', '42.5');

        self::assertSame(
            42.5,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatReturnsNegativeFloatSetViaSetAsFloat()
    {
        $this->subject->setAsFloat('foo', -42.5);

        self::assertSame(
            -42.5,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatReturnsZeroSetViaSetAsFloat()
    {
        $this->subject->setAsFloat('foo', 0.5);

        self::assertSame(
            0.5,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatReturnsZeroForStringSetViaSetAsFloat()
    {
        $this->subject->setAsFloat('foo', 'bar');

        self::assertSame(
            0.0,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function getAsFloatCanReturnPositiveFloatFromString()
    {
        $this->subject->setData(['foo' => '42.5']);

        self::assertSame(
            42.5,
            $this->subject->getAsFloat('foo')
        );
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringReturnsTrue()
    {
        $this->subject->setAsString('foo', 'bar');

        self::assertTrue(
            $this->subject->hasString('foo')
        );
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringReturnsFalse()
    {
        $this->subject->setAsString('foo', '');

        self::assertFalse(
            $this->subject->hasString('foo')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerReturnsTrue()
    {
        $this->subject->setAsInteger('foo', 42);

        self::assertTrue(
            $this->subject->hasInteger('foo')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerReturnsTrue()
    {
        $this->subject->setAsInteger('foo', -42);

        self::assertTrue(
            $this->subject->hasInteger('foo')
        );
    }

    /**
     * @test
     */
    public function hasIntegerForZeroReturnsFalse()
    {
        $this->subject->setAsInteger('foo', 0);

        self::assertFalse(
            $this->subject->hasInteger('foo')
        );
    }

    /**
     * @test
     */
    public function hasFloatForPositiveFloatReturnsTrue()
    {
        $this->subject->setAsFloat('foo', 42.00);

        self::assertTrue(
            $this->subject->hasFloat('foo')
        );
    }

    /**
     * @test
     */
    public function hasFloatForNegativeFloatReturnsTrue()
    {
        $this->subject->setAsFloat('foo', -42.00);

        self::assertTrue(
            $this->subject->hasFloat('foo')
        );
    }

    /**
     * @test
     */
    public function hasFloatForZeroReturnsFalse()
    {
        $this->subject->setAsFloat('foo', 0.00);

        self::assertFalse(
            $this->subject->hasFloat('foo')
        );
    }
}
