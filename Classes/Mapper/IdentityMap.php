<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Model\AbstractModel;

/**
 * This class represents an identity map that stores and retrieves model instances by their UIDs.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class IdentityMap
{
    /**
     * @var AbstractModel[] the items in this map with their UIDs as keys
     */
    protected $items = [];

    /**
     * @var int the highest used UID
     */
    private $highestUid = 0;

    /**
     * Adds a model to the identity map.
     *
     * @param AbstractModel $model the model to add, must have a UID
     *
     * @return void
     */
    public function add(AbstractModel $model)
    {
        if (!$model->hasUid()) {
            throw new \InvalidArgumentException('Add() requires a model that has a UID.', 1331488748);
        }

        $this->items[$model->getUid()] = $model;
        $this->highestUid = max($this->highestUid, $model->getUid());
    }

    /**
     * Retrieves a model from the map by UID.
     *
     * @throws NotFoundException if this map does not have a model
     *                                     with that particular UID
     *
     * @param int $uid the UID of the model to retrieve, must be > 0
     *
     * @return AbstractModel the stored model with the UID $uid
     */
    public function get(int $uid): AbstractModel
    {
        if ($uid <= 0) {
            throw new \InvalidArgumentException('$uid must be > 0.', 1331488761);
        }

        if (!isset($this->items[$uid])) {
            throw new NotFoundException(
                'This map currently does not contain a model with the UID ' .
                $uid . '.'
            );
        }

        return $this->items[$uid];
    }

    /**
     * Gets a UID that has not been used in the map before and that is greater
     * than the greatest used UID.
     *
     * @return int a new UID, will be > 0
     */
    public function getNewUid(): int
    {
        return $this->highestUid + 1;
    }
}
