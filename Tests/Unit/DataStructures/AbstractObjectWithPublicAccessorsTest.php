<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\DataStructures;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Tests\Unit\DataStructures\Fixtures\TestingObjectWithPublicAccessors;

/**
 * @covers \OliverKlee\Oelib\DataStructures\AbstractObjectWithAccessors
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
     * @return array<string, array<int, string|int|bool>>
     */
    public function stringDataProvider(): array
    {
        return [
            'empty string' => ['', ''],
            'non-empty string' => ['bar', 'bar'],
            'integer' => [1, '1'],
            'boolean true' => [true, '1'],
            'boolean false' => [false, ''],
        ];
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider stringDataProvider
     */
    public function getAsStringReturnsDataCastToString($inputValue, string $expected)
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame($expected, $this->subject->getAsString($key));
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider stringDataProvider
     */
    public function setAsStringSetsDataToString($inputValue, string $expected)
    {
        $key = 'foo';
        $this->subject->setAsString($key, $inputValue);

        self::assertSame($expected, $this->subject->getAsString($key));
    }

    /**
     * @test
     */
    public function getAsStringReturnsTrimmedValue()
    {
        $key = 'foo';
        $this->subject->setData([$key => ' bar ']);

        self::assertSame('bar', $this->subject->getAsString($key));
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
     * @return array<string, array<int, int|string|float|bool>>
     */
    public function integerDataProvider(): array
    {
        return [
            'zero' => [0, 0],
            'positive integer' => [2, 2],
            'negative integer' => [-2, -2],
            'integer as string' => ['2', 2],
            'any other string' => ['bar', 0],
            'boolean true' => [true, 1],
            'float' => [12.34, 12],
        ];
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider integerDataProvider
     */
    public function getAsIntegerReturnsDataCastToInteger($inputValue, int $expected)
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame($expected, $this->subject->getAsInteger($key));
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider integerDataProvider
     */
    public function setAsIntegerSetsDataToInteger($inputValue, int $expected)
    {
        $key = 'foo';
        $this->subject->setAsInteger($key, $inputValue);

        self::assertSame($expected, $this->subject->getAsInteger($key));
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
    public function getAsIntegerArrayWithEmptyDataReturnsEmptyArray()
    {
        $key = 'foo';
        $this->subject->setData([$key => '']);

        self::assertSame([], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsIntegerArraySplitsCommaSeparatedString()
    {
        $key = 'foo';
        $this->subject->setData([$key => '7,4']);

        self::assertSame([7, 4], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider integerDataProvider
     */
    public function getAsIntegerArrayCastsValuesToInteger($inputValue, int $expected)
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame([$expected], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider integerDataProvider
     */
    public function getAsIntegerArrayCastsValuesFromSetAsArrayToInteger($inputValue, int $expected)
    {
        $key = 'foo';
        $this->subject->setAsArray($key, [$inputValue]);

        self::assertSame([$expected], $this->subject->getAsIntegerArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayWithEmptyDataReturnsEmptyArray()
    {
        $key = 'foo';
        $this->subject->setData([$key => '']);

        self::assertSame([], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArraySplitsCommaSeparatedString()
    {
        $key = 'foo';
        $this->subject->setData([$key => 'hey,ho']);

        self::assertSame(['hey', 'ho'], $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayReturnsDataSetViaSetAsArray()
    {
        $key = 'foo';
        $value = ['foo', 'bar'];
        $this->subject->setAsArray($key, $value);

        self::assertSame($value, $this->subject->getAsTrimmedArray($key));
    }

    /**
     * @test
     */
    public function getAsTrimmedArrayTrimsValues()
    {
        $key = 'foo';
        $this->subject->setData([$key => ' hey , ho ']);

        self::assertSame(['hey', 'ho'], $this->subject->getAsTrimmedArray($key));
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
     * @return array<string, array>
     */
    public function booleanDataProvider(): array
    {
        return [
            'boolean false' => [false, false],
            'boolean true' => [true, true],
            'integer 0' => [0, false],
            'integer 1' => [1, true],
            'string 0' => ['0', false],
            'string 1' => ['1', true],
            'empty string 0' => ['', false],
            'some other string' => ['hello', true],
        ];
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider booleanDataProvider
     */
    public function getAsBooleanCastsDataToBoolean($inputValue, bool $expected)
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertSame($expected, $this->subject->getAsBoolean($key));
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider booleanDataProvider
     */
    public function setAsBooleanSetsAndCastsDataToBoolean($inputValue, bool $expected)
    {
        $key = 'foo';
        $this->subject->setAsBoolean($key, $inputValue);

        self::assertSame($expected, $this->subject->getAsBoolean($key));
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
     * @return array<string, array>
     */
    public function floatDataProvider(): array
    {
        return [
            'zero float' => [0.0, 0.0],
            'positive float' => [12.3, 12.3],
            'negative float' => [-12.3, -12.3],
            'zero float as string' => ['0.0', 0.0],
            'positive float as string' => ['12.3', 12.3],
            'negative float as string' => ['-12.3', -12.3],
            'zero integer' => [0, 0.0],
            'positive integer' => [12, 12.0],
            'negative integer' => [-12, -12.0],
            'zero integer as string' => ['0', 0.0],
            'positive integer as string' => ['12', 12.0],
            'negative integer as string' => ['-12', -12.0],
            'some random string' => ['hello', 0.0],
            'boolean true' => [true, 1.0],
            'boolean false' => [false, 0.0],
        ];
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider floatDataProvider
     */
    public function getAsFloatCastsDataToFloat($inputValue, float $expected)
    {
        $key = 'foo';
        $this->subject->setData([$key => $inputValue]);

        self::assertEquals($expected, $this->subject->getAsFloat($key), '', 0.001);
    }

    /**
     * @test
     *
     * @param mixed $inputValue
     *
     * @dataProvider floatDataProvider
     */
    public function setAsFloatSetsAndCastsDataToFloat($inputValue, float $expected)
    {
        $key = 'foo';
        $this->subject->setAsFloat($key, $inputValue);

        self::assertEquals($expected, $this->subject->getAsFloat($key), '', 0.001);
    }

    /**
     * @test
     */
    public function hasStringForNonEmptyStringReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setData([$key => 'bar']);

        self::assertTrue($this->subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasStringForEmptyStringReturnsFalse()
    {
        $key = 'foo';
        $this->subject->setData([$key => '']);

        self::assertFalse($this->subject->hasString($key));
    }

    /**
     * @test
     */
    public function hasIntegerForPositiveIntegerReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setData([$key => 42]);

        self::assertTrue($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForNegativeIntegerReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setData([$key => -42]);

        self::assertTrue($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasIntegerForZeroReturnsFalse()
    {
        $key = 'foo';
        $this->subject->setData([$key => 0]);

        self::assertFalse($this->subject->hasInteger($key));
    }

    /**
     * @test
     */
    public function hasFloatForPositiveFloatReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setData([$key => 42.1]);

        self::assertTrue($this->subject->hasFloat($key));
    }

    /**
     * @test
     */
    public function hasFloatForNegativeFloatReturnsTrue()
    {
        $key = 'foo';
        $this->subject->setData([$key => -42.1]);

        self::assertTrue($this->subject->hasFloat($key));
    }

    /**
     * @test
     */
    public function hasFloatForZeroReturnsFalse()
    {
        $key = 'foo';
        $this->subject->setData([$key => 0.0]);

        self::assertFalse($this->subject->hasFloat($key));
    }
}
