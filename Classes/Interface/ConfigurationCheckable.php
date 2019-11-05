<?php
declare(strict_types = 1);

/**
 * This interface represents an object that can have an automatic configuration check.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace();
}
