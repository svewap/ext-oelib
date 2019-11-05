<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class ConfigurationProxyTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_ConfigurationProxy
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
        $this->subject = \Tx_Oelib_ConfigurationProxy::getInstance('oelib');
        // ensures the same configuration at the beginning of each test
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['oelib'] = serialize($this->testConfiguration);
        $this->subject->retrieveConfiguration();
    }

    protected function tearDown()
    {
        \Tx_Oelib_ConfigurationProxy::purgeInstances();
        parent::tearDown();
    }

    /**
     * @test
     */
    public function getInstanceReturnsObject()
    {
        self::assertInternalType(
            'object',
            $this->subject
        );
    }

    /**
     * @test
     */
    public function getInstanceThrowsExceptionIfNoExtensionKeyGiven()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            'The extension key was not set.'
        );
        \Tx_Oelib_ConfigurationProxy::getInstance('');
    }

    /**
     * @test
     */
    public function getInstanceReturnsTheSameObjectWhenCalledInTheSameClass()
    {
        self::assertSame(
            $this->subject,
            \Tx_Oelib_ConfigurationProxy::getInstance('oelib')
        );
    }

    /**
     * @test
     */
    public function instantiateOfAnotherProxyCreatesNewObject()
    {
        $otherConfiguration = \Tx_Oelib_ConfigurationProxy::getInstance('other_extension');

        self::assertNotSame(
            $this->subject,
            $otherConfiguration
        );
    }

    /**
     * @test
     */
    public function extendsPublicObject()
    {
        self::assertInstanceOf(
            \Tx_Oelib_PublicObject::class,
            $this->subject
        );
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
        $otherConfiguration = \Tx_Oelib_ConfigurationProxy::getInstance('other_extension');
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
}
