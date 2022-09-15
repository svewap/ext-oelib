<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Interfaces\ConvertableToMimeAddress;
use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Model\BackEndUserGroup;
use Symfony\Component\Mime\Address;

/**
 * @covers \OliverKlee\Oelib\Model\BackEndUser
 */
final class BackEndUserTest extends UnitTestCase
{
    /**
     * @var BackEndUser
     */
    private $subject = null;

    protected function setUp(): void
    {
        $this->subject = new BackEndUser();
    }

    /**
     * @test
     */
    public function implementsMailRole(): void
    {
        self::assertInstanceOf(MailRole::class, $this->subject);
    }

    ///////////////////////////////////////////
    // Tests concerning getting the username
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function getUserNameForEmptyUserNameReturnsEmptyString(): void
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
    public function getUserNameForNonEmptyUserNameReturnsUserName(): void
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
    public function getNameForNonEmptyNameReturnsName(): void
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
    public function getNameForEmptyNameReturnsEmptyString(): void
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
    public function getLanguageForNonEmptyLanguageReturnsLanguageKey(): void
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
    public function getLanguageForEmptyLanguageKeyReturnsDefault(): void
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
    public function getLanguageForLanguageSetInUserConfigurationReturnsThisLanguage(): void
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
    public function getLanguageForSetDefaultAndLanguageInUserConfigurationReturnsLanguageFromConfiguration(): void
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
    public function getLanguageForSetDefaultLanguageAndEmptyLanguageSetInUserConfigurationReturnsDefaultLanguage(): void
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
    public function getDefaultLanguageSetsLanguage(): void
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
    public function setDefaultLanguageWithDefaultSetsLanguage(): void
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
    public function setDefaultLanguageWithEmptyKeyThrowsException(): void
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
    public function hasLanguageWithoutLanguageReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithDefaultLanguageSetReturnsFalse(): void
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
    public function hasLanguageWithNonEmptyLanguageReturnsTrue(): void
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
    public function getEmailAddressForEmptyEmailReturnsEmptyString(): void
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
    public function getEmailAddressForNonEmptyEmailReturnsEmail(): void
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertSame(
            'john@doe.com',
            $this->subject->getEmailAddress()
        );
    }

    // Tests concerning the MIME email address

    /**
     * @test
     */
    public function implementsConvertableToMimeAddress(): void
    {
        self::assertInstanceOf(ConvertableToMimeAddress::class, $this->subject);
    }

    /**
     * @test
     */
    public function canBuildMimeAddress(): void
    {
        $this->subject->setData(['email' => 'tina@example.com']);

        $address = $this->subject->toMimeAddress();
        self::assertInstanceOf(Address::class, $address);
    }

    /**
     * @test
     */
    public function mimeAddressHasEmailAddress(): void
    {
        $emailAddress = 'jade@example.com';
        $this->subject->setData(['email' => $emailAddress]);

        $address = $this->subject->toMimeAddress();
        self::assertSame($emailAddress, $address->getAddress());
    }

    /**
     * @test
     */
    public function mimeAddressHasName(): void
    {
        $name = 'Max';
        $this->subject->setData(['realName' => $name, 'email' => 'tina@example.com']);

        $address = $this->subject->toMimeAddress();
        self::assertSame($name, $address->getName());
    }

    // Tests concerning getGroups

    /**
     * @test
     */
    public function getGroupsReturnsListFromUserGroupField(): void
    {
        /** @var Collection<BackEndUserGroup> $expectedGroups */
        $expectedGroups = new Collection();

        $this->subject->setData(['usergroup' => $expectedGroups]);

        /** @var Collection<BackEndUserGroup> $actualGroups */
        $actualGroups = $this->subject->getGroups();
        self::assertSame($expectedGroups, $actualGroups);
    }
}
