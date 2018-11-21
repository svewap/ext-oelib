<?php

namespace OliverKlee\Oelib\Tests\Unit\Configuration\Fixtures;

/**
 * This is mere a class to test the configuration check class.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 */
class DummyObjectToCheck extends \Tx_Oelib_TemplateHelper implements \Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * The constructor.
     *
     * @param array $configuration
     *        configuration for the dummy object, may be empty
     */
    public function __construct(array $configuration)
    {
        $this->init($configuration);
    }

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace()
    {
        return 'plugin.tx_oelib_test.';
    }
}
