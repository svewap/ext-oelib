<?php

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Email\GeneralEmailRole;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class GeneralEmailRoleTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsEmailRole()
    {
        $subject = new GeneralEmailRole('jade@example.com');

        static::assertInstanceOf(\Tx_Oelib_Interface_MailRole::class, $subject);
    }

    /**
     * @test
     */
    public function usesEmailAddressFromConstructor()
    {
        $emailAddress = 'jade@example.com';
        $subject = new GeneralEmailRole($emailAddress);

        static::assertSame($emailAddress, $subject->getEmailAddress());
    }

    /**
     * @test
     */
    public function usesNameFromConstructor()
    {
        $name = 'Jade Jennings';
        $subject = new GeneralEmailRole('jade@example.com', $name);

        static::assertSame($name, $subject->getName());
    }

    /**
     * @test
     */
    public function hasEmptyNameByDefault()
    {
        $subject = new GeneralEmailRole('jade@example.com');

        static::assertSame('', $subject->getName());
    }
}
