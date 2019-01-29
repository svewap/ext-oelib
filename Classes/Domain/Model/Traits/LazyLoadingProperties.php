<?php

namespace OliverKlee\Oelib\Domain\Model\Traits;

use TYPO3\CMS\Extbase\Persistence\Generic\LazyLoadingProxy;

/**
 * This provides a method for loading lazy n:1 and 1:1 properties.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
trait LazyLoadingProperties
{
    /**
     * @param string $propertyName
     *
     * @return void
     */
    private function loadLazyProperty($propertyName)
    {
        if ($this->$propertyName instanceof LazyLoadingProxy) {
            $this->$propertyName = $this->$propertyName->_loadRealInstance();
        }
    }
}
