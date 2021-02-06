<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use TYPO3\CMS\Core\Exception;

/**
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TestingConfigurationCheck extends AbstractConfigurationCheck
{
    /**
     * @var string
     */
    private $checkMethod = '';

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
     */
    protected function checkAllConfigurationValues()
    {
        switch ($this->checkMethod) {
            case 'checkStaticIncluded':
                $this->checkStaticIncluded();
                break;
            default:
        }
    }
}
