<?php

declare(strict_types=1);

/**
 * This interface represents an object that can be sorted.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
interface Tx_Oelib_Interface_Sortable
{
    /**
     * Returns the sorting value for this object.
     *
     * This is the sorting as used in the back end.
     *
     * @return int the sorting value of this object, will be >= 0
     */
    public function getSorting();

    /**
     * Sets the sorting value for this object.
     *
     * This is the sorting as used in the back end.
     *
     * @param int $sorting the sorting value of this object, must be >= 0
     *
     * @return void
     */
    public function setSorting($sorting);
}
