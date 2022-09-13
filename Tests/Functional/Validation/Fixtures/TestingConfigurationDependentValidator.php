<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Validation\Fixtures;

use OliverKlee\Oelib\Validation\AbstractConfigurationDependentValidator;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * @extends AbstractConfigurationDependentValidator<TestingValidatableModel>
 */
final class TestingConfigurationDependentValidator extends AbstractConfigurationDependentValidator
{
    protected function getModelClassName(): string
    {
        return TestingValidatableModel::class;
    }

    protected function isFieldFilledIn(string $field, AbstractEntity $model): bool
    {
        switch ($field) {
            case 'title':
                $result = $model->getTitle() !== '';
                break;
            default:
                $result = true;
        }

        return $result;
    }
}
