<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Functional\Mapper;

use OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper;
use OliverKlee\Oelib\Model\FrontEndUserGroup;
use OliverKlee\Oelib\Testing\TestingFramework;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Oelib\Mapper\FrontEndUserGroupMapper
 * @covers \OliverKlee\Oelib\Model\FrontEndUserGroup
 */
final class FrontEndUserGroupMapperTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib'];

    /**
     * @var TestingFramework for creating dummy records
     */
    private $testingFramework = null;

    /**
     * @var FrontEndUserGroupMapper the object to test
     */
    private $subject = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->testingFramework = new TestingFramework('tx_oelib');

        $this->subject = new FrontEndUserGroupMapper();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    /////////////////////////////////////////
    // Tests concerning the basic functions
    /////////////////////////////////////////

    /**
     * @test
     */
    public function loadForExistingUserGroupCanLoadUserGroupData(): void
    {
        /** @var FrontEndUserGroup $userGroup */
        $userGroup = $this->subject->find(
            $this->testingFramework->createFrontEndUserGroup(['title' => 'foo'])
        );

        $this->subject->load($userGroup);

        self::assertSame(
            'foo',
            $userGroup->getTitle()
        );
    }
}
