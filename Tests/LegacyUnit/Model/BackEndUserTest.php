<?php

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Model_BackEndUserTest extends \Tx_Phpunit_TestCase
{
    /**
     * @var \Tx_Oelib_Model_BackEndUser
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Model_BackEndUser();
    }

    ///////////////////////////////////////////
    // Tests concerning getting the user name
    ///////////////////////////////////////////

    /**
     * @test
     */
    public function getUserNameForEmptyUserNameReturnsEmptyString()
    {
        $this->subject->setData(['username' => '']);

        self::assertSame(
            '',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameForNonEmptyUserNameReturnsUserName()
    {
        $this->subject->setData(['username' => 'johndoe']);

        self::assertSame(
            'johndoe',
            $this->subject->getUserName()
        );
    }

    //////////////////////////////////////
    // Tests concerning getting the name
    //////////////////////////////////////

    /**
     * @test
     */
    public function getNameForNonEmptyNameReturnsName()
    {
        $this->subject->setData(['realName' => 'John Doe']);

        self::assertSame(
            'John Doe',
            $this->subject->getName()
        );
    }

    /**
     * @test
     */
    public function getNameForEmptyNameReturnsEmptyString()
    {
        $this->subject->setData(['realName' => '']);

        self::assertSame(
            '',
            $this->subject->getName()
        );
    }

    //////////////////////////////////////////////////////
    // Tests concerning setting and getting the language
    //////////////////////////////////////////////////////

    /**
     * @test
     */
    public function getLanguageForNonEmptyLanguageReturnsLanguageKey()
    {
        $this->subject->setData(['lang' => 'de']);

        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForEmptyLanguageKeyReturnsDefault()
    {
        $this->subject->setData(['lang' => '']);

        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForLanguageSetInUserConfigurationReturnsThisLanguage()
    {
        $this->subject->setData(['uc' => serialize(['lang' => 'de'])]);

        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForSetDefaultLanguageAndLanguageSetInUserConfigurationReturnsLanguageFromConfiguration()
    {
        $this->subject->setData(['uc' => serialize(['lang' => 'fr'])]);
        $this->subject->setDefaultLanguage('de');

        self::assertSame(
            'fr',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getLanguageForSetDefaultLanguageAndEmptyLanguageSetInUserConfigurationReturnsDefaultLanguage()
    {
        $this->subject->setData(['uc' => serialize(['lang' => ''])]);
        $this->subject->setDefaultLanguage('fr');

        self::assertSame(
            'fr',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function getDefaultLanguageSetsLanguage()
    {
        $this->subject->setDefaultLanguage('de');

        self::assertSame(
            'de',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setDefaultLanguageWithDefaultSetsLanguage()
    {
        $this->subject->setDefaultLanguage('default');

        self::assertSame(
            'default',
            $this->subject->getLanguage()
        );
    }

    /**
     * @test
     */
    public function setDefaultLanguageWithEmptyKeyThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$language must not be empty.'
        );

        $this->subject->setDefaultLanguage('');
    }

    /**
     * @test
     */
    public function hasLanguageWithoutLanguageReturnsFalse()
    {
        $this->subject->setData([]);

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithDefaultLanguageSetReturnsFalse()
    {
        $this->subject->setData([]);
        $this->subject->setDefaultLanguage('default');

        self::assertFalse(
            $this->subject->hasLanguage()
        );
    }

    /**
     * @test
     */
    public function hasLanguageWithNonEmptyLanguageReturnsTrue()
    {
        $this->subject->setData(['lang' => 'de']);

        self::assertTrue(
            $this->subject->hasLanguage()
        );
    }

    ////////////////////////////////////////////////
    // Tests concerning getting the e-mail address
    ////////////////////////////////////////////////

    /**
     * @test
     */
    public function getEmailAddressForEmptyEmailReturnsEmptyString()
    {
        $this->subject->setData(['email' => '']);

        self::assertSame(
            '',
            $this->subject->getEmailAddress()
        );
    }

    /**
     * @test
     */
    public function getEmailAddressForNonEmptyEmailReturnsEmail()
    {
        $this->subject->setData(['email' => 'john@doe.com']);

        self::assertSame(
            'john@doe.com',
            $this->subject->getEmailAddress()
        );
    }

    ///////////////////////////////
    // Tests concerning getGroups
    ///////////////////////////////

    /**
     * @test
     */
    public function getGroupsReturnsListFromUserGroupField()
    {
        $groups = new \Tx_Oelib_List();

        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            $groups,
            $this->subject->getGroups()
        );
    }

    //////////////////////////////////
    // Tests concerning getAllGroups
    //////////////////////////////////

    /**
     * @test
     */
    public function getAllGroupsForNoGroupsReturnsList()
    {
        $this->subject->setData(['usergroup' => new \Tx_Oelib_List()]);

        self::assertInstanceOf(
            \Tx_Oelib_List::class,
            $this->subject->getAllGroups()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForNoGroupsReturnsEmptyList()
    {
        $this->subject->setData(['usergroup' => new \Tx_Oelib_List()]);

        self::assertTrue(
            $this->subject->getAllGroups()->isEmpty()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForOneGroupReturnsListWithThatGroup()
    {
        $group = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            $group,
            $this->subject->getAllGroups()->first()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForTwoGroupsReturnsBothGroups()
    {
        $group1 = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $group2 = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $groups = new \Tx_Oelib_List();
        $groups->add($group1);
        $groups->add($group2);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($group1->getUid())
        );
        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($group2->getUid())
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubgroupReturnsBothGroups()
    {
        $subgroup = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $group = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['subgroup' => $subgroup->getUid()]
        );
        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($group->getUid())
        );
        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($subgroup->getUid())
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubsubgroupContainsSubsubgroup()
    {
        $subsubgroup = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel([]);
        $subgroup = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['subgroup' => $subsubgroup->getUid()]
        );
        $group = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getLoadedTestingModel(
            ['subgroup' => $subgroup->getUid()]
        );
        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertTrue(
            $this->subject->getAllGroups()->hasUid($subsubgroup->getUid())
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubgroupSelfReferenceReturnsOnlyOneGroup()
    {
        $group = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getNewGhost();
        $subgroups = new \Tx_Oelib_List();
        $subgroups->add($group);
        $group->setData(['subgroup' => $subgroups]);

        $groups = new \Tx_Oelib_List();
        $groups->add($group);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            1,
            $this->subject->getAllGroups()->count()
        );
    }

    /**
     * @test
     */
    public function getAllGroupsForGroupWithSubgroupCycleReturnsBothGroups()
    {
        $group1 = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getNewGhost();
        $group2 = \Tx_Oelib_MapperRegistry::
        get(\Tx_Oelib_Mapper_BackEndUserGroup::class)->getNewGhost();

        $subgroups1 = new \Tx_Oelib_List();
        $subgroups1->add($group2);
        $group1->setData(['subgroup' => $subgroups1]);

        $subgroups2 = new \Tx_Oelib_List();
        $subgroups2->add($group1);
        $group2->setData(['subgroup' => $subgroups2]);

        $groups = new \Tx_Oelib_List();
        $groups->add($group1);
        $this->subject->setData(['usergroup' => $groups]);

        self::assertSame(
            2,
            $this->subject->getAllGroups()->count()
        );
    }
}
