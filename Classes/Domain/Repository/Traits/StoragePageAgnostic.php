<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Repository\Traits;

use TYPO3\CMS\Extbase\Persistence\Generic\QuerySettingsInterface;

/**
 * This trait for repositories makes the repository ignore the storage page setting when fetching models.
 */
trait StoragePageAgnostic
{
    /**
     * @return void
     */
    public function initializeObject()
    {
        /** @var QuerySettingsInterface $querySettings */
        $querySettings = $this->objectManager->get(QuerySettingsInterface::class);
        $querySettings->setRespectStoragePage(false);
        $this->setDefaultQuerySettings($querySettings);
    }
}
