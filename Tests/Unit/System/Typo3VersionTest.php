<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\System;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\System\Typo3Version;

final class Typo3VersionTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Core\Information\Typo3Version
     */
    private $version = null;

    protected function setUp()
    {
        $this->version = new \TYPO3\CMS\Core\Information\Typo3Version();
    }

    private function getMajorVersion(): int
    {
        return $this->version->getMajorVersion();
    }

    /**
     * @test
     */
    public function isAtLeastForLowerVersionThanCurrentVersionReturnsTrue()
    {
        $result = Typo3Version::isAtLeast($this->getMajorVersion() - 1);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isAtLeastForCurrentVersionReturnsTrue()
    {
        $result = Typo3Version::isAtLeast($this->getMajorVersion());

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isAtLeastForHigherVersionThanCurrentVersionReturnsFalse()
    {
        $result = Typo3Version::isAtLeast($this->getMajorVersion() + 1);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isNotHigherThanForLowerVersionThanCurrentVersionReturnsFalse()
    {
        $result = Typo3Version::isNotHigherThan($this->getMajorVersion() - 1);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isNotHigherThanForCurrentVersionReturnsTrue()
    {
        $result = Typo3Version::isNotHigherThan($this->getMajorVersion());

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isNotHigherThanForHigherVersionThanCurrentVersionReturnsTrue()
    {
        $result = Typo3Version::isNotHigherThan($this->getMajorVersion() + 1);

        self::assertTrue($result);
    }
}
