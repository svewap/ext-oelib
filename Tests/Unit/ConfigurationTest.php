<?php

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_ConfigurationTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_Configuration the model to test
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Configuration();
    }

    //////////////////////////////////////
    // Tests for the basic functionality
    //////////////////////////////////////

    /**
     * @test
     */
    public function setWithEmptyKeyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$key must not be empty.'
        );

        $this->subject->set('', 'foo');
    }

    /**
     * @test
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
