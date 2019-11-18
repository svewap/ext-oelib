<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\Model\Fixtures;

/**
 * This class represents a read-only model for testing purposes.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class ReadOnlyModel extends \Tx_Oelib_Model
{
    /**
     * @var bool whether this model is read-only
     */
    protected $readOnly = true;

    /**
     * Sets the "title" data item for this model.
     *
     * @param string $value
     *
     * @return void
     */
    public function setTitle(string $value)
    {
        $this->setAsString('title', $value);
    }
}
