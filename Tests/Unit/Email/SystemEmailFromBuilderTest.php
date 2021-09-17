<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Email\GeneralEmailRole;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;

class SystemEmailFromBuilderTest extends UnitTestCase
{
    /**
     * @var SystemEmailFromBuilder
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new SystemEmailFromBuilder();
    }

    /**
     * @test
     */
    public function canBuildForEmptyAddressAndNameReturnsFalse(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        self::assertFalse($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNullAddressAndNameReturnsFalse(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = null;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = null;

        self::assertFalse($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNonEmptyValidAddressAndEmptyNameReturnsTrue(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'admin@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        self::assertTrue($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNonEmptyValidAddressAndNullNameReturnsTrue(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'admin@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = null;

        self::assertTrue($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNonEmptyInvalidAddressAndEmptyNameReturnsFalse(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '78345uirefdx';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        self::assertFalse($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function buildForInvalidDataThrowsException(): void
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->expectException(\UnexpectedValueException::class);

        $this->subject->build();
    }

    /**
     * @test
     */
    public function buildForValidDataReturnSystemEmailSubjectWithGivenData(): void
    {
        $emailAddress = 'elena@example.com';
        $name = 'Elena Alene';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $emailAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $name;

        $result = $this->subject->build();

        self::assertInstanceOf(GeneralEmailRole::class, $result);
        self::assertSame($emailAddress, $result->getEmailAddress());
        self::assertSame($name, $result->getName());
    }
}
