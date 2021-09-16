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
        self::assertStringContainsString('plugin.tx_oelib.templateFile', $warning);
        self::assertStringContainsString('is empty, but needs to be non-empty', $warning);
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
        self::assertStringContainsString('plugin.tx_oelib.templateFile', $warning);
        self::assertStringContainsString('cannot be read', $warning);
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
        self::assertStringContainsString('plugin.tx_oelib.file', $warning);
        self::assertStringContainsString('some description', $warning);
        self::assertStringContainsString('is empty, but needs to be non-empty', $warning);
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
        self::assertStringContainsString('plugin.tx_oelib.file', $warning);
        self::assertStringContainsString('cannot be read', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsOrEmptyForEmptyStringNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsOrEmptyForNonEmptyStringNotInTableAddsWarningWithPathAndExplanation()
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
    public function checkIfSingleInTableColumnsOrEmptyForStringInTableNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => 'title']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInTableColumnsNotEmptyForEmptyStringAddsWarningWithPathAndExplanation()
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
    public function checkIfSingleInTableColumnsNotEmptyForNonEmptyStringNotInTableAddsWarningWithPathAndExplanation()
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
    public function checkIfSingleInTableColumnsNotEmptyForStringInTableNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['column' => 'title']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInTableColumnsNotEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForEmptyStringNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForSingleStringNotInTableAddsWarningWithPathAndExplanation()
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
    public function checkIfMultiInTableColumnsOrEmptyForOneStringInTableAndAnotherNotInTableAddsWarning()
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
    public function checkIfMultiInTableColumnsOrEmptyForSingleStringInTableNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['columns' => 'title']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfMultiInTableColumnsOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfMultiInTableColumnsOrEmptyForMultipleStringsInTableNotAddsWarning()
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
    public function checkIfMultiInTableColumnsOrEmptyForMultipleStringsInTableWithSpaceNotAddsWarning()
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
    public function checkIfMultiInTableColumnsNotEmptyForEmptyStringAddsWarningWithPathAndExplanation()
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
    public function checkIfMultiInTableColumnsNotEmptyForSingleStringNotInTableAddsWarningWithPathAndExplanation()
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
    public function checkIfMultiInTableColumnsNotEmptyForOneStringInTableAndAnotherNotInTableAddsWarning()
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
    public function checkIfMultiInTableColumnsNotEmptyForMultipleStringInTableNotAddsWarning()
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
    public function checkIfMultiInTableColumnsNotEmptyForMultipleStringWithSpaceInTableNotAddsWarning()
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
