<?php
declare(strict_types = 1);

/**
 * This class represents a back-end user.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Model_BackEndUser extends \Tx_Oelib_Model implements \Tx_Oelib_Interface_MailRole
{
    /**
     * @var string[] the user's configuration (unserialized)
     */
    private $configuration = [];

    /**
     * Gets this user's user name.
     *
     * @return string this user's user name, will not be empty for valid users
     */
    public function getUserName(): string
    {
        return $this->getAsString('username');
    }

    /**
     * Gets this user's real name.
     *
     * @return string the user's real name, will not be empty for valid records
     */
    public function getName()
    {
        return $this->getAsString('realName');
    }

    /**
     * Gets the user's e-mail address.
     *
     * @return string the e-mail address, might be empty
     */
    public function getEmailAddress()
    {
        return $this->getAsString('email');
    }

    /**
     * Gets this user's language. Will be a two-letter "lg_typo3" key of the
     * "static_languages" table or "default" for the default language.
     *
     * @return string this user's language key, will not be empty
     */
    public function getLanguage(): string
    {
        $configuration = $this->getConfiguration();
        $result = !empty($configuration['lang']) ? $configuration['lang'] : $this->getDefaultLanguage();

        return ($result !== '') ? $result : 'default';
    }

    /**
     * Sets this user's default language.
     *
     * @param string $language
     *        this user's language key, must be a two-letter "lg_typo3" key of
     *        the "static_languages" table or "default" for the default language
     *
     * @return void
     */
    public function setDefaultLanguage(string $language)
    {
        if ($language === '') {
            throw new \InvalidArgumentException('$language must not be empty.', 1331488621);
        }

        $this->setAsString(
            'lang',
            ($language !== 'default') ? $language : ''
        );
    }

    /**
     * Checks whether this user has a non-default language set.
     *
     * @return bool TRUE if this user has a non-default language set, FALSE
     *                 otherwise
     */
    public function hasLanguage(): bool
    {
        return $this->getLanguage() !== 'default';
    }

    /**
     * Returns the direct user groups of this user.
     *
     * @return \Tx_Oelib_List<\Tx_Oelib_Model_BackEndUserGroup> the user's direct groups, will be empty if this
     *                       user has no groups
     */
    public function getGroups(): \Tx_Oelib_List
    {
        return $this->getAsList('usergroup');
    }

    /**
     * Recursively gets all groups and subgroups of this user.
     *
     * @return \Tx_Oelib_List<\Tx_Oelib_Model_BackEndUserGroup> all groups and subgroups of this user, will be
     *                       empty if this user has no groups
     */
    public function getAllGroups(): \Tx_Oelib_List
    {
        $result = new \Tx_Oelib_List();
        $groupsToProcess = $this->getGroups();

        do {
            $groupsForNextStep = new \Tx_Oelib_List();
            $result->append($groupsToProcess);
            /** @var \Tx_Oelib_Model_BackEndUserGroup $group */
            foreach ($groupsToProcess as $group) {
                /** @var \Tx_Oelib_Model_BackEndUserGroup $subgroup */
                foreach ($group->getSubgroups() as $subgroup) {
                    if (!$result->hasUid($subgroup->getUid())) {
                        $groupsForNextStep->add($subgroup);
                    }
                }
            }
            $groupsToProcess = $groupsForNextStep;
        } while (!$groupsToProcess->isEmpty());

        return $result;
    }

    /**
     * Retrieves the user's configuration, and unserializes it.
     *
     * @return string[] the user's configuration, will be empty if the user has no configuration set
     */
    private function getConfiguration(): array
    {
        if (empty($this->configuration)) {
            $this->configuration = (array)\unserialize($this->getAsString('uc'));
        }

        return $this->configuration;
    }

    /**
     * Returns the user's default language.
     *
     * @return string the user's default language, will be empty if no default
     *                language has been set
     */
    private function getDefaultLanguage(): string
    {
        return $this->getAsString('lang');
    }
}
