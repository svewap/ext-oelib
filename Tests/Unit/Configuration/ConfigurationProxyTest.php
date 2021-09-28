<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractReadOnlyObjectWithPublicAccessors;
use OliverKlee\Oelib\Interfaces\Configuration;

/**
 * @covers \OliverKlee\Oelib\Configuration\ConfigurationProxy
 */
class ConfigurationProxyTest extends UnitTestCase
{
    /**
     * @var ConfigurationProxy
     */
    private $subject;

    /**
     * @var array<string, string|int>
     */
    private $testConfiguration = [
        'testValueString' => 'foo',
        'testValueEmptyString' => '',
        'testValuePositiveInteger' => 2,
        'testValueNegativeInteger' => -1,
        'testValueZeroInteger' => 0,
        'testValueTrue' => 1,
        'testValueFalse' => 0,
    ];

    protected function setUp(): void
    {
        /** @var ConfigurationProxy $subject */
        $subject = ConfigurationProxy::getInstance('oelib');
        // ensures the same configuration at the beginning of each test
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['oelib'] = $this->testConfiguration;
        $subject->retrieveConfiguration();
        $this->subject = $subject;
    }

    protected function tearDown(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['oelib'] = [];
        ConfigurationProxy::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isPublicReadOnlyObjectWithAccessors(): void
    {
        self::assertInstanceOf(AbstractReadOnlyObjectWithPublicAccessors::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsConfigurationInterface(): void
    {
        self::assertInstanceOf(Configuration::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInstanceReturnsProxyInstance(): void
    {
        self::assertInstanceOf(ConfigurationProxy::class, ConfigurationProxy::getInstance('oelib'));
    }

    /**
     * @test
     */
    public function getInstanceThrowsExceptionIfNoExtensionKeyGiven(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension key was not set.');
        $this->expectExceptionCode(1331318826);

        ConfigurationProxy::getInstance('');
    }

    /**
     * @test
     */
    public function getInstanceReturnsTheSameObjectWhenCalledForTheSameClass(): void
    {
        self::assertSame(ConfigurationProxy::getInstance('oelib'), ConfigurationProxy::getInstance('oelib'));
    }

    /**
     * @test
     */
    public function instantiateOfAnotherProxyCreatesNewObject(): void
    {
        $otherConfiguration = ConfigurationProxy::getInstance('other_extension');

        self::assertNotSame($this->subject, $otherConfiguration);
    }

    /**
     * @test
     */
    public function getCompleteConfigurationReturnsAllTestConfigurationData(): void
    {
        self::assertSame(
            $this->testConfiguration,
            $this->subject->getCompleteConfiguration()
        );
    }

    /**
     * @test
     */
    public function retrieveConfigurationForNoConfigurationSetsEmptyArray(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXTENSIONS']['oelib']);

        $this->subject->retrieveConfiguration();

        self::assertSame([], $this->subject->getCompleteConfiguration());
    }

    /**
     * @test
     */
    public function setInstanceWithEmptyExtensionKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension key must not be empty.');
        $this->expectExceptionCode(1612091700);

        ConfigurationProxy::setInstance('', new DummyConfiguration());
    }

    /**
     * @test
     */
    public function setInstanceSetsInstanceForTheGivenExtensionKey(): void
    {
        $extensionKey = 'greenery';
        $instance = new DummyConfiguration();

        ConfigurationProxy::setInstance($extensionKey, $instance);

        self::assertSame($instance, ConfigurationProxy::getInstance($extensionKey));
    }

    /**
     * @test
     */
    public function setInstanceOverwritesInstanceForTheGivenExtensionKey(): void
    {
        $extensionKey = 'greenery';
        $instance1 = new DummyConfiguration();
        ConfigurationProxy::setInstance($extensionKey, $instance1);
        $instance2 = new DummyConfiguration();
        ConfigurationProxy::setInstance($extensionKey, $instance2);

        self::assertSame($instance2, ConfigurationProxy::getInstance($extensionKey));
    }

    /**
     * @test
     */
    public function setInstanceNotSetsInstanceForTheOtherExtensionKey(): void
    {
        $instance = new DummyConfiguration();

        ConfigurationProxy::setInstance('greenery', $instance);

        self::assertNotSame($instance, ConfigurationProxy::getInstance('shrubbery'));
    }
}
