<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers\Fixtures;

use OliverKlee\Oelib\ViewHelpers\AbstractConfigurationCheckViewHelper;

final class TestingConfigurationCheckViewHelper extends AbstractConfigurationCheckViewHelper
{
    protected static function getExtensionKey(): string
    {
        return 'oelib';
    }

    protected static function getConfigurationCheckClassName(): string
    {
        return TestingConfigurationCheck::class;
    }
}
