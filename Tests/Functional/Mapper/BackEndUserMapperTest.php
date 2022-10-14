<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Model\BackEndUserGroup;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\BackEndUserMapper
 * @covers \OliverKlee\Oelib\Model\BackEndUser
 */
final class BackEndUserMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var BackEndUserMapper the object to test
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new BackEndUserMapper();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');
        $model = $this->subject->find(1);

        $this->subject->load($model);

        self::assertSame('max', $model->getUserName());
    }

    // Tests concerning findByUserName

    /**
     * @test
     */
    public function findByUserNameWithNameOfExistingUserReturnsMatchingBackEndUserInstance(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('max');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('max', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsMatchingModel(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('MAX');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('max', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsMatchingModel(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('MOE');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('MOE', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercaseNameOfExistingUppercaseUserReturnsModelWithThatUid(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('moe');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('MOE', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameNotFindsDeletedUser(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('No records found in the table "be_users" matching: {"username":"deleted"}');
        $this->expectExceptionCode(0);

        $this->subject->findByUserName('deleted');
    }

    // Tests concerning the relations

    /**
     * @test
     */
    public function loadsMapsUserGroupsRelation(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->find(1);

        $firstGroup = $model->getGroups()->first();
        self::assertInstanceOf(BackEndUserGroup::class, $firstGroup);
        self::assertSame(1, $firstGroup->getUid());
    }
}
