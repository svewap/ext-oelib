<?php

namespace OliverKlee\Oelib\Tests\Unit\Model;

use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 */
class FrontEndUserGroupTest extends UnitTestCase
{
    /**
     * @var \Tx_Oelib_Model_FrontEndUserGroup
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new \Tx_Oelib_Model_FrontEndUserGroup();
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

    //////////////////////////////////////
    // Tests concerning getDescription()
    //////////////////////////////////////

    /**
     * @test
     */
    public function getDescriptionForNonEmptyGroupDescriptionReturnsGroupDescription()
    {
        $this->subject->setData(['description' => 'foo']);

        self::assertSame(
            'foo',
            $this->subject->getDescription()
        );
    }

    /**
     * @test
     */
    public function getDescriptionForEmptyGroupDescriptionReturnsEmptyString()
    {
        $this->subject->setData(['description' => '']);

        self::assertSame(
            '',
            $this->subject->getDescription()
        );
    }
}
