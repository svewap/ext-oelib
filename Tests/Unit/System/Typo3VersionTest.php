<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\System;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\System\Typo3Version;

/**
 * @covers \OliverKlee\Oelib\System\Typo3Version
 */
final class Typo3VersionTest extends UnitTestCase
{
    /**
     * @var \TYPO3\CMS\Core\Information\Typo3Version
     */
    private $version = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->version = new \TYPO3\CMS\Core\Information\Typo3Version();
    }

    private function getMajorVersion(): int
    {
        return $this->version->getMajorVersion();
    }

    /**
     * @test
     */
    public function isAtLeastForLowerVersionThanCurrentVersionReturnsTrue(): void
    {
        $result = Typo3Version::isAtLeast($this->getMajorVersion() - 1);

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isAtLeastForCurrentVersionReturnsTrue(): void
    {
        $result = Typo3Version::isAtLeast($this->getMajorVersion());

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isAtLeastForHigherVersionThanCurrentVersionReturnsFalse(): void
    {
        $result = Typo3Version::isAtLeast($this->getMajorVersion() + 1);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isNotHigherThanForLowerVersionThanCurrentVersionReturnsFalse(): void
    {
        $result = Typo3Version::isNotHigherThan($this->getMajorVersion() - 1);

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function isNotHigherThanForCurrentVersionReturnsTrue(): void
    {
        $result = Typo3Version::isNotHigherThan($this->getMajorVersion());

        self::assertTrue($result);
    }

    /**
     * @test
     */
    public function isNotHigherThanForHigherVersionThanCurrentVersionReturnsTrue(): void
    {
        $result = Typo3Version::isNotHigherThan($this->getMajorVersion() + 1);

        self::assertTrue($result);
    }
}
