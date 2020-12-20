<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

use OliverKlee\Oelib\DataStructures\Collection;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Interfaces\Address;
use OliverKlee\Oelib\Interfaces\MailRole;
use OliverKlee\Oelib\Mapper\CountryMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class represents a front-end user.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FrontEndUser extends AbstractModel implements MailRole, Address
{
    /**
     * @var int represents the male gender for this user
     */
    const GENDER_MALE = 0;

    /**
     * @var int represents the female gender for this user
     */
    const GENDER_FEMALE = 1;

    /**
     * @var int represents an unknown gender for this user
     */
    const GENDER_UNKNOWN = 99;

    /**
     * Gets this user's user name (login name).
     *
     * @return string this user's user name, will not be empty for valid users
     */
    public function getUserName(): string
    {
        return $this->getAsString('username');
    }

    /**
     * Sets this user's user name (login name).
     *
     * @param string $userName
     *        the user name to set, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setUserName(string $userName)
    {
        if ($userName === '') {
            throw new \InvalidArgumentException('$userName must not be empty.');
        }

        $this->setAsString('username', $userName);
    }

    /**
     * Gets the password.
     *
     * @return string the password, might be empty
     */
    public function getPassword(): string
    {
        return $this->getAsString('password');
    }

    /**
     * Sets the password.
     *
     * @param string $password the password to set, must not be empty
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setPassword(string $password)
    {
        if ($password === '') {
            throw new \InvalidArgumentException('$password must not be empty.');
        }

        $this->setAsString('password', $password);
    }

    /**
     * Gets this user's real name.
     *
     * First, the "name" field is checked. If that is empty, the fields
     * "first_name" and "last_name" are checked. If those are empty as well,
     * the user name is returned as a fallback value.
     *
     * @return string the user's real name, will not be empty for valid records
     */
    public function getName(): string
    {
        if ($this->hasString('name')) {
            $result = $this->getAsString('name');
        } elseif ($this->hasFirstName() || $this->hasLastName()) {
            $result = trim($this->getFirstName() . ' ' . $this->getLastName());
        } else {
            $result = $this->getUserName();
        }

        return $result;
    }

    /**
     * Checks whether this user has a non-empty name.
     *
     * @return bool TRUE if this user has a non-empty name, FALSE otherwise
     */
    public function hasName(): bool
    {
        return $this->hasString('name') || $this->hasFirstName()
            || $this->hasLastName();
    }

    /**
     * Sets the full name.
     *
     * @param string $name the name to set, may be empty
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->setAsString('name', $name);
    }

    /**
     * Gets this user's company.
     *
     * @return string this user's company, may be empty
     */
    public function getCompany(): string
    {
        return $this->getAsString('company');
    }

    /**
     * Checks whether this user has a non-empty company set.
     *
     * @return bool TRUE if this user has a company set, FALSE otherwise
     */
    public function hasCompany(): bool
    {
        return $this->hasString('company');
    }

    /**
     * Sets the company.
     *
     * @param string $company the company set, may be empty
     *
     * @return void
     */
    public function setCompany(string $company)
    {
        $this->setAsString('company', $company);
    }

    /**
     * Gets this user's street.
     *
     * @return string this user's street, may be multi-line, may be empty
     */
    public function getStreet(): string
    {
        return $this->getAsString('address');
    }

    /**
     * Checks whether this user has a non-empty street set.
     *
     * @return bool TRUE if this user has a street set, FALSE otherwise
     */
    public function hasStreet(): bool
    {
        return $this->hasString('address');
    }

    /**
     * Sets the street address.
     *
     * @param string $street the street address, may be empty
     *
     * @return void
     */
    public function setStreet(string $street)
    {
        $this->setAsString('address', $street);
    }

    /**
     * Gets this user's ZIP code.
     *
     * @return string this user's ZIP code, may be empty
     */
    public function getZip(): string
    {
        return $this->getAsString('zip');
    }

    /**
     * Checks whether this user has a non-empty ZIP code set.
     *
     * @return bool TRUE if this user has a ZIP code set, FALSE otherwise
     */
    public function hasZip(): bool
    {
        return $this->hasString('zip');
    }

    /**
     * Sets the ZIP code.
     *
     * @param string $zipCode the ZIP code, may be empty
     *
     * @return void
     */
    public function setZip(string $zipCode)
    {
        $this->setAsString('zip', $zipCode);
    }

    /**
     * Gets this user's city.
     *
     * @return string this user's city, may be empty
     */
    public function getCity(): string
    {
        return $this->getAsString('city');
    }

    /**
     * Checks whether this user has a non-empty city set.
     *
     * @return bool TRUE if this user has a city set, FALSE otherwise
     */
    public function hasCity(): bool
    {
        return $this->hasString('city');
    }

    /**
     * Sets the city.
     *
     * @param string $city the city name, may be empty
     *
     * @return void
     */
    public function setCity(string $city)
    {
        $this->setAsString('city', $city);
    }

    /**
     * Gets this user's ZIP code and city, separated by a space.
     *
     * @return string this user's ZIP code city, will be empty if the user has
     *                no city set
     */
    public function getZipAndCity(): string
    {
        if (!$this->hasCity()) {
            return '';
        }

        return trim($this->getZip() . ' ' . $this->getCity());
    }

    /**
     * Gets this user's phone number.
     *
     * @return string this user's phone number, may be empty
     */
    public function getPhoneNumber(): string
    {
        return $this->getAsString('telephone');
    }

    /**
     * Checks whether this user has a non-empty phone number set.
     *
     * @return bool TRUE if this user has a phone number set, FALSE otherwise
     */
    public function hasPhoneNumber(): bool
    {
        return $this->hasString('telephone');
    }

    /**
     * Sets the phone number.
     *
     * @param string $phoneNumber the phone number, may be empty
     *
     * @return void
     */
    public function setPhoneNumber(string $phoneNumber)
    {
        $this->setAsString('telephone', $phoneNumber);
    }

    /**
     * Gets this user's e-mail address.
     *
     * @return string this user's e-mail address, may be empty
     */
    public function getEmailAddress(): string
    {
        return $this->getAsString('email');
    }

    /**
     * Checks whether this user has a non-empty e-mail address set.
     *
     * @return bool TRUE if this user has an e-mail address set, FALSE
     *                 otherwise
     */
    public function hasEmailAddress(): bool
    {
        return $this->hasString('email');
    }

    /**
     * Sets the e-mail address.
     *
     * @param string $eMailAddress the e-mail address to set, may be empty
     *
     * @return void
     */
    public function setEmailAddress(string $eMailAddress)
    {
        $this->setAsString('email', $eMailAddress);
    }

    /**
     * Gets this user's homepage URL (not linked yet).
     *
     * @return string this user's homepage URL, may be empty
     */
    public function getHomepage(): string
    {
        return $this->getAsString('www');
    }

    /**
     * Checks whether this user has a non-empty homepage set.
     *
     * @return bool TRUE if this user has a homepage set, FALSE otherwise
     */
    public function hasHomepage(): bool
    {
        return $this->hasString('www');
    }

    /**
     * Gets this user's image path (relative to the global upload directory).
     *
     * @return string this user's image path, may be empty
     */
    public function getImage(): string
    {
        return $this->getAsString('image');
    }

    /**
     * Checks whether this user has an image set.
     *
     * @return bool TRUE if this user has an image set, FALSE otherwise
     */
    public function hasImage(): bool
    {
        return $this->hasString('image');
    }

    /**
     * Checks whether this user has agreed to receive HTML e-mails.
     *
     * @return bool TRUE if the user agreed to receive HTML e-mails, FALSE
     *                 otherwise
     */
    public function wantsHtmlEmail(): bool
    {
        return $this->getAsBoolean('module_sys_dmail_html');
    }

    /**
     * Gets this user's user groups.
     *
     * @return Collection<BackEndUserGroup> this user's FE user groups, will not be empty if
     *                       the user data is valid
     */
    public function getUserGroups(): Collection
    {
        return $this->getAsList('usergroup');
    }

    /**
     * Sets this user's direct user groups.
     *
     * @param Collection<BackEndUserGroup> $userGroups the user groups to set, may be empty
     *
     * @return void
     */
    public function setUserGroups(Collection $userGroups)
    {
        $this->set('usergroup', $userGroups);
    }

    /**
     * Adds $group to this user's direct groups.
     *
     * @param FrontEndUserGroup $group
     *
     * @return void
     */
    public function addUserGroup(FrontEndUserGroup $group)
    {
        $this->getUserGroups()->add($group);
    }

    /**
     * Checks whether this user is a member of at least one of the user groups
     * provided as comma-separated UID list.
     *
     * @param string $uidList
     *        comma-separated list of user group UIDs, can also consist of only
     *        one UID, but must not be empty
     *
     * @return bool TRUE if the user is member of at least one of the user groups provided, FALSE otherwise
     *
     * @throws \InvalidArgumentException
     */
    public function hasGroupMembership(string $uidList): bool
    {
        if ($uidList === '') {
            throw new \InvalidArgumentException('$uidList must not be empty.', 1331488635);
        }

        $isMember = false;

        foreach (GeneralUtility::intExplode(',', $uidList, true) as $uid) {
            if ($this->getUserGroups()->hasUid($uid)) {
                $isMember = true;
                break;
            }
        }

        return $isMember;
    }

    /**
     * Gets this user's gender.
     *
     * Will return "unknown gender" if there is no FrontEndUser.gender field.
     *
     * @return int the gender of the user, will be ::GENDER_FEMALE, ::GENDER_MALE or ::GENDER_UNKNOWN
     */
    public function getGender(): int
    {
        if (!self::hasGenderField()) {
            return self::GENDER_UNKNOWN;
        }

        return $this->getAsInteger('gender');
    }

    /**
     * Checks whether FE users have a "gender" field at all.
     *
     * @return bool
     */
    public static function hasGenderField(): bool
    {
        return isset($GLOBALS['TCA']['fe_users']['columns']['gender']);
    }

    /**
     * Sets the gender.
     *
     * @param int $genderKey one of the predefined gender constants
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function setGender(int $genderKey)
    {
        $validGenderKeys = [self::GENDER_MALE, self::GENDER_FEMALE, self::GENDER_UNKNOWN];
        if (!in_array($genderKey, $validGenderKeys, true)) {
            throw new \InvalidArgumentException(
                '$genderKey must be one of the predefined constants, but actually is: ' . $genderKey,
                1393329321
            );
        }

        $this->setAsInteger('gender', $genderKey);
    }

    /**
     * Checks whether this user has a first name.
     *
     * @return bool TRUE if the user has a first name, FALSE otherwise
     */
    public function hasFirstName(): bool
    {
        return $this->hasString('first_name');
    }

    /**
     * Gets this user's first name
     *
     * @return string the first name of this user, will be empty if no first
     *                name is set
     */
    public function getFirstName(): string
    {
        return $this->getAsString('first_name');
    }

    /**
     * Sets the first name.
     *
     * @param string $firstName the first name to set, may be empty
     *
     * @return void
     */
    public function setFirstName(string $firstName)
    {
        $this->setAsString('first_name', $firstName);
    }

    /**
     * Checks whether this user has a last name.
     *
     * @return bool TRUE if the user has a last name, FALSE otherwise
     */
    public function hasLastName(): bool
    {
        return $this->hasString('last_name');
    }

    /**
     * Gets this user's last name
     *
     * @return string the last name of this user, will be empty if no last name
     *                is set
     */
    public function getLastName(): string
    {
        return $this->getAsString('last_name');
    }

    /**
     * Sets the last name.
     *
     * @param string $lastName the last name to set, may be empty
     *
     * @return void
     */
    public function setLastName(string $lastName)
    {
        $this->setAsString('last_name', $lastName);
    }

    /**
     * Gets this user's first name; if the user does not have a first name the
     * full name is returned instead.
     *
     * @return string the first name of this user if it exists, will return the
     *                user's full name otherwise
     */
    public function getFirstOrFullName(): string
    {
        return $this->hasFirstName() ? $this->getFirstName() : $this->getName();
    }

    /**
     * Gets this user's last name; if the user does not have a last name the
     * full name is returned instead.
     *
     * @return string the last name of this user if it exists, will return the
     *                user's full name otherwise
     */
    public function getLastOrFullName(): string
    {
        return $this->hasLastName() ? $this->getLastName() : $this->getName();
    }

    /**
     * Gets this user's date of birth as a UNIX timestamp.
     *
     * @return int the user's date of birth, will be zero if no date has
     *                 been set
     */
    public function getDateOfBirth(): int
    {
        return $this->getAsInteger('date_of_birth');
    }

    /**
     * Checks whether this user has a date of birth set.
     *
     * @return bool TRUE if this user has a non-zero date of birth, FALSE otherwise
     */
    public function hasDateOfBirth(): bool
    {
        return $this->hasInteger('date_of_birth');
    }

    /**
     * Returns this user's age in years.
     *
     * Note: This function only works correctly for users that were born after
     * 1970-01-01 and that were not born in the future.
     *
     * @return int this user's age in years, will be 0 if this user has no birth date set
     */
    public function getAge(): int
    {
        if (!$this->hasDateOfBirth()) {
            return 0;
        }

        $currentTimestamp = $GLOBALS['EXEC_TIME'];
        $birthTimestamp = $this->getDateOfBirth();

        $currentYear = (int)strftime('%Y', $currentTimestamp);
        $currentMonth = (int)strftime('%m', $currentTimestamp);
        $currentDay = (int)strftime('%d', $currentTimestamp);
        $birthYear = (int)strftime('%Y', $birthTimestamp);
        $birthMonth = (int)strftime('%m', $birthTimestamp);
        $birthDay = (int)strftime('%d', $birthTimestamp);

        $age = $currentYear - $birthYear;

        if ($currentMonth < $birthMonth) {
            $age--;
        } elseif ($currentMonth === $birthMonth) {
            if ($currentDay < $birthDay) {
                $age--;
            }
        }

        return $age;
    }

    /**
     * Gets this user's last login date and time as a UNIX timestamp.
     *
     * @return int the user's last login date and time, will be zero if the user has never logged in
     */
    public function getLastLoginAsUnixTimestamp(): int
    {
        return $this->getAsInteger('lastlogin');
    }

    /**
     * Checks whether this user has a last login date set.
     *
     * @return bool TRUE if this user has a non-zero last login date, FALSE
     */
    public function hasLastLogin(): bool
    {
        return $this->hasInteger('lastlogin');
    }

    /**
     * Returns the country of this user.
     *
     * Note: This function uses the "country code" field, not the free-text country field.
     *
     * @return Country|null
     */
    public function getCountry()
    {
        $countryCode = $this->getAsString('static_info_country');
        if ($countryCode === '') {
            return null;
        }

        try {
            /** @var CountryMapper $countryMapper */
            $countryMapper = MapperRegistry::get(CountryMapper::class);
            /** @var Country $country */
            $country = $countryMapper->findByIsoAlpha3Code($countryCode);
        } catch (NotFoundException $exception) {
            $country = null;
        }

        return $country;
    }

    /**
     * Sets the country of this user.
     *
     * @param Country $country
     *        the country to set for this place, can be NULL for "no country"
     *
     * @return void
     */
    public function setCountry(Country $country = null)
    {
        $countryCode = $country instanceof Country ? $country->getIsoAlpha3Code() : '';

        $this->setAsString('static_info_country', $countryCode);
    }

    /**
     * Returns whether this user has a country.
     *
     * @return bool TRUE if this user has a country, FALSE otherwise
     */
    public function hasCountry(): bool
    {
        return $this->getCountry() instanceof Country;
    }

    /**
     * Gets this user's job title.
     *
     * @return string this user's job title, may be empty
     */
    public function getJobTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Checks whether this user has a non-empty job title set.
     *
     * @return bool TRUE if this user has an job title set, FALSE otherwise
     */
    public function hasJobTitle(): bool
    {
        return $this->hasString('title');
    }

    /**
     * Sets this user's job title.
     *
     * @param string $jobTitle the job title to set, may be empty
     *
     * @return void
     */
    public function setJobTitle(string $jobTitle)
    {
        $this->setAsString('title', $jobTitle);
    }
}
