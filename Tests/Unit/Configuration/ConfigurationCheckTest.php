<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\ConfigurationCheck;
use OliverKlee\Oelib\Interfaces\ConfigurationCheckable;
use OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures\DummyObjectToCheck;

/**
 * @covers \OliverKlee\Oelib\Configuration\ConfigurationCheck
 */
class ConfigurationCheckTest extends UnitTestCase
{
    /**
     * @var ConfigurationCheck configuration check object to be tested
     */
    private $subject = null;

    /**
     * @var DummyObjectToCheck
     */
    private $objectToCheck = null;

    protected function setUp()
    {
        parent::setUp();

        $this->objectToCheck = new DummyObjectToCheck(
            [
                'emptyString' => '',
                'nonEmptyString' => 'foo',
                'validEmail' => 'any-address@valid-email.org',
                'existingColumn' => 'title',
                'inexistentColumn' => 'does_not_exist',
            ]
        );
        $this->subject = new ConfigurationCheck($this->objectToCheck);
    }

    // Tests concerning the basics

    /**
     * @test
     */
    public function objectToCheckIsCheckable()
    {
        self::assertInstanceOf(
            ConfigurationCheckable::class,
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

    // Tests concerning values to check

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

    /**
     * @test
     */
    public function checkIsValidDefaultFromEmailAddressForValidAddressMarksItAsValid()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'oliver@example.com';
        $this->subject->checkIsValidDefaultFromEmailAddress();

        self::assertSame('', $this->subject->getRawMessage());
    }

    /**
     * @return array[]
     */
    public function invalidEmailDataProvider(): array
    {
        return [
            'null' => [null],
            'empty string' => [''],
            'invalid email address' => ['bitouz6tz1432zwerds'],
        ];
    }

    /**
     * @test
     *
     * @param mixed $emailAddress
     *
     * @dataProvider invalidEmailDataProvider
     */
    public function checkIsValidDefaultFromEmailAddressForInvalidAddressMarksItAsInvalid($emailAddress)
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $emailAddress;

        $this->subject->checkIsValidDefaultFromEmailAddress();

        self::assertNotSame('', $this->subject->getRawMessage());
    }
}
