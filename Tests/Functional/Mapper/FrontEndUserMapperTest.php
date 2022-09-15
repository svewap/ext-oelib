<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Oelib\Mapper\FrontEndUserMapper;
use OliverKlee\Oelib\Mapper\MapperRegistry;
use OliverKlee\Oelib\Model\FrontEndUser;

/**
 * @covers \OliverKlee\Oelib\Mapper\FrontEndUserMapper
 * @covers \OliverKlee\Oelib\Model\FrontEndUser
 */
final class FrontEndUserMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var FrontEndUserMapper
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FrontEndUserMapper();
    }

    //////////////////////////////
    // Test concerning getGroups
    //////////////////////////////

    /**
     * @test
     */
    public function getUserGroupsGetsRelatedGroupsAsList(): void
    {
        $groupMapper = MapperRegistry::get(FrontEndUserGroupMapper::class);

        $group1 = $groupMapper->getNewGhost();
        $group2 = $groupMapper->getNewGhost();
        $groupUids = $group1->getUid() . ',' . $group2->getUid();

        $this->getDatabaseConnection()->insertArray('fe_users', ['usergroup' => $groupUids]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        /** @var FrontEndUser $user */
        $user = $this->subject->find($uid);
        self::assertSame(
            $groupUids,
            $user->getUserGroups()->getUids()
        );
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameForEmptyUserNameThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('$value must not be empty.');

        // @phpstan-ignore-next-line We are explicitly testing for a contract violation here.
        $this->subject->findByUserName('');
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsFrontEndUserInstance(): void
    {
        $username = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $username]);

        self::assertInstanceOf(
            FrontEndUser::class,
            $this->subject->findByUserName($username)
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsModelWithThatUid(): void
    {
        $username = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $username]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($username)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsModelWithThatUid(): void
    {
        $username = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $username]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName(strtoupper($username))->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
    {
        $username = 'MAX.DOE';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $username]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($username)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercasedNameOfExistingUppercasedUserReturnsModelWithThatUid(): void
    {
        $username = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => strtoupper($username)]);
        $uid = (int)$this->getDatabaseConnection()->lastInsertId();

        self::assertSame(
            $uid,
            $this->subject->findByUserName($username)->getUid()
        );
    }

    /**
     * @test
     */
    public function findByUserNameWithNameOfNonExistentUserThrowsException(): void
    {
        $this->expectException(NotFoundException::class);

        $username = 'max.doe';
        $this->getDatabaseConnection()->insertArray('fe_users', ['username' => $username, 'deleted' => 1]);

        $this->subject->findByUserName($username);
    }
}
