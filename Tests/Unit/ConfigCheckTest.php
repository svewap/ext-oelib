<?php

use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class Tx_Oelib_Tests_Unit_ConfigCheckTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_ConfigCheck configuration check object to be tested
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck dummy object to be checked by the configuration check object
     */
    private $objectToCheck = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var bool
     */
    protected $deprecationLogEnabledBackup = false;

    protected function setUp()
    {
        $this->deprecationLogEnabledBackup = $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'];

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_oelib');
        $this->testingFramework->createFakeFrontEnd();

        $this->objectToCheck = new \Tx_Oelib_Tests_Unit_Fixtures_DummyObjectToCheck(
            [
                'emptyString' => '',
                'nonEmptyString' => 'foo',
                'validEmail' => 'any-address@valid-email.org',
                'existingColumn' => 'title',
                'inexistentColumn' => 'does_not_exist',
            ]
        );
        $this->subject = new \Tx_Oelib_ConfigCheck($this->objectToCheck);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['enableDeprecationLog'] = $this->deprecationLogEnabledBackup;
    }

    ///////////////////////
    // Utility functions.
    ///////////////////////

    /**
     * Returns the current front-end instance.
     *
     * @return TypoScriptFrontendController
     */
    private function getFrontEndController()
    {
        return $GLOBALS['TSFE'];
    }

    /**
     * Sets the configuration value for the locale to $localeKey.
     *
     * @param string $localeKey
     *        key for the locale, to receive a non-configured locale, provide
     *        an empty string
     *
     * @return void
     */
    private function setConfigurationForLocale($localeKey)
    {
        $this->getFrontEndController()->config['config']['locale_all'] = $localeKey;
    }

    /////////////////////////////////////
    // Tests for the utility functions.
    /////////////////////////////////////

    /**
     * @test
     */
    public function setConfigurationForLocaleToANonEmptyValue()
    {
        $this->setConfigurationForLocale('foo');

        self::assertSame(
            'foo',
            $this->getFrontEndController()->config['config']['locale_all']
        );
    }

    /**
     * @test
     */
    public function setConfigurationForLocaleToAnEmptyString()
    {
        $this->setConfigurationForLocale('');

        self::assertSame(
            '',
            $this->getFrontEndController()->config['config']['locale_all']
        );
    }

    /*
     * Tests concerning the basics
     */

    /**
     * @test
     */
    public function objectToCheckIsCheckable()
    {
        self::assertInstanceOf(
            \Tx_Oelib_Interface_ConfigurationCheckable::class,
            $this->objectToCheck
        );
    }

    /**
     * @test
     */
    public function checkContainsNamespaceInErrorMessage()
    {
        $this->subject->checkForNonEmptyString('', false, '', '');

        self::assertContains(
            'plugin.tx_oelib_test.',
            $this->subject->getRawMessage()
        );
    }

    /////////////////////////////////
    // Tests concerning the flavor.
    /////////////////////////////////

    /**
     * @test
     */
    public function setFlavorReturnsFlavor()
    {
        $this->subject->setFlavor('foo');

        self::assertSame(
            'foo',
            $this->subject->getFlavor()
        );
    }

    //////////////////////////////////////
    // Tests concerning values to check.
    //////////////////////////////////////

    /**
     * @test
     */
    public function checkForNonEmptyStringWithNonEmptyString()
    {
        $this->subject->checkForNonEmptyString('nonEmptyString', false, '', '');

        self::assertSame(
            '',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkForNonEmptyStringWithEmptyString()
    {
        $this->subject->checkForNonEmptyString('emptyString', false, '', '');

        self::assertContains(
            'emptyString',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIfSingleInTableNotEmptyForValueNotInTableComplains()
    {
        $this->subject->checkIfSingleInTableNotEmpty(
            'inexistentColumn',
            false,
            '',
            '',
            'tx_oelib_test'
        );

        self::assertContains(
            'inexistentColumn',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIfSingleInTableNotEmptyForValueNotInTableNotComplains()
    {
        $this->subject->checkIfSingleInTableNotEmpty(
            'existingColumn',
            false,
            '',
            '',
            'tx_oelib_test'
        );

        self::assertSame(
            '',
            $this->subject->getRawMessage()
        );
    }

    ///////////////////////////////////////////////
    // Tests concerning the e-mail address check.
    ///////////////////////////////////////////////

    /**
     * @test
     */
    public function checkIsValidEmailOrEmptyWithEmptyString()
    {
        $this->subject->checkIsValidEmailOrEmpty('emptyString', false, '', false, '');

        self::assertSame(
            '',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIsValidEmailOrEmptyWithValidEmail()
    {
        $this->subject->checkIsValidEmailOrEmpty('validEmail', false, '', false, '');

        self::assertSame(
            '',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIsValidEmailOrEmptyWithInvalidEmail()
    {
        $this->subject->checkIsValidEmailOrEmpty('nonEmptyString', false, '', false, '');

        self::assertContains(
            'nonEmptyString',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIsValidEmailNotEmptyWithEmptyString()
    {
        $this->subject->checkIsValidEmailNotEmpty('emptyString', false, '', false, '');

        self::assertContains(
            'emptyString',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIsValidEmailNotEmptyWithValidEmail()
    {
        $this->subject->checkIsValidEmailNotEmpty('validEmail', false, '', false, '');

        self::assertSame(
            '',
            $this->subject->getRawMessage()
        );
    }
}
