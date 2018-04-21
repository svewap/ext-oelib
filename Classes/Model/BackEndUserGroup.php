<?php

/**
 * This class represents a back-end user group.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_BackEndUserGroup extends Tx_Oelib_Model
{
    /**
     * Gets this group's title.
     *
     * @return string the title of this group, will be empty if the group has
     *                none
     */
    public function getTitle()
    {
        return $this->getAsString('title');
    }

    /**
     * Returns this group's direct subgroups.
     *
     * @return Tx_Oelib_List<Tx_Oelib_Model_BackEndUserGroup> this group's direct subgroups, will be empty if
     *                       this group has no subgroups
     */
    public function getSubgroups()
    {
        return $this->getAsList('subgroup');
    }
}
