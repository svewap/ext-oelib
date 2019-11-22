<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Templating\Fixtures;

/**
 * Testing subclass of TemplateHelper with a custom configuration check class.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class PluginWithCustomConfigurationCheck extends \Tx_Oelib_TemplateHelper
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
