<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\Oelib\Exception\NotFoundException;
use OliverKlee\Oelib\Mapper\BackEndUserMapper;
use OliverKlee\Oelib\Model\BackEndUser;
use OliverKlee\Oelib\Model\BackEndUserGroup;

/**
 * @covers \OliverKlee\Oelib\Mapper\BackEndUserMapper
 */
final class BackEndUserMapperTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var BackEndUserMapper the object to test
     */
    private $subject = null;

    protected function setUp()
    {
        parent::setUp();

        $this->subject = new BackEndUserMapper();
    }

    /**
     * @test
     */
    public function loadForExistingRecordLoadsScalarData()
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
    public function findByUserNameWithNameOfExistingUserReturnsMatchingBackEndUserInstance()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('max');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('max', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingLowercasedUserReturnsMatchingModel()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('MAX');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('max', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameWithUppercasedNameOfExistingUppercasedUserReturnsMatchingModel()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('MOE');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('MOE', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameWithLowercaseNameOfExistingUppercaseUserReturnsModelWithThatUid()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->findByUserName('moe');

        self::assertInstanceOf(BackEndUser::class, $model);
        self::assertSame('MOE', $model->getUserName());
    }

    /**
     * @test
     */
    public function findByUserNameNotFindsDeletedUser()
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
    public function loadsMapsUserGroupsRelation()
    {
        $this->importDataSet(__DIR__ . '/Fixtures/BackEndUsers.xml');

        $model = $this->subject->find(1);

        $firstGroup = $model->getGroups()->first();
        self::assertInstanceOf(BackEndUserGroup::class, $firstGroup);
        self::assertSame(1, $firstGroup->getUid());
    }
}
