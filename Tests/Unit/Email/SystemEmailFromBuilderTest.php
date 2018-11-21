<?php

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Email\GeneralEmailRole;
use OliverKlee\Oelib\Email\SystemEmailFromBuilder;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class SystemEmailFromBuilderTest extends UnitTestCase
{
    /**
     * @var SystemEmailFromBuilder
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new SystemEmailFromBuilder();
    }

    /**
     * @test
     */
    public function canBuildForEmptyAddressAndNameReturnsFalse()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        static::assertFalse($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNullAddressAndNameReturnsFalse()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = null;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = null;

        static::assertFalse($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNonEmptyValidAddressAndEmptyNameReturnsTrue()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'admin@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        static::assertTrue($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNonEmptyValidAddressAndNullNameReturnsTrue()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = 'admin@example.com';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = null;

        static::assertTrue($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function canBuildForNonEmptyInvalidAddressAndEmptyNameReturnsFalse()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '78345uirefdx';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        static::assertFalse($this->subject->canBuild());
    }

    /**
     * @test
     */
    public function buildForInvalidDataThrowsException()
    {
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = '';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = '';

        $this->setExpectedException(\UnexpectedValueException::class);

        $this->subject->build();
    }

    /**
     * @test
     */
    public function buildForValidDataReturnSystemEmailSubjectWithGivenData()
    {
        $emailAddress = 'elena@example.com';
        $name = 'Elena Alene';
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress'] = $emailAddress;
        $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName'] = $name;

        $result = $this->subject->build();

        static::assertInstanceOf(GeneralEmailRole::class, $result);
        static::assertSame($emailAddress, $result->getEmailAddress());
        static::assertSame($name, $result->getName());
    }
}
