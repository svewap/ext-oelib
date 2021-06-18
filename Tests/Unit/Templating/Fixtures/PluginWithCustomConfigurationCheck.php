<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Templating\Fixtures;

use OliverKlee\Oelib\Templating\TemplateHelper;

/**
 * Testing subclass of TemplateHelper with a custom configuration check class.
 */
class PluginWithCustomConfigurationCheck extends TemplateHelper
{
    /**
     * @var string
     */
    public $extKey = 'oelib';

    /**
     * @return string
     */
    protected function getConfigurationCheckClassName(): string
    {
        return TestingConfigurationCheck::class;
    }
}
