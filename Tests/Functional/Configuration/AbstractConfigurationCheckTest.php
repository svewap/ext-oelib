<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures\TestingConfigurationCheck;

/**
 * @covers \OliverKlee\Oelib\Configuration\AbstractConfigurationCheck
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractConfigurationCheckTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @test
     */
    public function checkTemplateFileForExistingTemplateFileGeneratesNoWarnings()
    {
        $configuration = new DummyConfiguration(
            ['templateFile' => 'EXT:oelib/Tests/Functional/Fixtures/Template.html']
        );
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkTemplateFile');

        $subject->check();

        self::assertSame([], $subject->getWarningsAsHtml());
    }

    /**
     * @test
     */
    public function checkTemplateFileForEmptyFileNameGeneratesWarning()
    {
        $configuration = new DummyConfiguration(['templateFile' => '']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkTemplateFile');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.templateFile', $warning);
        self::assertContains('is empty, but needs to be non-empty', $warning);
    }

    /**
     * @test
     */
    public function checkTemplateFileForInexistentFileNameGeneratesWarning()
    {
        $configuration = new DummyConfiguration(['templateFile' => 'nothing to see here']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkTemplateFile');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.templateFile', $warning);
        self::assertContains('cannot be read', $warning);
    }

    /**
     * @test
     */
    public function checkFileExistsForExistingFileGeneratesNoWarnings()
    {
        $configuration = new DummyConfiguration(['file' => 'EXT:oelib/Tests/Functional/Fixtures/Template.html']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkFileExists');

        $subject->check();

        self::assertSame([], $subject->getWarningsAsHtml());
    }

    /**
     * @test
     */
    public function checkFileExistsForEmptyFileNameGeneratesWarningWithPathAndEplanationAndDescription()
    {
        $configuration = new DummyConfiguration(['file' => '']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkFileExists');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.file', $warning);
        self::assertContains('some description', $warning);
        self::assertContains('is empty, but needs to be non-empty', $warning);
    }

    /**
     * @test
     */
    public function checkFileExistsForInexistentFileNameGeneratesWarning()
    {
        $configuration = new DummyConfiguration(['file' => 'nothing to see here']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkFileExists');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.file', $warning);
        self::assertContains('cannot be read', $warning);
    }
}
