<?php
/***************************************************************
* Copyright notice
*
* (c) 2008 Oliver Klee (typo3-coding@oliverklee.de)
* All rights reserved
*
* This script is part of the TYPO3 project. The TYPO3 project is
* free software; you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* The GNU General Public License can be found at
* http://www.gnu.org/copyleft/gpl.html.
*
* This script is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
*
* This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'tests/fixtures/class.tx_oelib_testingModel.php');

/**
 * Testcase for the tx_oelib_model class in the 'oelib' extension.
 *
 * @package TYPO3
 * @subpackage tx_oelib
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_oelib_model_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_oelib_testingModel the model to test
	 */
	private $fixture;

	public function setUp() {
		$this->fixture = new tx_oelib_testingModel();
	}

	public function tearDown() {
		$this->fixture->__destruct();
		unset($this->fixture);
	}


	//////////////////////////////////////
	// Tests for the basic functionality
	//////////////////////////////////////

	public function testGetWithNoDataThrowsException() {
		$this->setExpectedException(
			'Exception',
			'Please call setData() directly after instantiation first.'
		);

		$this->fixture->getTitle();
	}

	public function testSetDataWithEmptyArrayIsAllowed() {
		$this->fixture->setData(array());
	}

	public function testGetAfterSetReturnsTheSetValue() {
		$this->fixture->setTitle('bar');

		$this->assertEquals(
			'bar',
			$this->fixture->getTitle()
		);
	}

	public function testGetAfterSetDataReturnsTheSetValue() {
		$this->fixture->setData(
			array('title' => 'bar')
		);

		$this->assertEquals(
			'bar',
			$this->fixture->getTitle()
		);
	}

	public function testSetDataCalledTwoTimesThrowsAnException() {
		$this->setExpectedException(
			'Exception', 'setData must only be called once per model instance.'
		);

		$this->fixture->setData(
			array('title' => 'bar')
		);
		$this->fixture->setData(
			array('title' => 'bar')
		);
	}


	/////////////////////////////
	// Tests concerning the UID
	/////////////////////////////

	public function testGetUidForNoUidReturnsZero() {
		$this->fixture->setData(array());

		$this->assertEquals(
			0,
			$this->fixture->getUid()
		);
	}

	public function testGetUidForSetUidReturnsTheSetUid() {
		$this->fixture->setUid(42);

		$this->assertEquals(
			42,
			$this->fixture->getUid()
		);
	}

	public function testHasUidForNoUidReturnsFalse() {
		$this->fixture->setData(array());

		$this->assertFalse(
			$this->fixture->hasUid()
		);
	}

	public function testHasUidForPositiveUidReturnsTrue() {
		$this->fixture->setUid(42);

		$this->assertTrue(
			$this->fixture->hasUid()
		);
	}
}
?>