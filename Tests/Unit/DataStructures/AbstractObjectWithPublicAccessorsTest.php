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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

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
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->getAsString('');
    }

    /**
     * @test
     */
    public function setAsStringWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->setAsString('', 'bar');
    }

    /**
     * @test
     */
    public function getAsStringWithInexistentKeyReturnsEmptyString()
    {
        self::assertSame('', $this->subject->getAsString('foo'));
    }

    /**
     * @test
     */
    public function getAsStringReturnsNonEmptyStringSetViaSetAsString()
    {
        $key = 'foo';
        $value = 'bar';
        $this->subject->setAsString($key, $value);

        self::assertSame($value, $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringReturnsTrimmedValue()
    {
        $key = 'foo';
        $this->subject->setAsString($key, ' bar ');

        self::assertSame('bar', $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringReturnsEmptyStringSetViaSetAsString()
    {
        $key = 'foo';
        $this->subject->setAsString($key, '');

        self::assertSame('', $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsIntegerWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->getAsInteger('');
    }

    /**
     * @test
     */
    public function setAsIntegerWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->setAsInteger('', 42);
    }

    /**
     * @test
     */
    public function getAsIntegerWithInexistentKeyReturnsZero()
    {
        self::assertSame(0, $this->subject->getAsInteger('foo'));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsPositiveIntegerSetViaSetAsInteger()
    {
        $key = 'foo';
        $value = 42;
        $this->subject->setAsInteger($key, $value);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsNegativeIntegerSetViaSetAsInteger()
    {
        $key = 'foo';
        $value = -42;
        $this->subject->setAsInteger($key, $value);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsZeroSetViaSetAsInteger()
    {
        $key = 'foo';
        $value = 0;
        $this->subject->setAsInteger($key, $value);

        self::assertSame($value, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsZeroForStringSetViaSetAsInteger()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, 'bar');

        self::assertSame(0, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsRoundedValueForFloatSetViaSetAsInteger()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, 12.34);

        self::assertSame(12, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->getAsTrimmedArray('');
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->getAsIntegerArray('');
    }

    /**
     * @test
     */
    public function setAsArrayWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->setAsArray('', ['bar']);
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithInexistentKeyReturnsEmptyArray()
    {
        self::assertSame([], $this->subject->getAsTrimmedArray('foo'));
    }

    /**
     * @test
     */
    public function getAsIntegerArrayWithInexistentKeyReturnsEmptyArray()
    {
        self::assertSame([], $this->subject->getAsIntegerArray('foo'));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsNonEmptyArraySetViaSetAsArray()
    {
        $key = 'foo';
        $value = ['foo', 'bar'];
        $this->subject->setAsArray($key, $value);

        self::assertSame($value, $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsIntegerArrayReturnsNonEmptyArraySetViaSetAsArray()
    {
        $key = 'foo';
        $value = [1, -2];
        $this->subject->setAsArray($key, $value);

        self::assertSame($value, $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsEmptyArraySetViaSetAsArray()
    {
        $key = 'foo';
        $this->subject->setAsArray($key, []);

        self::assertSame([], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsIntegerArrayReturnsEmptyArraySetViaSetAsArray()
    {
        $key = 'foo';
        $this->subject->setAsArray($key, []);

        self::assertSame([], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsTrimmedValues()
    {
        $key = 'foo';
        $this->subject->setAsArray($key, [' foo ']);

        self::assertSame(['foo'], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsIntegerArrayReturnsIntegerCastValues()
    {
        $key = 'foo';
        $this->subject->setAsArray($key, ['asdf']);

        self::assertSame([0], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsBooleanWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->getAsBoolean('');
    }

    /**
     * @test
     */
    public function setAsBooleanWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->setAsBoolean('', false);
    }

    /**
     * @test
     */
    public function getAsBooleanWithInexistentKeyReturnsFalse()
    {
        self::assertFalse($this->subject->getAsBoolean('foo'));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueSetViaSetAsBoolean()
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, true);

        self::assertTrue($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsFalseSetViaSetAsBoolean()
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, false);

        self::assertFalse($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueForNonEmptyStringSetViaSetAsBoolean()
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, 'bar');

        self::assertTrue($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsFalseForEmptyStringSetViaSetAsBoolean()
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, '');

        self::assertFalse($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsOneForTrueSetViaSetAsBoolean()
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, true);

        self::assertSame(1, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsIntegerReturnsZeroForFalseSetViaSetAsBoolean()
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, false);

        self::assertSame(0, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueForPositiveIntegerSetViaSetAsInteger()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, 42);

        self::assertTrue($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsTrueForNegativeIntegerSetViaSetAsInteger()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, -42);

        self::assertTrue($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsBooleanReturnsFalseForZeroSetViaSetAsInteger()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, 0);

        self::assertFalse($this->subject->getAsBoolean($key));
    }

    /**
     * @test
     */
    public function getAsFloatWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->getAsFloat('');
    }

    /**
     * @test
     */
    public function setAsFloatWithEmptyKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$key must not be empty.');
        $this->expectExceptionCode(1331488963);

        $this->subject->setAsFloat('', 42.5);
    }

    /**
     * @test
     */
    public function getAsFloatWithInexistentKeyReturnsZero()
    {
        self::assertSame(0.0, $this->subject->getAsFloat('foo'));
    }

    /**
     * @test
     */
    public function getAsFloatCanReturnPositiveFloatFromFloat()
    {
        $key = 'foo';
        $value = 42.5;
        $this->subject->setData([$key => $value]);

        self::assertSame($value, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function getAsFloatReturnsPositiveFloatSetViaSetAsFloat()
    {
        $key = 'foo';
        $value = 42.5;
        $this->subject->setAsFloat($key, $value);

        self::assertSame($value, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function getAsFloatReturnsPositiveFloatSetAsStringViaSetAsFloat()
    {
        $key = 'foo';
        $this->subject->setAsFloat($key, '42.5');

        self::assertSame(42.5, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function getAsFloatReturnsNegativeFloatSetViaSetAsFloat()
    {
        $key = 'foo';
        $value = -42.5;
        $this->subject->setAsFloat($key, $value);

        self::assertSame($value, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function getAsFloatReturnsZeroSetViaSetAsFloat()
    {
        $key = 'foo';
        $value = 0.0;
        $this->subject->setAsFloat($key, $value);

        self::assertSame($value, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function getAsFloatReturnsZeroForStringSetViaSetAsFloat()
    {
        $key = 'foo';
        $this->subject->setAsFloat($key, 'bar');

        self::assertSame(0.0, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function getAsFloatCanReturnPositiveFloatFromString()
    {
        $key = 'foo';
        $this->subject->setData([$key => '42.5']);

        self::assertSame(42.5, $this->subject->getAsFloat($key));
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setAsString($key, 'bar');

        self::assertTrue($this->subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringReturnsFalse()
    {
        $key = 'foo';
        $this->subject->setAsString($key, '');

        self::assertFalse($this->subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, 42);

        self::assertTrue($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, -42);

        self::assertTrue($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForZeroReturnsFalse()
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, 0);

        self::assertFalse($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasFloatForPositiveFloatReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setAsFloat($key, 42.0);

        self::assertTrue($this->subject->hasFloat($key));
    }

    /**
     * @test
     */
    public function hasFloatForNegativeFloatReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setAsFloat($key, -42.0);

        self::assertTrue($this->subject->hasFloat($key));
    }

    /**
     * @test
     */
    public function hasFloatForZeroReturnsFalse()
    {
        $key = 'foo';
        $this->subject->setAsFloat($key, 0.0);

        self::assertFalse($this->subject->hasFloat($key));
    }
}
