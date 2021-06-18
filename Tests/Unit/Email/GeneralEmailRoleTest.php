<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Email\GeneralEmailRole;
use OliverKlee\Oelib\Interfaces\MailRole;

class GeneralEmailRoleTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsEmailRole()
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertInstanceOf(MailRole::class, $subject);
    }

    /**
     * @test
     */
    public function usesEmailAddressFromConstructor()
    {
        $emailAddress = 'jade@example.com';
        $subject = new GeneralEmailRole($emailAddress);

        self::assertSame($emailAddress, $subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function usesNameFromConstructor()
    {
        $name = 'Jade Jennings';
        $subject = new GeneralEmailRole('jade@example.com', $name);

        self::assertSame($name, $subject->getName());
    }

    /**
     * @test
     */
    public function hasEmptyNameByDefault()
    {
        $subject = new GeneralEmailRole('jade@example.com');

        self::assertSame('', $subject->getName());
    }
}
