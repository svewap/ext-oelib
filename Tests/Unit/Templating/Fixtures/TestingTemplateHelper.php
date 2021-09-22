<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Templating\Fixtures;

use OliverKlee\Oelib\Templating\TemplateHelper;

/**
 * This is mere a class used for unit tests. Don't use it for any other purpose.
 */
final class TestingTemplateHelper extends TemplateHelper
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
     * @param array $configuration TypoScript setup configuration, may be empty
     */
    public function __construct(array $configuration = [])
    {
        $this->init($configuration);
    }

    /**
     * Sets the salutation mode.
     *
     * @param string $salutation the salutation mode to use ("formal" or "informal")
     */
    public function setSalutationMode(string $salutation): void
    {
        $this->setConfigurationValue('salutation', $salutation);
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
     */
    public function ensureIntegerPiVars(array $additionalPiVars = []): void
    {
        parent::ensureIntegerPiVars($additionalPiVars);
    }

    /**
     * Ensures that all values in the given array are cast to int and removes empty
     * or invalid values.
     *
     * @param string[] $keys the keys of the piVars to check, may be empty
     */
    public function ensureIntegerArrayValues(array $keys): void
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
     */
    public function ensureContentObject(): void
    {
        parent::ensureContentObject();
    }
}
