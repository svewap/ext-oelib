<?php
defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['oelib']['testingFrameworkCleanUp'][]
    = \OliverKlee\Oelib\Tests\TestingFrameworkCleanup::class;
