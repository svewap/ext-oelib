<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\DataStructures\AbstractObjectWithPublicAccessors;
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
     * @var array
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

    protected function setUp()
    {
        /** @var ConfigurationProxy $subject */
        $subject = ConfigurationProxy::getInstance('oelib');
        // ensures the same configuration at the beginning of each test
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['oelib'] = \serialize($this->testConfiguration);
        $subject->retrieveConfiguration();
        $this->subject = $subject;
    }

    protected function tearDown()
    {
        ConfigurationProxy::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isPublicObjectWithAccessors()
    {
        self::assertInstanceOf(AbstractObjectWithPublicAccessors::class, $this->subject);
    }

    /**
     * @test
     */
    public function implementsConfigurationInterface()
    {
        self::assertInstanceOf(Configuration::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInstanceReturnsProxyInstance()
    {
        self::assertInstanceOf(ConfigurationProxy::class, ConfigurationProxy::getInstance('oelib'));
    }

    /**
     * @test
     */
    public function getInstanceThrowsExceptionIfNoExtensionKeyGiven()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension key was not set.');
        $this->expectExceptionCode(1331318826);

        ConfigurationProxy::getInstance('');
    }

    /**
     * @test
     */
    public function getInstanceReturnsTheSameObjectWhenCalledForTheSameClass()
    {
        self::assertSame(ConfigurationProxy::getInstance('oelib'), ConfigurationProxy::getInstance('oelib'));
    }

    /**
     * @test
     */
    public function instantiateOfAnotherProxyCreatesNewObject()
    {
        $otherConfiguration = ConfigurationProxy::getInstance('other_extension');

        self::assertNotSame($this->subject, $otherConfiguration);
    }

    /**
     * @test
     */
    public function getCompleteConfigurationReturnsAllTestConfigurationData()
    {
        self::assertSame(
            $this->testConfiguration,
            $this->subject->getCompleteConfiguration()
        );
    }

    /**
     * @test
     */
    public function retrieveConfigurationForNoConfigurationReturnsEmptyArray()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['oelib']);

        $this->subject->retrieveConfiguration();

        self::assertSame([], $this->subject->getCompleteConfiguration());
    }

    /**
     * @test
     */
    public function retrieveConfigurationIfThereIsNoneAndSetNewConfigurationValue()
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['oelib']);
        $this->subject->retrieveConfiguration();
        $this->subject->setAsString('testValue', 'foo');

        self::assertSame(
            'foo',
            $this->subject->getAsString('testValue')
        );
    }

    /**
     * @test
     */
    public function instantiateAnotherProxyAndSetValueNotAffectsThisFixture()
    {
        /** @var ConfigurationProxy $otherConfiguration */
        $otherConfiguration = ConfigurationProxy::getInstance('other_extension');
        $otherConfiguration->setAsString('testValue', 'foo');

        self::assertSame(
            'foo',
            $otherConfiguration->getAsString('testValue')
        );

        self::assertSame(
            $this->testConfiguration,
            $this->subject->getCompleteConfiguration()
        );
    }

    /**
     * @test
     */
    public function setInstanceWithEmptyExtensionKeyThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The extension key must not be empty.');
        $this->expectExceptionCode(1612091700);

        ConfigurationProxy::setInstance('', new DummyConfiguration());
    }

    /**
     * @test
     */
    public function setInstanceSetsInstanceForTheGivenExtensionKey()
    {
        $extensionKey = 'greenery';
        $instance = new DummyConfiguration();

        ConfigurationProxy::setInstance($extensionKey, $instance);

        self::assertSame($instance, ConfigurationProxy::getInstance($extensionKey));
    }

    /**
     * @test
     */
    public function setInstanceOverwritesInstanceForTheGivenExtensionKey()
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
    public function setInstanceNotSetsInstanceForTheOtherExtensionKey()
    {
        $instance = new DummyConfiguration();

        ConfigurationProxy::setInstance('greenery', $instance);

        self::assertNotSame($instance, ConfigurationProxy::getInstance('shrubbery'));
    }
}
