<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TestingConfigurationCheck extends AbstractConfigurationCheck
{
    /**
     * @var string
     */
    const TEST_TABLE_NAME = 'tx_oelib_test';

    /**
     * @var string
     */
    private $checkMethod = '';

    /**
     * @var string
     */
    private $value = '';

    /**
     * @return void
     */
    public function setCheckMethod(string $method)
    {
        $this->checkMethod = $method;
    }

    /**
     * @return void
     */
    public function setValue(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return void
     */
    public function generateDummyWarnings(int $numberOfWarnings)
    {
        for ($i = 1; $i <= $numberOfWarnings; $i++) {
            $this->addWarning("This is warning #{$i}");
        }
    }

    /**
     * @return void
     */
    public function generateWarningWithText(string $warningText)
    {
        $this->addWarning($warningText);
    }

    /**
     * Checks all configuration values.
     *
     * This method does not reset any existing configuration check warnings.
     *
     * @return void
     *
     * @throws \BadMethodCallException
     */
    protected function checkAllConfigurationValues()
    {
        switch ($this->checkMethod) {
            case 'checkStaticIncluded':
                $this->checkStaticIncluded();
                break;
            case 'checkTemplateFile':
                $this->checkTemplateFile();
                break;
            case 'checkFileExists':
                $this->checkFileExists('file', 'some description');
                break;
            case 'checkForNonEmptyString':
                $this->checkForNonEmptyString('title', 'some explanation');
                break;
            case 'checkForNonEmptyStringWithUnsafeVariable':
                $this->checkForNonEmptyString('a"b', 'some explanation');
                break;
            case 'checkIfSingleInSetOrEmpty':
                $this->checkIfSingleInSetOrEmpty('size', 'some explanation', ['s', 'm']);
                break;
            case 'checkIfSingleInSetNotEmpty':
                $this->checkIfSingleInSetNotEmpty('size', 'some explanation', ['s', 'm']);
                break;
            case 'checkIfBoolean':
                $this->checkIfBoolean('switch', 'some explanation');
                break;
            case 'checkIfNonNegativeIntegerOrEmpty':
                $this->checkIfNonNegativeIntegerOrEmpty('limit', 'some explanation');
                break;
            case 'checkIfIntegerInRange':
                $this->checkIfIntegerInRange('limit', 2, 4, 'some explanation');
                break;
            case 'checkIfIntegerInRangeSame':
                $this->checkIfIntegerInRange('limit', 2, 2, 'some explanation');
                break;
            case 'checkIfIntegerInRangeSwitched':
                $this->checkIfIntegerInRange('limit', 3, 2, 'some explanation');
                break;
            case 'checkIfPositiveInteger':
                $this->checkIfPositiveInteger('limit', 'some explanation');
                break;
            case 'checkIfPositiveIntegerOrEmpty':
                $this->checkIfPositiveIntegerOrEmpty('limit', 'some explanation');
                break;
            case 'checkIfNonNegativeInteger':
                $this->checkIfNonNegativeInteger('limit', 'some explanation');
                break;
            case 'checkIfMultiInSetNotEmpty':
                $this->checkIfMultiInSetNotEmpty('sizes', 'some explanation', ['s', 'm']);
                break;
            case 'checkIfMultiInSetOrEmpty':
                $this->checkIfMultiInSetOrEmpty('sizes', 'some explanation', ['s', 'm']);
                break;
            case 'checkIfSingleInTableColumnsOrEmpty':
                $this->checkIfSingleInTableColumnsOrEmpty('column', 'some explanation', self::TEST_TABLE_NAME);
                break;
            case 'checkIfSingleInTableColumnsNotEmpty':
                $this->checkIfSingleInTableColumnsNotEmpty('column', 'some explanation', self::TEST_TABLE_NAME);
                break;
            case 'checkIfMultiInTableColumnsNotEmpty':
                $this->checkIfMultiInTableColumnsNotEmpty('columns', 'some explanation', self::TEST_TABLE_NAME);
                break;
            case 'checkIfMultiInTableColumnsOrEmpty':
                $this->checkIfMultiInTableColumnsOrEmpty('columns', 'some explanation', self::TEST_TABLE_NAME);
                break;
            case 'checkSalutationMode':
                $this->checkSalutationMode();
                break;
            case 'checkRegExp':
                $this->checkRegExp('title', 'some explanation', '/^[abc]+\\s*[1234]*$/');
                break;
            case 'checkIfIntegerListOrEmpty':
                $this->checkIfIntegerListOrEmpty('pages', 'some explanation');
                break;
            case 'checkIfIntegerListNotEmpty':
                $this->checkIfIntegerListNotEmpty('pages', 'some explanation');
                break;
            case 'checkIsValidEmailOrEmpty':
                $this->checkIsValidEmailOrEmpty('email', 'some explanation');
                break;
            case 'checkIsValidEmailNotEmpty':
                $this->checkIsValidEmailNotEmpty('email', 'some explanation');
                break;
            case 'checkNothing':
                break;
            default:
                throw new \BadMethodCallException(
                    'Unknown value for the check method: "' . $this->checkMethod . '"',
                    1616068312
                );
        }
    }
}
