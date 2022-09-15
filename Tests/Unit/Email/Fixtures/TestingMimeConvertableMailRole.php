<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Email\Fixtures;

use OliverKlee\Oelib\Email\ConvertableToMimeAddressTrait;
use OliverKlee\Oelib\Interfaces\ConvertableToMimeAddress;

final class TestingMimeConvertableMailRole implements ConvertableToMimeAddress
{
    use ConvertableToMimeAddressTrait;

    public function getName(): string
    {
        return 'Trix';
    }

    public function getEmailAddress(): string
    {
        return 'trix@example.com';
    }
}
