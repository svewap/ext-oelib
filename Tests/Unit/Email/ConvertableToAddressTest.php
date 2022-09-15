<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Interfaces\ConvertableToMimeAddress;
use OliverKlee\Oelib\Tests\Unit\Email\Fixtures\TestingMimeConvertableMailRole;
use Symfony\Component\Mime\Address;

/**
 * @covers \OliverKlee\Oelib\Email\ConvertableToMimeAddressTrait
 */
final class ConvertableToAddressTest extends UnitTestCase
{
    /**
     * @test
     */
    public function implementsInterface(): void
    {
        self::assertInstanceOf(ConvertableToMimeAddress::class, new TestingMimeConvertableMailRole());
    }

    /**
     * @test
     */
    public function canBuildMimeAddress(): void
    {
        $subject = new TestingMimeConvertableMailRole();

        $address = $subject->toMimeAddress();
        self::assertInstanceOf(Address::class, $address);
    }

    /**
     * @test
     */
    public function mimeAddressHasEmailAddress(): void
    {
        $subject = new TestingMimeConvertableMailRole();

        $address = $subject->toMimeAddress();
        self::assertSame('trix@example.com', $address->getAddress());
    }

    /**
     * @test
     */
    public function mimeAddressHasName(): void
    {
        $subject = new TestingMimeConvertableMailRole();

        $address = $subject->toMimeAddress();
        self::assertSame('Trix', $address->getName());
    }
}
