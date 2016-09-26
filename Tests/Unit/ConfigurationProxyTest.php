<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class Tx_Oelib_Tests_Unit_ConfigurationProxyTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_ConfigurationProxy
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
        $this->subject = Tx_Oelib_ConfigurationProxy::getInstance('oelib');
        // ensures the same configuration at the beginning of each test
        $GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['oelib']
            = serialize($this->testConfiguration);
        $this->subject->retrieveConfiguration();
    }

    protected function tearDown()
    {
        Tx_Oelib_ConfigurationProxy::purgeInstances();
    }

    /**
     * @test
     */
    public function getInstanceReturnsObject()
    {
        self::assertTrue(
            is_object($this->subject)
        );
    }

    /**
     * @test
     */
    public function getInstanceThrowsExceptionIfNoExtensionKeyGiven()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            'The extension key was not set.'
        );
        Tx_Oelib_ConfigurationProxy::getInstance('');
    }

    /**
     * @test
     */
    public function getInstanceReturnsTheSameObjectWhenCalledInTheSameClass()
    {
        self::assertSame(
            $this->subject,
            Tx_Oelib_ConfigurationProxy::getInstance('oelib')
        );
    }

    /**
     * @test
     */
    public function instantiateOfAnotherProxyCreatesNewObject()
    {
        $otherConfiguration = Tx_Oelib_ConfigurationProxy::getInstance('other_extension');

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
            Tx_Oelib_PublicObject::class,
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
        $otherConfiguration = Tx_Oelib_ConfigurationProxy::getInstance('other_extension');
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
