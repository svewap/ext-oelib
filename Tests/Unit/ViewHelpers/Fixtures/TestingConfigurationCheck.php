<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Interfaces\Configuration;

final class TestingConfigurationCheck extends AbstractConfigurationCheck
{
    /**
     * @var Configuration|null
     */
    private static $checkedConfiguration = null;

    public static function getCheckedConfiguration(): ?Configuration
    {
        return self::$checkedConfiguration;
    }

    protected function checkAllConfigurationValues(): void
    {
        self::$checkedConfiguration = $this->configuration;

        $this->addWarning('This is a configuration check warning.');
    }
}
