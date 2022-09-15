<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Logging\Fixtures;

use OliverKlee\Oelib\Logging\Interfaces\LoggingAware;

/**
 * Testing class for the LoggingAware trait.
 */
final class TestingLoggingAware implements LoggingAware
{
    use \OliverKlee\Oelib\Logging\Traits\LoggingAware;
}
