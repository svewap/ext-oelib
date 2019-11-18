<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Domain\Model\Traits;

/**
 * Trait for domain models with a change date.
 *
 * This is the default implementation of the corresponding interface.
 *
 * Any models that use this mode will still need to map the "tstamp" column using "mapOnProperty".
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de
 */
trait ChangeDate
{
    /**
     * @var \DateTime|null
     */
    protected $creationDate = null;

    /**
     * @return \DateTime|null
     */
    public function getChangeDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTime $creationDate
     *
     * @return void
     */
    public function setChangeDate(\DateTime $creationDate)
    {
        $this->creationDate = $creationDate;
    }
}
