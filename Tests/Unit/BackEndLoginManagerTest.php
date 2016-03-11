<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;

/**
 * Test case.
 *
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Oelib_Tests_Unit_BackEndLoginManagerTest extends Tx_Phpunit_TestCase
{
    /**
     * @var Tx_Oelib_BackEndLoginManager
     */
    private $subject = null;

    /**
     * @var Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var Tx_Oelib_BackEndLoginManager
     */
    private $backEndUserMapper = null;

    protected function setUp()
    {
        $this->testingFramework = new Tx_Oelib_TestingFramework('tx_oelib');

        $this->backEndUserMapper = Tx_Oelib_MapperRegistry::get(Tx_Oelib_Mapper_BackEndUser::class);

        $this->subject = Tx_Oelib_BackEndLoginManager::getInstance();
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();
    }

    /**
     * Returns $GLOBALS['BE_USER'].
     *
     * @return BackendUserAuthentication
     */
    private function getBackEndUserAuthentication()
    {
        return $GLOBALS['BE_USER'];
    }

    ////////////////////////////////////////////
    // Tests concerning the Singleton property
    ////////////////////////////////////////////

    /**
     * @test
     */
    public function getInstanceReturnsBackEndLoginManagerInstance()
    {
        self::assertInstanceOf(Tx_Oelib_BackEndLoginManager::class, $this->subject);
    }

    /**
     * @test
     */
    public function getInstanceTwoTimesReturnsSameInstance()
    {
        self::assertSame(
            $this->subject,
            Tx_Oelib_BackEndLoginManager::getInstance()
        );
    }

    /**
     * @test
     */
    public function getInstanceAfterPurgeInstanceReturnsNewInstance()
    {
        Tx_Oelib_BackEndLoginManager::purgeInstance();

        self::assertNotSame(
            $this->subject,
            Tx_Oelib_BackEndLoginManager::getInstance()
        );
    }

    ////////////////////////////////
    // Tests concerning isLoggedIn
    ////////////////////////////////

    /**
     * @test
     */
    public function isLoggedInWithLoggedInBackEndUserReturnsTrue()
    {
        // We assume that the tests are run when logged in in the BE.
        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /**
     * @test
     */
    public function isLoggedInForFakedUserReturnsTrue()
    {
        /** @var Tx_Oelib_Model_BackEndUser $ghostUser */
        $ghostUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($ghostUser);

        self::assertTrue(
            $this->subject->isLoggedIn()
        );
    }

    /////////////////////////////////////
    // Tests concerning getLoggedInUser
    /////////////////////////////////////

    /**
     * @test
     */
    public function getLoggedInUserWithEmptyMapperNameThrowsException()
    {
        $this->setExpectedException(
            'InvalidArgumentException',
            '$mapperName must not be empty.'
        );

        $this->subject->getLoggedInUser('');
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserInstance()
    {
        self::assertTrue(
            $this->subject->getLoggedInUser()
                instanceof Tx_Oelib_Model_BackEndUser
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithOtherMapperNameAndLoggedInUserReturnsCorrespondingModel()
    {
        self::assertTrue(
            $this->subject->getLoggedInUser('Tx_Oelib_Tests_Unit_Fixtures_TestingMapper')
                instanceof Tx_Oelib_Tests_Unit_Fixtures_TestingModel
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithLoggedInUserReturnsBackEndUserWithUidOfLoggedInUser()
    {
        self::assertSame(
            (int)$this->getBackEndUserAuthentication()->user['uid'],
            $this->subject->getLoggedInUser()->getUid()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserWithAlreadyCreatedUserModelReturnsThatInstance()
    {
        /** @var Tx_Oelib_Model_BackEndUser $user */
        $user = $this->backEndUserMapper->find($this->getBackEndUserAuthentication()->user['uid']);

        self::assertSame(
            $user,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function getLoggedInUserUsesMappedUserDataFromMemory()
    {
        $backedUpName = $this->getBackEndUserAuthentication()->user['realName'];
        $this->getBackEndUserAuthentication()->user['realName'] = 'John Doe';

        self::assertSame(
            'John Doe',
            $this->subject->getLoggedInUser()->getName()
        );

        $this->getBackEndUserAuthentication()->user['realName'] = $backedUpName;
    }

    ////////////////////////////////////
    // Tests concerning setLoggedInUser
    ////////////////////////////////////

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenSetsTheLoggedInUser()
    {
        /** @var Tx_Oelib_Model_BackEndUser $backEndUser */
        $backEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($backEndUser);

        self::assertSame(
            $backEndUser,
            $this->subject->getLoggedInUser()
        );
    }

    /**
     * @test
     */
    public function setLoggedInUserForUserGivenAndAlreadyStoredLoggedInUserOverridesTheOldUserWithTheNewOne()
    {
        /** @var Tx_Oelib_Model_BackEndUser $oldBackEndUser */
        $oldBackEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($oldBackEndUser);
        /** @var Tx_Oelib_Model_BackEndUser $newBackEndUser */
        $newBackEndUser = $this->backEndUserMapper->getNewGhost();
        $this->subject->setLoggedInUser($newBackEndUser);

        self::assertSame(
            $newBackEndUser,
            $this->subject->getLoggedInUser()
        );
    }
}
