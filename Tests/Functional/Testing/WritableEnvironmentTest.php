<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Testing;

use OliverKlee\Oelib\Testing\WritableEnvironment;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Testing\WritableEnvironment
 */
final class WritableEnvironmentTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    protected $initializeDatabase = false;

    /**
     * @test
     */
    public function setCurrentScriptOverwritesCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();
        $new = '/var/www/html/public/index.php';
        self::assertNotSame($previous, $new);

        WritableEnvironment::setCurrentScript($new);

        self::assertSame($new, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function restoreCurrentScriptWithoutOverwrittenCurrentScriptKeepsCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();

        WritableEnvironment::restoreCurrentScript();

        self::assertSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function restoreCurrentScriptCalledTwiceWithoutOverwrittenCurrentScriptKeepsCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();

        WritableEnvironment::restoreCurrentScript();
        WritableEnvironment::restoreCurrentScript();

        self::assertSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function restoreCurrentScriptAfterOverwritingRestoresOriginalCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();
        WritableEnvironment::setCurrentScript('/var/www/html/public/index.php');

        WritableEnvironment::restoreCurrentScript();

        self::assertSame($previous, Environment::getCurrentScript());
    }

    /**
     * @test
     */
    public function restoreCurrentScriptAfterOverwritingTwiceRestoresOriginalCurrentScript(): void
    {
        $previous = Environment::getCurrentScript();
        WritableEnvironment::setCurrentScript('/var/www/html/public/index.php');
        WritableEnvironment::setCurrentScript('/var/www/html/public-2/index.php');

        WritableEnvironment::restoreCurrentScript();

        self::assertSame($previous, Environment::getCurrentScript());
    }
}
