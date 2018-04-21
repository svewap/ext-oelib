<?php

/**
 * This class represents a read-only model for testing purposes.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
final class Tx_Oelib_Tests_Unit_Fixtures_ReadOnlyModel extends \Tx_Oelib_Model
{
    /**
     * @var bool whether this model is read-only
     */
    protected $readOnly = true;

    /**
     * Sets the "title" data item for this model.
     *
     * @param string $value
     *        the value to set, may be empty
     *
     * @return void
     */
    public function setTitle($value)
    {
        $this->setAsString('title', $value);
    }
}
