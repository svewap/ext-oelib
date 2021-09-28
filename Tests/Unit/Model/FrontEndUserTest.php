<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;
use OliverKlee\Oelib\Model\FrontEndUserGroup;

class FrontEndUserTest extends UnitTestCase
{
    /**
     * @var FrontEndUser
     */
    private $subject = null;

    /**
     * @var int a backup of $GLOBALS['EXEC_TIME']
     */
    private $globalExecTimeBackup = 0;

    /**
     * @var array<string, mixed>
     */
    private $tcaBackup = [];

    protected function setUp(): void
    {
        $this->subject = new FrontEndUser();

        $this->globalExecTimeBackup = $GLOBALS['EXEC_TIME'];
        $this->tcaBackup = $GLOBALS['TCA']['fe_users'] ?? [];
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $GLOBALS['TCA']['fe_users'] = $this->tcaBackup;
        $GLOBALS['EXEC_TIME'] = $this->globalExecTimeBackup;
    }

    private function removeGenderField(): void
    {
        unset($GLOBALS['TCA']['fe_users']['columns']['gender']);
    }

    private function enableGenderField(): void
    {
        $GLOBALS['TCA']['fe_users']['columns']['gender'] = ['config' => ['type' => 'radio']];
    }

    // Tests concerning the user name

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

    /**
     * @test
     */
    public function setUserNameSetsUserName(): void
    {
        $this->subject->setUserName('foo_bar');

        self::assertSame(
            'foo_bar',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function setUserNameWithEmptyUserNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setUserName('');
    }

    // Tests concerning the password

    /**
     * @test
     */
    public function getPasswordInitiallyReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            '',
            $this->subject->getPassword()
        );
    }

    /**
     * @test
     */
    public function getPasswordReturnsPassword(): void
    {
        $this->subject->setData(['password' => 'kasfdjklsdajk']);

        self::assertSame(
            'kasfdjklsdajk',
            $this->subject->getPassword()
        );
    }

    /**
     * @test
     */
    public function setPasswordSetsPassword(): void
    {
        $this->subject->setPassword('kljvasgd24vsga354');

        self::assertSame(
            'kljvasgd24vsga354',
            $this->subject->getPassword()
        );
    }

    /**
     * @test
     */
    public function setPasswordWithEmptyPasswordThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->setPassword('');
    }

    // Tests concerning the name

    /**
     * @test
     */
    public function hasNameForEmptyNameLastNameAndFirstNameReturnsFalse(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => '',
                'last_name' => '',
            ]
        );

        self::assertFalse(
            $this->subject->hasName()
        );
    }

    /**
     * @test
     */
    public function hasNameForNonEmptyUserReturnsFalse(): void
    {
        $this->subject->setData(
            [
                'username' => 'johndoe',
            ]
        );

        self::assertFalse(
            $this->subject->hasName()
        );
    }

    /**
     * @test
     */
    public function hasNameForNonEmptyNameReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'name' => 'John Doe',
                'first_name' => '',
                'last_name' => '',
            ]
        );

        self::assertTrue(
            $this->subject->hasName()
        );
    }

    /**
     * @test
     */
    public function hasNameForNonEmptyFirstNameReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => 'John',
                'last_name' => '',
            ]
        );

        self::assertTrue(
            $this->subject->hasName()
        );
    }

    /**
     * @test
     */
    public function hasNameForNonEmptyLastNameReturnsTrue(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => '',
                'last_name' => 'Doe',
            ]
        );

        self::assertTrue(
            $this->subject->hasName()
        );
    }

    /**
     * @test
     */
    public function getNameForNonEmptyNameReturnsName(): void
    {
        $this->subject->setData(
            [
                'name' => 'John Doe',
            ]
        );

        self::assertSame(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForNonEmptyNameFirstNameAndLastNameReturnsName(): void
    {
        $this->subject->setData(
            [
                'name' => 'John Doe',
                'first_name' => 'Peter',
                'last_name' => 'Pan',
            ]
        );

        self::assertSame(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForEmptyNameAndNonEmptyFirstAndLastNameReturnsFirstAndLastName(): void
    {
        $this->subject->setData(
            [
                'name' => '',
                'first_name' => 'Peter',
                'last_name' => 'Pan',
            ]
        );

        self::assertSame(
            'Peter Pan',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForNonEmptyFirstAndLastNameAndNonEmptyUserNameReturnsFirstAndLastName(): void
    {
        $this->subject->setData(
            [
                'first_name' => 'Peter',
                'last_name' => 'Pan',
                'username' => 'johndoe',
            ]
        );

        self::assertSame(
            'Peter Pan',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForEmptyFirstNameAndNonEmptyLastAndUserNameReturnsLastName(): void
    {
        $this->subject->setData(
            [
                'first_name' => '',
                'last_name' => 'Pan',
                'username' => 'johndoe',
            ]
        );

        self::assertSame(
            'Pan',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForEmptyLastNameAndNonEmptyFirstAndUserNameReturnsFirstName(): void
    {
        $this->subject->setData(
            [
                'first_name' => 'Peter',
                'last_name' => '',
                'username' => 'johndoe',
            ]
        );

        self::assertSame(
            'Peter',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForEmptyFirstAndLastNameAndNonEmptyUserNameReturnsUserName(): void
    {
        $this->subject->setData(
            [
                'first_name' => '',
                'last_name' => '',
                'username' => 'johndoe',
            ]
        );

        self::assertSame(
            'johndoe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function setNameSetsFullName(): void
    {
        $this->subject->setName('Alfred E. Neumann');

        self::assertSame(
            'Alfred E. Neumann',
            $this->subject->getName()
        );
    }

    // Tests concerning getting the company

    /**
     * @test
     */
    public function hasCompanyForEmptyCompanyReturnsFalse(): void
    {
        $this->subject->setData(['company' => '']);

        self::assertFalse(
            $this->subject->hasCompany()
        );
    }

    /**
     * @test
     */
    public function hasCompanyForNonEmptyCompanyReturnsTrue(): void
    {
        $this->subject->setData(['company' => 'Test Inc.']);

        self::assertTrue(
            $this->subject->hasCompany()
        );
    }

    /**
     * @test
     */
    public function getCompanyForEmptyCompanyReturnsEmptyString(): void
    {
        $this->subject->setData(['company' => '']);

        self::assertSame(
            '',
            $this->subject->getCompany()
        );
    }

    /**
     * @test
     */
    public function getCompanyForNonEmptyCompanyReturnsCompany(): void
    {
        $this->subject->setData(['company' => 'Test Inc.']);

        self::assertSame(
            'Test Inc.',
            $this->subject->getCompany()
        );
    }

    /**
     * @test
     */
    public function setCompanySetsCompany(): void
    {
        $this->subject->setCompany('Test Inc.');

        self::assertSame(
            'Test Inc.',
            $this->subject->getCompany()
        );
    }

    // Tests concerning getting the street

    /**
     * @test
     */
    public function hasStreetForEmptyAddressReturnsFalse(): void
    {
        $this->subject->setData(['address' => '']);

        self::assertFalse(
            $this->subject->hasStreet()
        );
    }

    /**
     * @test
     */
    public function hasStreetForNonEmptyAddressReturnsTrue(): void
    {
        $this->subject->setData(['address' => 'Foo street 1']);

        self::assertTrue(
            $this->subject->hasStreet()
        );
    }

    /**
     * @test
     */
    public function getStreetForEmptyAddressReturnsEmptyString(): void
    {
        $this->subject->setData(['address' => '']);

        self::assertSame(
            '',
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function getStreetForNonEmptyAddressReturnsAddress(): void
    {
        $this->subject->setData(['address' => 'Foo street 1']);

        self::assertSame(
            'Foo street 1',
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function getStreetForMultilineAddressReturnsAddress(): void
    {
        $this->subject->setData(
            [
                'address' => "Foo street 1\nFloor 3",
            ]
        );

        self::assertSame(
            "Foo street 1\nFloor 3",
            $this->subject->getStreet()
        );
    }

    /**
     * @test
     */
    public function setStreetSetsStreet(): void
    {
        $street = 'Barber Street 42';
        $this->subject->setData([]);
        $this->subject->setStreet($street);

        self::assertSame(
            $street,
            $this->subject->getStreet()
        );
    }

    // Tests concerning the ZIP code

    /**
     * @test
     */
    public function hasZipForEmptyZipReturnsFalse(): void
    {
        $this->subject->setData(['zip' => '']);

        self::assertFalse(
            $this->subject->hasZip()
        );
    }

    /**
     * @test
     */
    public function hasZipForNonEmptyZipReturnsTrue(): void
    {
        $this->subject->setData(['zip' => '12345']);

        self::assertTrue(
            $this->subject->hasZip()
        );
    }

    /**
     * @test
     */
    public function getZipForEmptyZipReturnsEmptyString(): void
    {
        $this->subject->setData(['zip' => '']);

        self::assertSame(
            '',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function getZipForNonEmptyZipReturnsZip(): void
    {
        $this->subject->setData(['zip' => '12345']);

        self::assertSame(
            '12345',
            $this->subject->getZip()
        );
    }

    /**
     * @test
     */
    public function setZipSetsZip(): void
    {
        $zip = '12356';
        $this->subject->setData([]);
        $this->subject->setZip($zip);

        self::assertSame(
            $zip,
            $this->subject->getZip()
        );
    }

    // Tests concerning the city

    /**
     * @test
     */
    public function hasCityForEmptyCityReturnsFalse(): void
    {
        $this->subject->setData(['city' => '']);

        self::assertFalse(
            $this->subject->hasCity()
        );
    }

    /**
     * @test
     */
    public function hasCityForNonEmptyCityReturnsTrue(): void
    {
        $this->subject->setData(['city' => 'Test city']);

        self::assertTrue(
            $this->subject->hasCity()
        );
    }

    /**
     * @test
     */
    public function getCityForEmptyCityReturnsEmptyString(): void
    {
        $this->subject->setData(['city' => '']);

        self::assertSame(
            '',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function getCityForNonEmptyCityReturnsCity(): void
    {
        $this->subject->setData(['city' => 'Test city']);

        self::assertSame(
            'Test city',
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function setCitySetsCity(): void
    {
        $city = 'Köln';
        $this->subject->setData([]);
        $this->subject->setCity($city);

        self::assertSame(
            $city,
            $this->subject->getCity()
        );
    }

    /**
     * @test
     */
    public function getZipAndCityForNonEmptyZipAndCityReturnsZipAndCity(): void
    {
        $this->subject->setData(
            [
                'zip' => '12345',
                'city' => 'Test city',
            ]
        );

        self::assertSame(
            '12345 Test city',
            $this->subject->getZipAndCity()
        );
    }

    /**
     * @test
     */
    public function getZipAndCityForEmptyZipAndNonEmptyCityReturnsCity(): void
    {
        $this->subject->setData(
            [
                'zip' => '',
                'city' => 'Test city',
            ]
        );

        self::assertSame(
            'Test city',
            $this->subject->getZipAndCity()
        );
    }

    /**
     * @test
     */
    public function getZipAndGetCityForNonEmptyZipAndEmptyCityReturnsEmptyString(): void
    {
        $this->subject->setData(
            [
                'zip' => '12345',
                'city' => '',
            ]
        );

        self::assertSame(
            '',
            $this->subject->getZipAndCity()
        );
    }

    /**
     * @test
     */
    public function getZipAndGetCityForEmptyZipAndEmptyCityReturnsEmptyString(): void
    {
        $this->subject->setData(
            [
                'zip' => '',
                'city' => '',
            ]
        );

        self::assertSame(
            '',
            $this->subject->getZipAndCity()
        );
    }

    // Tests concerning the phone number

    /**
     * @test
     */
    public function hasPhoneNumberForEmptyPhoneReturnsFalse(): void
    {
        $this->subject->setData(['telephone' => '']);

        self::assertFalse(
            $this->subject->hasPhoneNumber()
        );
    }

    /**
     * @test
     */
    public function hasPhoneNumberForNonEmptyPhoneReturnsTrue(): void
    {
        $this->subject->setData(['telephone' => '1234 5678']);

        self::assertTrue(
            $this->subject->hasPhoneNumber()
        );
    }

    /**
     * @test
     */
    public function getPhoneNumberForEmptyPhoneReturnsEmptyString(): void
    {
        $this->subject->setData(['telephone' => '']);

        self::assertSame(
            '',
            $this->subject->getPhoneNumber()
        );
    }

    /**
     * @test
     */
    public function getPhoneNumberForNonEmptyPhoneReturnsPhone(): void
    {
        $this->subject->setData(['telephone' => '1234 5678']);

        self::assertSame(
            '1234 5678',
            $this->subject->getPhoneNumber()
        );
    }

    /**
     * @test
     */
    public function setPhoneNumberSetsPhoneNumber(): void
    {
        $phoneNumber = '+49 124 1234123';
        $this->subject->setData([]);
        $this->subject->setPhoneNumber($phoneNumber);

        self::assertSame(
            $phoneNumber,
            $this->subject->getPhoneNumber()
        );
    }

    // Tests concerning the e-mail address

    /**
     * @test
     */
    public function hasEmailAddressForEmptyEmailReturnsFalse(): void
    {
        $this->subject->setData(['email' => '']);

        self::assertFalse(
            $this->subject->hasEmailAddress()
        );
    }

    /**
     * @test
     */
    public function hasEmailAddressForNonEmptyEmailReturnsTrue(): void
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertTrue(
            $this->subject->hasEmailAddress()
        );
    }

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

    /**
     * @test
     */
    public function setEmailAddressSetsEmailAddress(): void
    {
        $this->subject->setEmailAddress('john@example.com');

        self::assertSame(
            'john@example.com',
            $this->subject->getEmailAddress()
        );
    }

    // Tests concerning getting the homepage

    /**
     * @test
     */
    public function hasHomepageForEmptyWwwReturnsFalse(): void
    {
        $this->subject->setData(['www' => '']);

        self::assertFalse(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function hasHomepageForNonEmptyWwwReturnsTrue(): void
    {
        $this->subject->setData(['www' => 'https://www.example.com']);

        self::assertTrue(
            $this->subject->hasHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepageForEmptyWwwReturnsEmptyString(): void
    {
        $this->subject->setData(['www' => '']);

        self::assertSame(
            '',
            $this->subject->getHomepage()
        );
    }

    /**
     * @test
     */
    public function getHomepageForNonEmptyWwwReturnsWww(): void
    {
        $this->subject->setData(['www' => 'https://www.example.com']);

        self::assertSame(
            'https://www.example.com',
            $this->subject->getHomepage()
        );
    }

    // Tests concerning getting the picture

    /**
     * @test
     */
    public function hasImageForEmptyImageReturnsFalse(): void
    {
        $this->subject->setData(['image' => '']);

        self::assertFalse(
            $this->subject->hasImage()
        );
    }

    /**
     * @test
     */
    public function hasImageForNonEmptyImageReturnsTrue(): void
    {
        $this->subject->setData(['image' => 'thats-me.jpg']);

        self::assertTrue(
            $this->subject->hasImage()
        );
    }

    /**
     * @test
     */
    public function getImageForEmptyImageReturnsEmptyString(): void
    {
        $this->subject->setData(['image' => '']);

        self::assertSame(
            '',
            $this->subject->getImage()
        );
    }

    /**
     * @test
     */
    public function getImageForNonEmptyImageReturnsImage(): void
    {
        $this->subject->setData(['image' => 'thats-me.jpg']);

        self::assertSame(
            'thats-me.jpg',
            $this->subject->getImage()
        );
    }

    // Tests concerning wantsHtmlEmail

    /**
     * @test
     */
    public function wantsHtmlEmailForMissingModuleSysDmailHtmlFieldReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->wantsHtmlEmail()
        );
    }

    /**
     * @test
     */
    public function wantsHtmlEmailForModuleSysDmailHtmlOneReturnsTrue(): void
    {
        $this->subject->setData(['module_sys_dmail_html' => 1]);

        self::assertTrue(
            $this->subject->wantsHtmlEmail()
        );
    }

    /**
     * @test
     */
    public function wantsHtmlEmailForModuleSysDmailHtmlZeroReturnsFalse(): void
    {
        $this->subject->setData(['module_sys_dmail_html' => 0]);

        self::assertFalse(
            $this->subject->wantsHtmlEmail()
        );
    }

    // Tests concerning the user groups

    /**
     * @test
     */
    public function getUserGroupsForReturnsUserGroups(): void
    {
        $userGroups = new Collection();

        $this->subject->setData(['usergroup' => $userGroups]);

        self::assertSame(
            $userGroups,
            $this->subject->getUserGroups()
        );
    }

    /**
     * @test
     */
    public function setUserGroupsSetsUserGroups(): void
    {
        /** @var Collection<FrontEndUserGroup> $userGroups */
        $userGroups = new Collection();

        $this->subject->setUserGroups($userGroups);

        self::assertSame(
            $userGroups,
            $this->subject->getUserGroups()
        );
    }

    /**
     * @test
     */
    public function addUserGroupAddsUserGroup(): void
    {
        /** @var Collection<FrontEndUserGroup> $userGroups */
        $userGroups = new Collection();
        $this->subject->setUserGroups($userGroups);

        $userGroup = new FrontEndUserGroup();
        $this->subject->addUserGroup($userGroup);

        self::assertTrue(
            $this->subject->getUserGroups()->contains($userGroup)
        );
    }

    // Test concerning hasGroupMembership

    /**
     * @test
     */
    public function hasGroupMembershipWithEmptyUidListThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->hasGroupMembership('');
    }

    /**
     * @test
     */
    public function hasGroupMembershipForUserOnlyInProvidedGroupReturnsTrue(): void
    {
        $userGroup = MapperRegistry::get(FrontEndUserGroupMapper::class)->getNewGhost();
        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->subject->hasGroupMembership((string)$userGroup->getUid())
        );
    }

    /**
     * @test
     */
    public function hasGroupMembershipForUserInProvidedGroupAndInAnotherReturnsTrue(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
        $userGroup = $groupMapper->getNewGhost();
        $list = new Collection();
        $list->add($groupMapper->getNewGhost());
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->subject->hasGroupMembership((string)$userGroup->getUid())
        );
    }

    /**
     * @test
     */
    public function hasGroupMembershipForUserInOneOfTheProvidedGroupsReturnsTrue(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
        $userGroup = $groupMapper->getNewGhost();
        $list = new Collection();
        $list->add($userGroup);

        $this->subject->setData(['usergroup' => $list]);

        self::assertTrue(
            $this->subject->hasGroupMembership(
                $userGroup->getUid() . ',' . $groupMapper->getNewGhost()->getUid()
            )
        );
    }

    /**
     * @test
     */
    public function hasGroupMembershipForUserNoneOfTheProvidedGroupsReturnsFalse(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);
        $list = new Collection();
        $list->add($groupMapper->getNewGhost());
        $list->add($groupMapper->getNewGhost());

        $this->subject->setData(['usergroup' => $list]);

        self::assertFalse(
            $this->subject->hasGroupMembership(
                $groupMapper->getNewGhost()->getUid() . ',' . $groupMapper->getNewGhost()->getUid()
            )
        );
    }

    // Tests concerning the gender

    /**
     * @test
     */
    public function hasGenderForNoGenderFieldInTcaReturnsFalse(): void
    {
        $this->removeGenderField();

        self::assertFalse(FrontEndUser::hasGenderField());
    }

    /**
     * @test
     */
    public function hasGenderForGenderFieldInTcaReturnsTrue(): void
    {
        $this->enableGenderField();

        self::assertTrue(FrontEndUser::hasGenderField());
    }

    /**
     * @test
     */
    public function getGenderForForNoGenderFieldReturnsGenderUnknown(): void
    {
        $this->removeGenderField();

        self::assertSame(FrontEndUser::GENDER_UNKNOWN, $this->subject->getGender());
    }

    /**
     * @test
     */
    public function getGenderForGenderValueZeroReturnsGenderMale(): void
    {
        $this->enableGenderField();

        $this->subject->setData(['gender' => 0]);

        self::assertSame(FrontEndUser::GENDER_MALE, $this->subject->getGender());
    }

    /**
     * @test
     */
    public function getGenderForGenderValueOneReturnsGenderFemale(): void
    {
        $this->enableGenderField();

        $this->subject->setData(['gender' => 1]);

        self::assertSame(FrontEndUser::GENDER_FEMALE, $this->subject->getGender());
    }

    /**
     * @return int[][]
     */
    public function genderDataProvider(): array
    {
        return [
            'male' => [FrontEndUser::GENDER_MALE],
            'female' => [FrontEndUser::GENDER_FEMALE],
            'unknown' => [FrontEndUser::GENDER_UNKNOWN],
        ];
    }

    /**
     * @test
     *
     * @param int $gender
     *
     * @dataProvider genderDataProvider
     */
    public function setGenderCanSetGender(int $gender): void
    {
        $this->enableGenderField();
        $this->subject->setData([]);

        $this->subject->setGender($gender);

        self::assertSame($gender, $this->subject->getGender());
    }

    /**
     * @test
     */
    public function setGenderForInvalidGenderKeyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->enableGenderField();
        $this->subject->setData([]);

        $this->subject->setGender(4);
    }

    // Tests concerning the first name

    /**
     * @test
     */
    public function hasFirstNameForNoFirstNameSetReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasFirstName()
        );
    }

    /**
     * @test
     */
    public function hasFirstNameForFirstNameSetReturnsTrue(): void
    {
        $this->subject->setData(['first_name' => 'foo']);

        self::assertTrue(
            $this->subject->hasFirstName()
        );
    }

    /**
     * @test
     */
    public function getFirstNameForNoFirstNameSetReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            '',
            $this->subject->getFirstName()
        );
    }

    /**
     * @test
     */
    public function getFirstNameForFirstNameSetReturnsFirstName(): void
    {
        $this->subject->setData(['first_name' => 'foo']);

        self::assertSame(
            'foo',
            $this->subject->getFirstName()
        );
    }

    /**
     * @test
     */
    public function setFirstNameSetsFirstName(): void
    {
        $this->subject->setFirstName('John');

        self::assertSame(
            'John',
            $this->subject->getFirstName()
        );
    }

    /**
     * @test
     */
    public function getFirstOrFullNameForUserWithFirstNameReturnsFirstName(): void
    {
        $this->subject->setData(
            ['first_name' => 'foo', 'name' => 'foo bar']
        );

        self::assertSame(
            'foo',
            $this->subject->getFirstOrFullName()
        );
    }

    /**
     * @test
     */
    public function getFirstOrFullNameForUserWithoutFirstNameReturnsName(): void
    {
        $this->subject->setData(['name' => 'foo bar']);

        self::assertSame(
            'foo bar',
            $this->subject->getFirstOrFullName()
        );
    }

    // Tests concerning the last name

    /**
     * @test
     */
    public function hasLastNameForNoLastNameSetReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLastName()
        );
    }

    /**
     * @test
     */
    public function hasLastNameForLastNameSetReturnsTrue(): void
    {
        $this->subject->setData(['last_name' => 'bar']);

        self::assertTrue(
            $this->subject->hasLastName()
        );
    }

    /**
     * @test
     */
    public function getLastNameForNoLastNameSetReturnsEmptyString(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            '',
            $this->subject->getLastName()
        );
    }

    /**
     * @test
     */
    public function getLastNameForLastNameSetReturnsLastName(): void
    {
        $this->subject->setData(['last_name' => 'bar']);

        self::assertSame(
            'bar',
            $this->subject->getLastName()
        );
    }

    /**
     * @test
     */
    public function setLastNameSetsLastName(): void
    {
        $this->subject->setLastName('Jacuzzi');

        self::assertSame(
            'Jacuzzi',
            $this->subject->getLastName()
        );
    }

    /**
     * @test
     */
    public function getLastOrFullNameForUserWithLastNameReturnsLastName(): void
    {
        $this->subject->setData(
            ['last_name' => 'bar', 'name' => 'foo bar']
        );

        self::assertSame(
            'bar',
            $this->subject->getLastOrFullName()
        );
    }

    /**
     * @test
     */
    public function getLastOrFullNameForUserWithoutLastNameReturnsName(): void
    {
        $this->subject->setData(['name' => 'foo bar']);

        self::assertSame(
            'foo bar',
            $this->subject->getLastOrFullName()
        );
    }

    // Tests concerning the date of birth

    /**
     * @test
     */
    public function getDateOfBirthReturnsZeroForNoDateSet(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getDateOfBirth()
        );
    }

    /**
     * @test
     */
    public function getDateOfBirthReturnsDateFromDateOfBirthField(): void
    {
        // 1980-04-01
        $date = 323391600;
        $this->subject->setData(['date_of_birth' => $date]);

        self::assertSame(
            $date,
            $this->subject->getDateOfBirth()
        );
    }

    /**
     * @test
     */
    public function hasDateOfBirthForNoDateOfBirthReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasDateOfBirth()
        );
    }

    /**
     * @test
     */
    public function hasDateOfBirthForNonZeroDateOfBirthReturnsTrue(): void
    {
        // 1980-04-01
        $date = 323391600;
        $this->subject->setData(['date_of_birth' => $date]);

        self::assertTrue(
            $this->subject->hasDateOfBirth()
        );
    }

    // Tests concerning getAge

    /**
     * @test
     */
    public function getAgeForNoDateOfBirthReturnsZero(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getAge()
        );
    }

    /**
     * @test
     */
    public function getAgeForBornOneHourAgoReturnsZero(): void
    {
        $now = mktime(18, 0, 0, 9, 15, 2010);
        $GLOBALS['EXEC_TIME'] = $now;

        $this->subject->setData(
            ['date_of_birth' => $now - 60 * 60]
        );

        self::assertSame(
            0,
            $this->subject->getAge()
        );
    }

    /**
     * @test
     */
    public function getAgeForAnAgeOfTenYearsAndSomeMonthsReturnsTen(): void
    {
        $GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

        $this->subject->setData(
            ['date_of_birth' => mktime(18, 0, 0, 1, 15, 2000)]
        );

        self::assertSame(
            10,
            $this->subject->getAge()
        );
    }

    /**
     * @test
     */
    public function getAgeForAnAgeOfTenYearsMinusSomeMonthsReturnsNine(): void
    {
        $GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

        $this->subject->setData(
            ['date_of_birth' => mktime(18, 0, 0, 11, 15, 2000)]
        );

        self::assertSame(
            9,
            $this->subject->getAge()
        );
    }

    /**
     * @test
     */
    public function getAgeForAnAgeOfTenYearsMinusSomeDaysReturnsNine(): void
    {
        $GLOBALS['EXEC_TIME'] = mktime(18, 0, 0, 9, 15, 2010);

        $this->subject->setData(
            ['date_of_birth' => mktime(18, 0, 0, 9, 21, 2000)]
        );

        self::assertSame(
            9,
            $this->subject->getAge()
        );
    }

    // Tests concerning the date of the last login

    /**
     * @test
     */
    public function getLastLoginAsUnixTimestampReturnsZeroForNoDateSet(): void
    {
        $this->subject->setData([]);

        self::assertSame(
            0,
            $this->subject->getLastLoginAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function getLastLoginAsUnixTimestampReturnsDateFromLastLoginField(): void
    {
        // 1980-04-01
        $date = 323391600;
        $this->subject->setData(['lastlogin' => $date]);

        self::assertSame(
            $date,
            $this->subject->getLastLoginAsUnixTimestamp()
        );
    }

    /**
     * @test
     */
    public function hasLastLoginForNoLastLoginReturnsFalse(): void
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLastLogin()
        );
    }

    /**
     * @test
     */
    public function hasLastLoginForNonZeroLastLoginReturnsTrue(): void
    {
        // 1980-04-01
        $date = 323391600;
        $this->subject->setData(['lastlogin' => $date]);

        self::assertTrue(
            $this->subject->hasLastLogin()
        );
    }

    // Tests concerning the job title

    /**
     * @test
     */
    public function hasJobTitleForEmptyJobTitleReturnsFalse(): void
    {
        $this->subject->setData(['title' => '']);

        self::assertFalse(
            $this->subject->hasJobTitle()
        );
    }

    /**
     * @test
     */
    public function hasJobTitleForNonEmptyJobTitleReturnsTrue(): void
    {
        $this->subject->setData(['title' => 'facility manager']);

        self::assertTrue(
            $this->subject->hasJobTitle()
        );
    }

    /**
     * @test
     */
    public function getJobTitleForEmptyJobTitleReturnsEmptyString(): void
    {
        $this->subject->setData(['title' => '']);

        self::assertSame(
            '',
            $this->subject->getJobTitle()
        );
    }

    /**
     * @test
     */
    public function getJobTitleForNonEmptyJobTitleReturnsJobTitle(): void
    {
        $this->subject->setData(['title' => 'facility manager']);

        self::assertSame(
            'facility manager',
            $this->subject->getJobTitle()
        );
    }

    /**
     * @test
     */
    public function setJobTitleSetsJobTitle(): void
    {
        $this->subject->setJobTitle('foo bar');

        self::assertSame(
            'foo bar',
            $this->subject->getJobTitle()
        );
    }
}
