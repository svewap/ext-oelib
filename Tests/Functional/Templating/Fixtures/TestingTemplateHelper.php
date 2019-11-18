<?php
declare(strict_types = 1);

namespace OliverKlee\Oelib\Tests\Functional\Templating\Fixtures;

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class TestingTemplateHelper extends \Tx_Oelib_TemplateHelper
{
    /**
     * @var string
     */
    public $scriptRelPath = 'Tests/Unit/Language/Fixtures/locallang.xlf';

    /**
     * @var string the extension key
     */
    public $extKey = 'oelib';

    /**
     * The constructor.
     *
     * @param array $configuration
     *        TS setup configuration, may be empty
     */
    public function __construct(array $configuration = [])
    {
        $this->init($configuration);
    }

    /**
     * @return \Tx_Oelib_ConfigCheck|null
     */
    public function getConfigurationCheck()
    {
        return $this->configurationCheck;
    }

    /**
     * Sets the salutation mode.
     *
     * @param string $salutation
     *        the salutation mode to use ("formal" or "informal")
     *
     * @return void
     */
    public function setSalutationMode(string $salutation)
    {
        $this->setConfigurationValue('salutation', $salutation);
    }

    /**
     * Retrieves the configuration (TS setup) of the page with the PID provided
     * as the parameter $pageId.
     *
     * Only the configuration for the current extension key will be retrieved.
     * For example, if the extension key is "foo", the TS setup for plugin.
     * tx_foo will be retrieved.
     *
     * @param int $pageId
     *        page ID of the page for which the configuration should be retrieved, must be > 0
     *
     * @return array configuration array of the requested page for the current
     *               extension key
     */
    public function retrievePageConfig(int $pageId): array
    {
        return parent::retrievePageConfig($pageId);
    }

    /**
     * Intvals all piVars that are supposed to be integers:
     * showUid, pointer, mode
     *
     * If some piVars are not set or no piVars array is defined yet, this
     * function will set the not yet existing piVars to zero.
     *
     * @param string[] $additionalPiVars
     *        keys for $this->piVars that will be ensured to exist as ints in
     *        $this->piVars as well
     *
     * @return void
     */
    public function ensureIntegerPiVars(array $additionalPiVars = [])
    {
        parent::ensureIntegerPiVars($additionalPiVars);
    }

    /**
     * Ensures that all values in the given array are cast to int and removes empty
     * or invalid values.
     *
     * @param string[] $keys the keys of the piVars to check, may be empty
     *
     * @return void
     */
    public function ensureIntegerArrayValues(array $keys)
    {
        parent::ensureIntegerArrayValues($keys);
    }

    /**
     * Ensures that $this->cObj points to a valid content object.
     *
     * If this object already has a valid cObj, this function does nothing.
     *
     * If there is a front end and this object does not have a cObj yet, the
     * cObj from the front end is used.
     *
     * If this object has no cObj and there is no front end, this function will
     * do nothing.
     *
     * @return void
     */
    public function ensureContentObject()
    {
        parent::ensureContentObject();
    }
}
