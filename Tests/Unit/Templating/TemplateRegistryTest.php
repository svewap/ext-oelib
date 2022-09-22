<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Templating;

use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Oelib\Templating\TemplateRegistry
 */
final class TemplateRegistryTest extends UnitTestCase
{
    // Tests concerning the Singleton property

    /**
     * @test
     */
    public function getInstanceReturnsTemplateRegistryInstance(): void
    {
        self::assertInstanceOf(
            TemplateRegistry::class,
            TemplateRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance(): void
    {
        self::assertSame(
            TemplateRegistry::getInstance(),
            TemplateRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance(): void
    {
        $firstInstance = TemplateRegistry::getInstance();
        TemplateRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            TemplateRegistry::getInstance()
        );
    }

    // Tests concerning get()

    /**
     * @test
     */
    public function getForEmptyTemplateFileNameReturnsTemplateInstance(): void
    {
        self::assertInstanceOf(
            Template::class,
            TemplateRegistry::get('')
        );
    }

    /**
     * @test
     */
    public function getForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            TemplateRegistry::get(''),
            TemplateRegistry::get('')
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameReturnsTemplate(): void
    {
        self::assertInstanceOf(
            Template::class,
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html')
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance(): void
    {
        self::assertNotSame(
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html'),
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html')
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameReturnsProcessedTemplate(): void
    {
        $template = TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html');

        self::assertSame(
            "Hello world!\n",
            $template->getSubpart()
        );
    }
}
