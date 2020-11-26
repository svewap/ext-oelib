<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper;
use OliverKlee\Oelib\Model\BackEndUserGroup;
use OliverKlee\Oelib\Model\FrontEndUserGroup;

/**
 * Test case.
 *
 * @author Bernd Schönbach <bernd@oliverklee.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndUserGroupMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var BackEndUserGroupMapper
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new BackEndUserGroupMapper();

        $this->importDataSet(__DIR__ . '/../Fixtures/BackEndUsers.xml');
    }

    /**
     * @test
     */
    public function findReturnsBackEndUserGroupInstance()
    {
        self::assertInstanceOf(BackEndUserGroup::class, $this->subject->find(1));
    }

    /**
     * @test
     */
    public function loadForExistingUserGroupCanLoadUserGroupData()
    {
        /** @var FrontEndUserGroup $userGroup */
        $userGroup = $this->subject->find(1);
        $this->subject->load($userGroup);

        self::assertSame('The best!', $userGroup->getTitle());
    }

    /**
     * @test
     */
    public function subgroupRelationIsUserGroupList()
    {
        /** @var BackEndUserGroup $group */
        $group = $this->subject->find(1);
        self::assertInstanceOf(BackEndUserGroup::class, $group->getSubgroups()->first());
    }
}
