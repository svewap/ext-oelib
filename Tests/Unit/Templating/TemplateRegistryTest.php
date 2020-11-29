<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Templating;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Templating\Template;
use OliverKlee\Oelib\Templating\TemplateRegistry;

/**
 * Test case.
 *
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class TemplateRegistryTest extends UnitTestCase
{
    /*
     * Tests concerning the Singleton property
     */

    /**
     * @test
     */
    public function getInstanceReturnsTemplateRegistryInstance()
    {
        self::assertInstanceOf(
            TemplateRegistry::class,
            TemplateRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            TemplateRegistry::getInstance(),
            TemplateRegistry::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        $firstInstance = TemplateRegistry::getInstance();
        TemplateRegistry::purgeInstance();

        self::assertNotSame(
            $firstInstance,
            TemplateRegistry::getInstance()
        );
    }

    /*
     * Tests concerning get()
     */

    /**
     * @test
     */
    public function getForEmptyTemplateFileNameReturnsTemplateInstance()
    {
        self::assertInstanceOf(
            Template::class,
            TemplateRegistry::get('')
        );
    }

    /**
     * @test
     */
    public function getForEmptyTemplateFileNameCalledTwoTimesReturnsNewInstance()
    {
        self::assertNotSame(
            TemplateRegistry::get(''),
            TemplateRegistry::get('')
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameReturnsTemplate()
    {
        self::assertInstanceOf(
            Template::class,
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html')
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameCalledTwoTimesReturnsNewInstance()
    {
        self::assertNotSame(
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html'),
            TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html')
        );
    }

    /**
     * @test
     */
    public function getForExistingTemplateFileNameReturnsProcessedTemplate()
    {
        $template = TemplateRegistry::get('EXT:oelib/Tests/Functional/Fixtures/Template.html');

        self::assertSame(
            'Hello world!' . LF,
            $template->getSubpart()
        );
    }
}
