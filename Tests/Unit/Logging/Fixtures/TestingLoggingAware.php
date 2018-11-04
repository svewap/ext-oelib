<?php

namespace OliverKlee\Oelib\Tests\Unit\Logging\Fixtures;

use OliverKlee\Oelib\Logging\Interfaces\LoggingAware;

/**
 * Testing class for the LoggingAware trait.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class TestingLoggingAware implements LoggingAware
{
    use \OliverKlee\Oelib\Logging\Traits\LoggingAware;
}
