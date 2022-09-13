<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Validation;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Validation\Error as ValidationError;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Checks that the fields that are configured to be required are filled in.
 *
 * The subclasses need to configure the class name of the model to be validated in the generics and in
 * `$this->modelClassName`, change `$this->$configurationKey` (if needed) and implement `isFieldFilledIn()`.
 *
 * The code of `isFieldFilledIn()` could look like this:
 *
 * ```php
 * switch ($field) {
 *     case 'name':
 *         $result = $model->getName() !== '';
 *         break;
 *     case 'city':
 *         $result = $model->getCity() !== '';
 *         break;
 *     default:
 *         $result = true; * ```
 * }
 *
 * return $result;
 * ```
 *
 * @template M of AbstractEntity
 */
abstract class AbstractConfigurationDependentValidator extends AbstractValidator
{
    /**
     * @var class-string<M>
     */
    protected $modelClassName;

    /**
     * the name of the configuration variable with the required model fields
     *
     * @var non-empty-string
     */
    protected $configurationKey = 'requiredFields';

    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var array<int, string>
     */
    private $requiredFields = [];

    /**
     * @param array<string, string> $settings
     */
    public function setSettings(array $settings): void
    {
        $requiredFieldsSetting = $settings[$this->configurationKey] ?? '';
        if (\is_string($requiredFieldsSetting)) {
            $this->requiredFields = GeneralUtility::trimExplode(',', $requiredFieldsSetting, true);
        }
    }

    /**
     * @param mixed $value
     */
    protected function isValid($value): void
    {
        if (!$value instanceof $this->modelClassName) {
            return;
        }

        foreach ($this->requiredFields as $field) {
            if (!$this->isFieldFilledIn($field, $value)) {
                $errorMessage = $this->translateErrorMessage('validationError.fillInField', 'oelib') ?? '';
                $error = new ValidationError($errorMessage, 1651765504);
                $this->result->forProperty($field)->addError($error);
            }
        }
    }

    /**
     * @param M $model
     */
    abstract protected function isFieldFilledIn(string $field, AbstractEntity $model): bool;
}
