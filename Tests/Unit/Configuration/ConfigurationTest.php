<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\Configuration;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ConfigurationTest extends UnitTestCase
{
    /**
     * @var Configuration
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new Configuration();
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function setWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$key must not be empty.'
        );

        $this->subject->set('', 'foo');
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setDataWithEmptyArrayIsAllowed()
    {
        $this->subject->setData([]);
    }

    /**
     * @test
     */
    public function getAfterSetReturnsTheSetValue()
    {
        $this->subject->set('foo', 'bar');

        self::assertSame(
            'bar',
            $this->subject->getAsString('foo')
        );
    }

    /**
     * @test
     */
    public function getAfterSetDataReturnsTheSetValue()
    {
        $this->subject->setData(
            ['foo' => 'bar']
        );

        self::assertSame(
            'bar',
            $this->subject->getAsString('foo')
        );
    }

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setDataCalledTwoTimesDoesNotFail()
    {
        $this->subject->setData(
            ['title' => 'bar']
        );
        $this->subject->setData(
            ['title' => 'bar']
        );
    }

    ////////////////////////////////////
    // Tests regarding getArrayKeys().
    ////////////////////////////////////

    /**
     * @test
     */
    public function getArrayKeysWithEmptyKeyReturnsKeysOfDataArray()
    {
        $this->subject->setData(['first' => 'test', 'second' => 'test']);

        self::assertSame(
            ['first', 'second'],
            $this->subject->getArrayKeys()
        );
    }

    /**
     * @test
     */
    public function getArrayKeysForInexistentKeyReturnEmptyArray()
    {
        self::assertSame(
            [],
            $this->subject->getArrayKeys('key')
        );
    }

    /**
     * @test
     */
    public function getArrayKeysForKeyOfStringDataItemReturnsEmptyArray()
    {
        $this->subject->setData(['key' => 'blub']);

        self::assertSame(
            [],
            $this->subject->getArrayKeys('key')
        );
    }

    /**
     * @test
     */
    public function getArrayKeysForKeyOfDataItemWithOneArrayElementReturnsKeyOfArrayElement()
    {
        $this->subject->setData(['key' => ['test' => 'child']]);

        self::assertSame(
            ['test'],
            $this->subject->getArrayKeys('key')
        );
    }

    /**
     * @test
     */
    public function getArrayKeysForKeyOfDataItemWithTwoArrayElementsReturnsKeysOfArrayElements()
    {
        $this->subject->setData(
            ['key' => ['first' => 'child', 'second' => 'child']]
        );

        self::assertSame(
            ['first', 'second'],
            $this->subject->getArrayKeys('key')
        );
    }

    /**
     * @test
     */
    public function getAsMultidimensionalArrayReturnsMultidimensionalArray()
    {
        $this->subject->setData(
            ['1' => ['1.1' => ['1.1.1' => 'child']]]
        );

        self::assertSame(
            ['1.1' => ['1.1.1' => 'child']],
            $this->subject->getAsMultidimensionalArray('1')
        );
    }

    /**
     * @test
     */
    public function getAsMultidimensionalArrayForInexistentKeyReturnsEmptyArray()
    {
        $this->subject->setData([]);

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1')
        );
    }

    /**
     * @test
     */
    public function getAsMultidimensionalArrayForStringReturnsEmptyArray()
    {
        $this->subject->setData(
            ['1' => 'child']
        );

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1')
        );
    }

    /**
     * @test
     */
    public function getAsMultidimensionalArrayForIntegerReturnsEmptyArray()
    {
        $this->subject->setData(
            ['1' => 42]
        );

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1')
        );
    }

    /**
     * @test
     */
    public function getAsMultidimensionalArrayForFloatReturnsEmptyArray()
    {
        $this->subject->setData(
            ['1' => 42.42]
        );

        self::assertSame(
            [],
            $this->subject->getAsMultidimensionalArray('1')
        );
    }
}
