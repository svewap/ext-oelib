<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Configuration;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures\TestingConfigurationCheck;

/**
 * @covers \OliverKlee\Oelib\Configuration\AbstractConfigurationCheck
 */
final class AbstractConfigurationCheckTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @test
     */
    public function checkTemplateFileForExistingTemplateFileGeneratesNoWarnings(): void
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
    public function checkTemplateFileForEmptyFileNameGeneratesWarning(): void
    {
        $configuration = new DummyConfiguration(['templateFile' => '']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkTemplateFile');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.templateFile', $warning);
        self::assertStringContainsString('is empty, but needs to be non-empty', $warning);
    }

    /**
     * @test
     */
    public function checkTemplateFileForInexistentFileNameGeneratesWarning(): void
    {
        $configuration = new DummyConfiguration(['templateFile' => 'nothing to see here']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkTemplateFile');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.templateFile', $warning);
        self::assertStringContainsString('cannot be read', $warning);
    }

    /**
     * @test
     */
    public function checkFileExistsForExistingFileGeneratesNoWarnings(): void
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
    public function checkFileExistsForEmptyFileNameGeneratesWarningWithPathAndEplanationAndDescription(): void
    {
        $configuration = new DummyConfiguration(['file' => '']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkFileExists');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.file', $warning);
        self::assertStringContainsString('some description', $warning);
        self::assertStringContainsString('is empty, but needs to be non-empty', $warning);
    }

    /**
     * @test
     */
    public function checkFileExistsForInexistentFileNameGeneratesWarning(): void
    {
        $configuration = new DummyConfiguration(['file' => 'nothing to see here']);
        $subject = new TestingConfigurationCheck($configuration, 'plugin.tx_oelib');
        $subject->setCheckMethod('checkFileExists');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.file', $warning);
        self::assertStringContainsString('cannot be read', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsOrEmptyForEmptyStringNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsOrEmptyForNonEmptyNotInTableAddsWarningWithPathAndExplanation(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => 'comment']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.column', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsOrEmptyForStringInTableNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => 'title']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsNotEmptyForEmptyStringAddsWarningWithPathAndExplanation(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.column', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsNotEmptyForNonEmptyNotInTableAddsWarningWithPathAndExplanation(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => 'comment']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.column', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsNotEmptyForStringInTableNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => 'title']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsNotEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForEmptyStringNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForSingleStringNotInTableAddsWarningWithPathAndExplanation(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => 'comment']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.columns', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForOneStringInTableAndAnotherNotInTableAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['columns' => 'title,comment']),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.columns', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForSingleStringInTableNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => 'title']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForMultipleStringsInTableNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['columns' => 'title,header']),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForMultipleStringsInTableWithSpaceNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['columns' => 'title, header']),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsNotEmptyForEmptyStringAddsWarningWithPathAndExplanation(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.columns', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsNotEmptyForSingleStringNotInTableAddsWarningWithPathAndExplanation(): void
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => 'comment']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.columns', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsNotEmptyForOneStringInTableAndAnotherNotInTableAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['columns' => 'title,comment']),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkIfMultiInTableColumnsNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertStringContainsString('plugin.tx_oelib.columns', $warning);
        self::assertStringContainsString('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsNotEmptyForMultipleStringInTableNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['columns' => 'title,header']),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkIfMultiInTableColumnsNotEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsNotEmptyForMultipleStringWithSpaceInTableNotAddsWarning(): void
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['columns' => 'title, header']),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkIfMultiInTableColumnsNotEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }
}
