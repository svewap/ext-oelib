<?php

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndUserTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Model_BackEndUser
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Model_BackEndUser();
    }

    ///////////////////////////////////////////
    // Tests concerning getting the user name
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function getUserNameForEmptyUserNameReturnsEmptyString()
    {
        $this->subject->setData(['username' => '']);

        self::assertSame(
            '',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameForNonEmptyUserNameReturnsUserName()
    {
        $this->subject->setData(['username' => 'johndoe']);

        self::assertSame(
            'johndoe',
            $this->subject->getUserName()
        );
    }

    //////////////////////////////////////
    // Tests concerning getting the name
    //////////////////////////////////////

    /**
     * @test
     */
    public function getNameForNonEmptyNameReturnsName()
    {
        $this->subject->setData(['realName' => 'John Doe']);

        self::assertSame(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForEmptyNameReturnsEmptyString()
    {
        $this->subject->setData(['realName' => '']);

        self::assertSame(
            '',
            $this->subject->getName()
        );
    }

    //////////////////////////////////////////////////////
    // Tests concerning setting and getting the language
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getLanguageForNonEmptyLanguageReturnsLanguageKey()
    {
        $this->subject->setData(['lang' => 'de']);

        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForEmptyLanguageKeyReturnsDefault()
    {
        $this->subject->setData(['lang' => '']);

        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForLanguageSetInUserConfigurationReturnsThisLanguage()
    {
        $this->subject->setData(['uc' => serialize(['lang' => 'de'])]);

        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForSetDefaultLanguageAndLanguageSetInUserConfigurationReturnsLanguageFromConfiguration()
    {
        $this->subject->setData(['uc' => serialize(['lang' => 'fr'])]);
        $this->subject->setDefaultLanguage('de');

        self::assertSame(
            'fr',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForSetDefaultLanguageAndEmptyLanguageSetInUserConfigurationReturnsDefaultLanguage()
    {
        $this->subject->setData(['uc' => serialize(['lang' => ''])]);
        $this->subject->setDefaultLanguage('fr');

        self::assertSame(
            'fr',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getDefaultLanguageSetsLanguage()
    {
        $this->subject->setDefaultLanguage('de');

        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setDefaultLanguageWithDefaultSetsLanguage()
    {
        $this->subject->setDefaultLanguage('default');

        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setDefaultLanguageWithEmptyKeyThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$language must not be empty.'
        );

        $this->subject->setDefaultLanguage('');
    }

    /**
     * @test
     */
    public function hasLanguageWithoutLanguageReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithDefaultLanguageSetReturnsFalse()
    {
        $this->subject->setData([]);
        $this->subject->setDefaultLanguage('default');

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithNonEmptyLanguageReturnsTrue()
    {
        $this->subject->setData(['lang' => 'de']);

        self::assertTrue(
            $this->subject->hasLanguage()
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning getting the e-mail address
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getEmailAddressForEmptyEmailReturnsEmptyString()
    {
        $this->subject->setData(['email' => '']);

        self::assertSame(
            '',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressForNonEmptyEmailReturnsEmail()
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertSame(
            'john@doe.com',
            $this->subject->getEmailAddress()
        );
    }

    ///////////////////////////////
    // Tests concerning getGroups
    ///////////////////////////////

    /**
     * @test
     */
    public function getGroupsReturnsListFromUserGroupField()
    {
        $groups = new \Tx_Oelib_List();

        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            $groups,
            $this->subject->getGroups()
        );
    }
}
