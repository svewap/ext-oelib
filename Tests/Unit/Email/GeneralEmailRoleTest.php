<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use OliverKlee\Oelib\Email\GeneralEmailRole;
use OliverKlee\Oelib\Interfaces\ConvertableToMimeAddress;
use OliverKlee\Oelib\Interfaces\MailRole;
use Symfony\Component\Mime\Address;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Email\GeneralEmailRole
 */
final class GeneralEmailRoleTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsMailRole(): void
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertInstanceOf(MailRole::class, $subject);
    }

    /**
     * @test
     */
    public function implementsConvertableToMimeAddress(): void
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertInstanceOf(ConvertableToMimeAddress::class, $subject);
    }

    /**
     * @test
     */
    public function usesEmailAddressFromConstructor(): void
    {
        $emailAddress = 'jade@example.com';
        $subject = new GeneralEmailRole($emailAddress);

        self::assertSame($emailAddress, $subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function usesNameFromConstructor(): void
    {
        $name = 'Jade Jennings';
        $subject = new GeneralEmailRole('jade@example.com', $name);

        self::assertSame($name, $subject->getName());
    }

    /**
     * @test
     */
    public function hasEmptyNameByDefault(): void
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertSame('', $subject->getName());
    }

    /**
     * @test
     */
    public function canBuildMimeAddress(): void
    {
        $subject = new GeneralEmailRole('jade@example.com');

        $address = $subject->toMimeAddress();
        self::assertInstanceOf(Address::class, $address);
    }

    /**
     * @test
     */
    public function mimeAddressHasEmailAddress(): void
    {
        $emailAddress = 'jade@example.com';
        $subject = new GeneralEmailRole($emailAddress);

        $address = $subject->toMimeAddress();
        self::assertSame($emailAddress, $address->getAddress());
    }

    /**
     * @test
     */
    public function mimeAddressHasName(): void
    {
        $name = 'Max';
        $subject = new GeneralEmailRole('hello@example.com', $name);

        $address = $subject->toMimeAddress();
        self::assertSame($name, $address->getName());
    }
}
