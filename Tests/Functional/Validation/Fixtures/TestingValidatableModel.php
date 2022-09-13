<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Validation\Fixtures;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class TestingValidatableModel extends AbstractEntity
{
    /**
     * @var string
     */
    protected $title;

    public function __construct(string $title = '')
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
