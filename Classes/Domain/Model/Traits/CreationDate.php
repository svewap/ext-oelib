<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

/**
 * Trait for domain models with a creation date.
 *
 * This is the default implementation of the corresponding interface.
 *
 * Any models that use this mode will still need to map the "crdate" column using "mapOnProperty".
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
trait CreationDate
{
    /**
     * @var \DateTime|null
     */
    protected $creationDate = null;

    /**
     * @return \DateTime|null
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return void
     */
    public function setCreationDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
