<?php

use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_LegacyUnit_Model_BackEndUserGroupTest extends TestCase
{
    /**
     * @var \Tx_Oelib_Model_BackEndUserGroup
     */
    private $subject;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Model_BackEndUserGroup();
    }

    ////////////////////////////////
    // Tests concerning getTitle()
    ////////////////////////////////

    /**
     * @test
     */
    public function getTitleForNonEmptyGroupTitleReturnsGroupTitle()
    {
        $this->subject->setData(['title' => 'foo']);

        self::assertSame(
            'foo',
            $this->subject->getTitle()
        );
    }

    /**
     * @test
     */
    public function getTitleForEmptyGroupTitleReturnsEmptyString()
    {
        $this->subject->setData(['title' => '']);

        self::assertSame(
            '',
            $this->subject->getTitle()
        );
    }

    /////////////////////////////////////
    // Tests concerning getSubgroups
    /////////////////////////////////////

    /**
     * @test
     */
    public function getSubgroupsReturnsListFromSubgroupField()
    {
        $groups = new \Tx_Oelib_List();

        $this->subject->setData(['subgroup' => $groups]);

        self::assertSame(
            $groups,
            $this->subject->getSubgroups()
        );
    }
}
