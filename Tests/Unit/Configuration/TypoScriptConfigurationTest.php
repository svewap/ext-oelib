<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\TypoScriptConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration as ConfigurationInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Configuration\TypoScriptConfiguration
 */
final class TypoScriptConfigurationTest extends UnitTestCase
{
    /**
     * @var TypoScriptConfiguration
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new TypoScriptConfiguration();
    }

    /**
     * @test
     */
    public function implementsConfigurationInterface(): void
    {
        self::assertInstanceOf(ConfigurationInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function hasTypoScriptName(): void
    {
        $subject = new TypoScriptConfiguration();

        self::assertSame('in your TypoScript template', $subject->getSourceName());
    }

    /**
     * @test
     */
    public function isObjectWithPublicAccessors(): void
    {
        self::assertInstanceOf(AbstractReadOnlyObjectWithPublicAccessors::class, $this->subject);
    }

    // Tests for the basic functionality

    /**
     * @test
     *
     * @doesNotPerformAssertions
     */
    public function setDataWithEmptyArrayIsAllowed(): void
    {
        $this->subject->setData([]);
    }

    /**
     * @test
     */
    public function getAfterSetDataReturnsTheSetValue(): void
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
    public function setDataCalledTwoTimesDoesNotFail(): void
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
    public function getArrayKeysWithEmptyKeyReturnsKeysOfDataArray(): void
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
    public function getArrayKeysForInexistentKeyReturnEmptyArray(): void
    {
        self::assertSame(
            [],
            $this->subject->getArrayKeys('key')
        );
    }

    /**
     * @test
     */
    public function getArrayKeysForKeyOfStringDataItemReturnsEmptyArray(): void
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
    public function getArrayKeysForKeyOfDataItemWithOneArrayElementReturnsKeyOfArrayElement(): void
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
    public function getArrayKeysForKeyOfDataItemWithTwoArrayElementsReturnsKeysOfArrayElements(): void
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
    public function getAsMultidimensionalArrayReturnsMultidimensionalArray(): void
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
    public function getAsMultidimensionalArrayForInexistentKeyReturnsEmptyArray(): void
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
    public function getAsMultidimensionalArrayForStringReturnsEmptyArray(): void
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
    public function getAsMultidimensionalArrayForIntegerReturnsEmptyArray(): void
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
    public function getAsMultidimensionalArrayForFloatReturnsEmptyArray(): void
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
