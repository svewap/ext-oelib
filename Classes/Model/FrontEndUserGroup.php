<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Model;

/**
 * This class represents a front-end user group.
 */
class FrontEndUserGroup extends AbstractModel
{
    /**
     * Gets this group's title.
     *
     * @return string the title of this group, will be empty if the group has
     *                none
     */
    public function getTitle(): string
    {
        return $this->getAsString('title');
    }

    /**
     * Gets this group's description.
     *
     * @return string the description of this group, will be empty if the group
     *                has none
     */
    public function getDescription(): string
    {
        return $this->getAsString('description');
    }
}
