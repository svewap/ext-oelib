<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\BackEndUserGroupMapper;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Model\BackEndUserGroup;
use OliverKlee\Oelib\Testing\TestingFramework;

/**
 * Test case.
 *
 * @author Saskia Metzler <saskia@merlin.owl.de>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class BackEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingFramework for creating dummy records
     */
    private $testingFramework = null;

    /**
     * @var BackEndUserMapper the object to test
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');

        $this->subject = new BackEndUserMapper();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    //////////////////////////
    // Tests concerning find
    //////////////////////////

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsBackEndUserInstance()
    {
        self::assertInstanceOf(
            BackEndUser::class,
            $this->subject->find($this->testingFramework->createBackEndUser())
        );
    }

    /**
     * @test
     */
    public function findWithUidOfExistingRecordReturnsModelWithThatUid()
    {
        $uid = $this->testingFramework->createBackEndUser();

        self::assertSame(
            $uid,
            $this->subject->find($uid)->getUid()
        );
    }

    ////////////////////////////////////
    // Tests concerning findByUserName
    ////////////////////////////////////

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException()
    {
        $this->expectException(
            \InvalidArgumentException::class
        );
        $this->expectExceptionMessage(
            '$value must not be empty.'
        );

        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsBackEndUserInstance()
    {
        $this->testingFramework->createBackEndUser(['username' => 'foo']);

        self::assertInstanceOf(
            BackEndUser::class,
            $this->subject->findByUserName('foo')
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'foo']),
            $this->subject->findByUserName('foo')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'foo']),
            $this->subject->findByUserName('FOO')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'FOO']),
            $this->subject->findByUserName('FOO')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercaseNameOfExistingUppercaseUserReturnsModelWithThatUid()
    {
        self::assertSame(
            $this->testingFramework->createBackEndUser(['username' => 'FOO']),
            $this->subject->findByUserName('foo')->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException()
    {
        $this->expectException(NotFoundException::class);

        $this->testingFramework->createBackEndUser(
            ['username' => 'foo', 'deleted' => 1]
        );

        $this->subject->findByUserName('foo');
    }

    ///////////////////////////////////
    // Tests concerning the relations
    ///////////////////////////////////

    /**
     * @test
     */
    public function userGroupRelationIsUserGroupList()
    {
        /** @var BackEndUserGroup $group */
        $group = MapperRegistry::get(BackEndUserGroupMapper::class)->getNewGhost();
        $groupUid = $group->getUid();
        $userUid = $this->subject->getLoadedTestingModel(['usergroup' => $groupUid])->getUid();

        /** @var BackEndUser $user */
        $user = $this->subject->find($userUid);
        self::assertInstanceOf(BackEndUserGroup::class, $user->getGroups()->first());
    }
}
