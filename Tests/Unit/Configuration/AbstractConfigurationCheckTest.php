<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures\TestingConfigurationCheck;

/**
 * @covers \OliverKlee\Oelib\Configuration\AbstractConfigurationCheck
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class AbstractConfigurationCheckTest extends UnitTestCase
{
    /**
     * @test
     */
    public function isAbstractConfigurationCheck()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');

        self::assertInstanceOf(AbstractConfigurationCheck::class, $subject);
    }

    /**
     * @test
     */
    public function hasWarningsInitiallyReturnsFalse()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function hasWarningsWithOneWarningReturnsTrue()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->generateDummyWarnings(1);

        self::assertTrue($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function hasWarningsWithTwoWarningReturnsTrue()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->generateDummyWarnings(2);

        self::assertTrue($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function getWarningsAsHtmlInitiallyReturnsEmptyArray()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');

        self::assertSame([], $subject->getWarningsAsHtml());
    }

    /**
     * @test
     */
    public function getWarningsWithOneWarningReturnsWarningWithWarningText()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->generateDummyWarnings(1);

        $warnings = $subject->getWarningsAsHtml();
        self::assertCount(1, $warnings);
        self::assertContains('warning #1', $warnings[0]);
    }

    /**
     * @test
     */
    public function getWarningsWithMultipleWarningsReturnsMultipleWarnings()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->generateDummyWarnings(2);

        $warnings = $subject->getWarningsAsHtml();
        self::assertCount(2, $warnings);
    }

    /**
     * @test
     */
    public function warningTextCanContainHtml()
    {
        $warningText = '<em>Hello!</em>';
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->generateWarningWithText($warningText);

        $warnings = $subject->getWarningsAsHtml();
        self::assertContains($warningText, $warnings[0]);
    }

    /**
     * @test
     */
    public function warningTextIsRenderedAsBootstrapWarningWithEnglish()
    {
        $warningText = 'Something is wrong.';
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->generateWarningWithText($warningText);

        $warnings = $subject->getWarningsAsHtml();
        self::assertContains('<div lang="en" class="alert alert-dark" role="alert">', $warnings[0]);
    }

    /**
     * @test
     */
    public function checkWithoutCreatingWarningsDoesNotAddAnyWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkStaticIncludedForStaticTemplateIncludedGeneratesNoWarnings()
    {
        $subject = new TestingConfigurationCheck(
            new DummyConfiguration(['isStaticTemplateLoaded' => 1]),
            'plugin.tx_oelib'
        );
        $subject->setCheckMethod('checkStaticIncluded');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkStaticIncludedForStaticTemplateNotIncludedGeneratesWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkStaticIncluded');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        self::assertContains('The static template is not included.', $subject->getWarningsAsHtml()[0]);
    }

    /**
     * @test
     */
    public function checkWithCreatingWarningsAddsWarningsOnlyOnce()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkStaticIncluded');

        $subject->check();
        $subject->check();

        self::assertCount(1, $subject->getWarningsAsHtml());
    }

    /**
     * @test
     */
    public function checkForNonEmptyStringForEmptyStringAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['title' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkForNonEmptyString');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.title', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkForNonEmptyStringForNonEmptyStringNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['title' => 'Yo!']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkForNonEmptyString');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInSetOrEmptyForEmptyStringNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['size' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInSetOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInSetOrEmptyForNonEmptyStringNotInSetAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['size' => 'great']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInSetOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.size', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInSetOrEmptyForNonEmptyStringInSetNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['size' => 's']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInSetOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfSingleInSetNotEmptyForEmptyStringAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['size' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInSetNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.size', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInSetNotEmptyForNonEmptyStringNotInSetAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['size' => 'great']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInSetNotEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.size', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfSingleInSetNotEmptyForNonEmptyStringInSetNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['size' => 's']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfSingleInSetNotEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }
}
