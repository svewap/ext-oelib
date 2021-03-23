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
    public function checkWithUnknownCheckMethodThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1616068312);
        $this->expectExceptionMessage('Unknown value for the check method: "unknown"');

        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->setCheckMethod('unknown');

        $subject->check();
    }

    /**
     * @test
     */
    public function checkWithoutCheckMethodThrowsException()
    {
        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionCode(1616068312);
        $this->expectExceptionMessage('Unknown value for the check method: ""');

        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');

        $subject->check();
    }

    /**
     * @test
     */
    public function checkWithoutCreatingWarningsDoesNotAddAnyWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkNothing');

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
    public function configurationPathsAreEncoded()
    {
        $namespace = 'plugin.tx&oelib';
        $key = 'a"b';
        $subject = new TestingConfigurationCheck(new DummyConfiguration([$key => '']), $namespace);
        $subject->setCheckMethod('checkForNonEmptyStringWithUnsafeVariable');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains(\htmlspecialchars("{$namespace}.{$key}", ENT_QUOTES | ENT_HTML5), $warning);
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

    /**
     * @return array<string, array<string>>
     */
    public function nonBooleanStringDataProvider(): array
    {
        return [
            'empty string' => [''],
            '2' => ['2'],
            'false as string' => ['false'],
            'true as string' => ['true'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider nonBooleanStringDataProvider
     */
    public function checkIfBooleanForNonBooleanStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['switch' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfBoolean');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.switch', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @return array<string, array<string>>
     */
    public function booleanStringDataProvider(): array
    {
        return [
            'boolean false as string 0' => ['0'],
            'boolean true as string 1' => ['1'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider booleanStringDataProvider
     */
    public function checkIfBooleanForValidBooleanNotAddsWarning(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['switch' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfBoolean');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @return array<string, array<string>>
     */
    public function nonIntegerStringDataProvider(): array
    {
        return [
            'non-integer string' => ['The cake is a lie.'],
            'string starting with an integer' => ['12 monkeys'],
            'pie' => ['3.14159'],
            'exponential notation' => ['1e14'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider nonIntegerStringDataProvider
     */
    public function checkIfNonNegativeIntegerOrEmptyForNonIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeIntegerOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfNonNegativeIntegerOrEmptyForNegativeIntegerStringAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => '-1']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeIntegerOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @return array<string, array<string>>
     */
    public function positiveIntegerDataProvider(): array
    {
        return [
            'positive, one-digit integer' => ['1'],
            'positive, two-digit integer' => ['12'],
            'positive, one-digit integer with leading zero' => ['04'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function zeroIntegerDataProvider(): array
    {
        return [
            'zero' => ['0'],
        ];
    }

    /**
     * @return array<string, array<string>>
     */
    public function negativeIntegerDataProvider(): array
    {
        return [
            'one-digit negative' => ['-1'],
            'two-digit negative' => ['-29'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider positiveIntegerDataProvider
     * @dataProvider zeroIntegerDataProvider
     */
    public function checkIfNonNegativeIntegerOrEmptyForNonNegativeIntegerNotAddsWarning(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeIntegerOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfNonNegativeIntegerOrEmptyForEmptyStringNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeIntegerOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfIntegerInRangeForMinimumAndMaximumSameAllowsTheSameValue()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => 2]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfIntegerInRangeSame');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     */
    public function checkIfIntegerInRangeForMinimumGreaterThaneMaximumThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionCode(1616069185);
        $this->expectExceptionMessage('$minimum must be <= $maximum.');

        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => 2]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfIntegerInRangeSwitched');

        $subject->check();
    }

    /**
     * @test
     *
     * @dataProvider nonIntegerStringDataProvider
     */
    public function checkIfIntegerInRangeForNonIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfIntegerInRange');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @return array<string, array<string>>
     */
    public function integerNotInRangeDataProvider(): array
    {
        return [
            '< minimum' => [1],
            '> maximum' => [5],
        ];
    }

    /**
     * @test
     *
     * @dataProvider integerNotInRangeDataProvider
     */
    public function checkIfIntegerInRangeForValuesOutsideTheRangeAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfIntegerInRange');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @return array<string, array<string>>
     */
    public function integerInRangeDataProvider(): array
    {
        return [
            'minimum' => [2],
            'between minimum and maximum' => [3],
            'maximum' => [4],
        ];
    }

    /**
     * @test
     *
     * @dataProvider integerInRangeDataProvider
     */
    public function checkIfIntegerInRangeForValuesInRangeNotAddsWarning(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfIntegerInRange');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     *
     * @dataProvider nonIntegerStringDataProvider
     */
    public function checkIfPositiveIntegerForNonIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveInteger');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     *
     * @dataProvider negativeIntegerDataProvider
     * @dataProvider zeroIntegerDataProvider
     */
    public function checkIfPositiveIntegerForNonPositiveIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveInteger');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfPositiveIntegerForEmptyStringAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveInteger');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     *
     * @dataProvider positiveIntegerDataProvider
     */
    public function checkIfPositiveIntegerForPositiveIntegerNotAddsWarning(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveInteger');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     *
     * @dataProvider nonIntegerStringDataProvider
     */
    public function checkIfPositiveIntegerOrEmptyForNonIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveIntegerOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     *
     * @dataProvider negativeIntegerDataProvider
     * @dataProvider zeroIntegerDataProvider
     */
    public function checkIfPositiveIntegerOrEmptyForNonPositiveIntegerStringAddsWarningWithPathAndExplanation(
        string $value
    ) {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveIntegerOrEmpty');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfPositiveIntegerOrEmptyForEmptyStringNotAddsWarning()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveIntegerOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     *
     * @dataProvider positiveIntegerDataProvider
     */
    public function checkIfPositiveIntegerOrEmptyForPositiveIntegerNotAddsWarning(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfPositiveIntegerOrEmpty');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }

    /**
     * @test
     *
     * @dataProvider nonIntegerStringDataProvider
     */
    public function checkIfNonNegativeIntegerForNonIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeInteger');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     *
     * @dataProvider negativeIntegerDataProvider
     */
    public function checkIfNonNegativeIntegerForNegativeIntegerStringAddsWarningWithPathAndExplanation(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeInteger');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     */
    public function checkIfNonNegativeIntegerForEmptyStringAddsWarningWithPathAndExplanation()
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => '']), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeInteger');

        $subject->check();

        self::assertTrue($subject->hasWarnings());
        $warning = $subject->getWarningsAsHtml()[0];
        self::assertContains('plugin.tx_oelib.limit', $warning);
        self::assertContains('some explanation', $warning);
    }

    /**
     * @test
     *
     * @dataProvider positiveIntegerDataProvider
     * @dataProvider zeroIntegerDataProvider
     */
    public function checkIfNonNegativeIntegerForNonNegativeIntegerNotAddsWarning(string $value)
    {
        $subject = new TestingConfigurationCheck(new DummyConfiguration(['limit' => $value]), 'plugin.tx_oelib');
        $subject->setCheckMethod('checkIfNonNegativeInteger');

        $subject->check();

        self::assertFalse($subject->hasWarnings());
    }
}
