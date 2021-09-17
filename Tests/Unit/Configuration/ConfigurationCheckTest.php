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

    protected function setUp(): void
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
    public function objectToCheckIsCheckable(): void
    {
        self::assertInstanceOf(
            ConfigurationCheckable::class,
            $this->objectToCheck
        );
    }

    /**
     * @test
     */
    public function checkContainsNamespaceInErrorMessage(): void
    {
        $this->subject->checkForNonEmptyString('', false, '', '');

        self::assertStringContainsString(
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
    public function setFlavorReturnsFlavor(): void
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
    public function checkForNonEmptyStringWithNonEmptyString(): void
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
    public function checkForNonEmptyStringWithEmptyString(): void
    {
        $this->subject->checkForNonEmptyString('emptyString', false, '', '');

        self::assertStringContainsString(
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
    public function checkIsValidEmailOrEmptyWithEmptyString(): void
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
    public function checkIsValidEmailOrEmptyWithValidEmail(): void
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
    public function checkIsValidEmailOrEmptyWithInvalidEmail(): void
    {
        $this->subject->checkIsValidEmailOrEmpty('nonEmptyString', false, '', false, '');

        self::assertStringContainsString(
            'nonEmptyString',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIsValidEmailNotEmptyWithEmptyString(): void
    {
        $this->subject->checkIsValidEmailNotEmpty('emptyString', false, '', false, '');

        self::assertStringContainsString(
            'emptyString',
            $this->subject->getRawMessage()
        );
    }

    /**
     * @test
     */
    public function checkIsValidEmailNotEmptyWithValidEmail(): void
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
    public function checkIsValidDefaultFromEmailAddressForValidAddressMarksItAsValid(): void
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
     * @dataProvider invalidEmailDataProvider
     */
    public function checkIsValidDefaultFromEmailAddressForInvalidAddressMarksItAsInvalid(?string $emailAddress): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $emailAddress;

        $this->subject->checkIsValidDefaultFromEmailAddress();

        self::assertNotSame('', $this->subject->getRawMessage());
    }
}
